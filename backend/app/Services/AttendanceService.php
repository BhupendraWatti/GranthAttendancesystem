<?php

namespace App\Services;

use App\Models\PunchLogModel;
use App\Models\AttendanceDailyModel;
use App\Models\EmployeeModel;

/**
 * AttendanceService — Core Business Logic
 * 
 * Processes raw punch data into daily attendance records.
 * Handles status determination, late calculation, and all business rules.
 *
 * RULES:
 * - Multiple punches → first_in = min(punch_times), last_out = max(punch_times)
 * - No punches → Absent
 * - Single punch → Half-day
 * - Present: work_minutes >= required_minutes * 0.90
 * - Half-day: work_minutes >= required_minutes * 0.50
 * - Absent: work_minutes < required_minutes * 0.50
 * 
 * WORK HOURS:
 * - Full-time: 510 minutes (8.5 hours)
 * - Intern: 330 minutes (5.5 hours)
 * 
 * DEPENDENCY INJECTION:
 * - Models can be injected for testing
 * - Services retrieved from CI container where applicable
 */
class AttendanceService
{
    private PunchLogModel $punchLogModel;
    private AttendanceDailyModel $attendanceModel;
    private EmployeeModel $employeeModel;
    private ValidationService $validationService;
    private \App\Models\HolidayModel $holidayModel;
    private \App\Models\LeaveRequestModel $leaveRequestModel;
    private \App\Models\AttendanceOverrideModel $overrideModel;
    private \App\Services\LeaveService $leaveService;

    private int $fullTimeMinutes;
    private int $internMinutes;
    private int $fullTimeHalfDayMinutes;
    private int $internHalfDayMinutes;
    private string $officeStartTime;

    /**
     * Constructor with dependency injection
     * 
     * @param PunchLogModel|null $punchLogModel If null, instantiates normally
     * @param AttendanceDailyModel|null $attendanceModel If null, instantiates normally
     * @param EmployeeModel|null $employeeModel If null, instantiates normally
     */
    public function __construct(
        ?PunchLogModel $punchLogModel = null,
        ?AttendanceDailyModel $attendanceModel = null,
        ?EmployeeModel $employeeModel = null
    ) {
        helper('attendance');
        $this->punchLogModel = $punchLogModel ?? new PunchLogModel();
        $this->attendanceModel = $attendanceModel ?? new AttendanceDailyModel();
        $this->employeeModel = $employeeModel ?? new EmployeeModel();
        $this->validationService = new ValidationService();
        $this->holidayModel = new \App\Models\HolidayModel();
        $this->leaveRequestModel = new \App\Models\LeaveRequestModel();
        $this->overrideModel = new \App\Models\AttendanceOverrideModel();
        $this->leaveService = new \App\Services\LeaveService();

        $this->fullTimeMinutes = (int) env('FULL_TIME_PRESENT_MINUTES', 510);
        $this->internMinutes = (int) env('INTERN_PRESENT_MINUTES', 330);
        $this->fullTimeHalfDayMinutes = (int) env('FULL_TIME_HALF_DAY_MINUTES', 255);
        $this->internHalfDayMinutes = (int) env('INTERN_HALF_DAY_MINUTES', 165);
        $this->officeStartTime = env('OFFICE_START_TIME', '10:00');
    }

    /**
     * Process all attendance for a specific date
     * Processes every active employee, even those with no punches (marks as absent)
     *
     * @param string $date Date in Y-m-d format
     * @return array Processing summary
     */
    public function processDay(string $date): array
    {
        log_message('info', "[AttendanceService] Processing attendance for date: {$date}");

        // 1. Check if date is a global holiday
        $isGlobalHoliday = $this->holidayModel->isHoliday($date);

        // 2. Check if date is a weekend off
        $isWeekendOff = isWeekendOff($date);

        // Get all punches for this date
        $allPunches = $this->punchLogModel->getAllPunchesForDate($date);

        // Group punches by employee code
        $grouped = $this->groupPunchesByEmployee($allPunches);

        // Get all active employees
        $employees = $this->employeeModel->getActive();

        // Get all existing records to check for locks
        $existingRecords = $this->attendanceModel->where('date', $date)->findAll();
        $lockedMap = [];
        foreach ($existingRecords as $rec) {
            if (!empty($rec['is_locked'])) {
                $lockedMap[$rec['emp_code']] = true;
            }
        }

        // Get all overrides for this date
        $overrides = $this->overrideModel->where('attendance_date', $date)->findAll();
        $overrideMap = [];
        foreach ($overrides as $o) {
            $overrideMap[$o['emp_code']] = $o['override_type'];
        }

        $processed = 0;
        $errors = [];

        foreach ($employees as $employee) {
            $empCode = $employee['emp_code'];

            // SKIP processing if the record is locked (manual edit protection)
            if (isset($lockedMap[$empCode])) {
                log_message('debug', "[AttendanceService] Skipping locked record for {$empCode} on {$date}");
                continue;
            }

            $punches = $grouped[$empCode] ?? [];
            $employeeType = $employee['employee_type'] ?? 'full_time';
            $overrideMode = $overrideMap[$empCode] ?? null;

            // 3. Logic for Holiday/Weekend vs Punches/Overrides
            $dayType = 'working_day';
            if ($isGlobalHoliday) $dayType = 'holiday';
            elseif ($isWeekendOff) $dayType = 'weekend';

            // If it's a non-working day (holiday/weekend), AND no punches, AND no manual override -> mark as dayType
            if (($isGlobalHoliday || $isWeekendOff) && empty($punches) && !$overrideMode) {
                $status = ($isGlobalHoliday) ? 'holiday' : 'absent';
                
                $result = [
                    'emp_code' => $empCode,
                    'date' => $date,
                    'status' => $status,
                    'attendance_status' => $status,
                    'day_type' => $dayType,
                    'work_mode' => null,
                    'work_minutes' => 0,
                    'punch_count' => 0,
                    'required_minutes' => $this->getRequiredMinutes($employeeType, $employee),
                    'employee_type' => $employeeType,
                ];
            } else {
                $result = $this->processEmployee($empCode, $date, $punches, $employeeType, $overrideMode, $dayType, $employee);
            }

            // 4. CREDIT COMP-OFF if work detected on weekend/holiday AND not already credited
            if (($dayType === 'weekend' || $dayType === 'holiday') && ($result['work_minutes'] ?? 0) > 0) {
                // Check existing record for credit flag
                $existing = $this->attendanceModel->where('emp_code', $empCode)->where('date', $date)->first();
                if (!$existing || empty($existing['is_compoff_credited'])) {
                    $this->leaveService->creditCompOff($empCode, $date);
                    $result['is_compoff_credited'] = 1;
                } else {
                    $result['is_compoff_credited'] = 1; // Preserve flag
                }
            }

            // Validate the result
            $validationErrors = $this->validationService->validateAttendance($result);
            if (!empty($validationErrors)) {
                $result['validation_errors'] = $validationErrors;
                $errors = array_merge($errors, $validationErrors);
            }

            // Upsert the attendance record
            $this->attendanceModel->upsertAttendance($result);
            $processed++;
        }

        log_message('info', "[AttendanceService] Processed {$processed} employees for {$date}, " . count($errors) . " validation issues");

        return [
            'date' => $date,
            'processed' => $processed,
            'errors' => $errors,
        ];
    }

    /**
     * Process attendance for a single employee on a date
     *
     * @param string $empCode Employee code
     * @param string $date Date in Y-m-d format
     * @param array $punches Array of punch records for this employee/date
     * @param string $employeeType 'full_time' or 'intern'
     * @param string|null $overrideMode Manual override ('wfh' or 'wfo')
     * @param string $dayType 'working_day', 'weekend', or 'holiday'
     * @param array|null $employee Entire employee array containing shift parameters
     * @return array Attendance record ready for upsert
     */
    public function processEmployee(
        string $empCode,
        string $date,
        array $punches,
        string $employeeType = 'full_time',
        ?string $overrideMode = null,
        string $dayType = 'working_day',
        ?array $employee = null
    ): array {
        $requiredMinutes = $this->getRequiredMinutes($employeeType, $employee);
        $punchCount = count($punches);

        // No punches → Check for approved Leave before marking Absent
        if ($punchCount === 0) {
            $leave = $this->leaveRequestModel->where('emp_code', $empCode)
                ->where('status', 'approved')
                ->where("'{$date}' BETWEEN from_date AND to_date")
                ->first();

            $status = 'absent';
            if ($leave) {
                $status = ($leave['leave_type'] === 'comp_off') ? 'comp_off' : 'leave';
            }

            return [
                'emp_code' => $empCode,
                'date' => $date,
                'first_in' => null,
                'last_out' => null,
                'work_minutes' => 0,
                'late_minutes' => 0,
                'status' => $status,
                'attendance_status' => $status,
                'day_type' => $dayType,
                'work_mode' => $overrideMode,
                'punch_count' => 0,
                'employee_type' => $employeeType,
                'required_minutes' => $requiredMinutes,
                'validation_errors' => null,
            ];
        }

        // Sort punches chronologically
        usort($punches, function ($a, $b) {
            return strtotime($a['punch_time']) - strtotime($b['punch_time']);
        });

        // First IN = first punch, Last OUT = last punch
        $firstIn = $punches[0]['punch_time'];
        $lastOut = $punches[count($punches) - 1]['punch_time'];

        // Calculate raw work minutes
        $workMinutes = $this->calculateWorkMinutes($firstIn, $lastOut);

        // Single punch → Half-day (force status)
        if ($punchCount === 1) {
            $lateMinutes = $this->calculateLateMinutes($firstIn, $date, $employee);

            return [
                'emp_code' => $empCode,
                'date' => $date,
                'first_in' => $firstIn,
                'last_out' => null, // No OUT punch
                'work_minutes' => 0,
                'late_minutes' => $lateMinutes,
                'status' => 'half_day',
                'attendance_status' => 'half_day',
                'day_type' => $dayType,
                'work_mode' => $overrideMode ?: 'wfo',
                'punch_count' => 1,
                'employee_type' => $employeeType,
                'required_minutes' => $requiredMinutes,
                'validation_errors' => json_encode([['type' => 'missing_out', 'message' => 'Only one punch recorded']]),
            ];
        }

        // Multiple punches — calculate everything
        $lateMinutes = $this->calculateLateMinutes($firstIn, $date, $employee);
        $status = $this->determineStatus($workMinutes, $requiredMinutes, $employee);

        return [
            'emp_code' => $empCode,
            'date' => $date,
            'first_in' => $firstIn,
            'last_out' => $lastOut,
            'work_minutes' => max(0, $workMinutes),
            'late_minutes' => $lateMinutes,
            'status' => $status,
            'attendance_status' => $status,
            'day_type' => $dayType,
            'work_mode' => $overrideMode ?: 'wfo',
            'punch_count' => $punchCount,
            'employee_type' => $employeeType,
            'required_minutes' => $requiredMinutes,
            'validation_errors' => null,
        ];
    }

    /**
     * Determine attendance status based on work minutes vs required
     *
     * @param int $workMinutes Actual work minutes
     * @param int $requiredMinutes Required minutes for this employee type
     * @param array|null $employee Contains shift parameters
     * @return string 'present', 'half_day', or 'absent'
     */
    public function determineStatus(int $workMinutes, int $requiredMinutes, ?array $employee = null): string
    {
        $graceMinutes = 30;
        $presentThreshold = $requiredMinutes - $graceMinutes;

        $baseHalfDayThreshold = $requiredMinutes === $this->internMinutes
            ? $this->internHalfDayMinutes
            : $this->fullTimeHalfDayMinutes;
        
        $halfDayThreshold = $baseHalfDayThreshold - $graceMinutes;

        if ($workMinutes >= $presentThreshold) {
            return 'present';
        }

        if ($workMinutes >= $halfDayThreshold) {
            return 'half_day';
        }

        return 'absent';
    }

    /**
     * Calculate work minutes between two datetimes
     */
    private function calculateWorkMinutes(string $firstIn, string $lastOut): int
    {
        $in = new \DateTime($firstIn);
        $out = new \DateTime($lastOut);
        $diff = $out->getTimestamp() - $in->getTimestamp();

        return (int) floor($diff / 60);
    }

    /**
     * Calculate late minutes (how much after office start time)
     *
     * @param string $firstIn First punch time (Y-m-d H:i:s)
     * @param string $date Date (Y-m-d)
     * @param array|null $employee Contains shift start time and flexible status
     * @return int Late minutes (0 if on time or early)
     */
    private function calculateLateMinutes(string $firstIn, string $date, ?array $employee = null): int
    {
        // Flexible employees do not get late minutes penalized as long as they hit hours
        if (!empty($employee['is_flexible'])) {
            return 0;
        }

        $startTimeStr = !empty($employee['start_time']) ? $employee['start_time'] : $this->officeStartTime;
        $officeStart = new \DateTime($date . ' ' . $startTimeStr . ':00');
        $punchIn = new \DateTime($firstIn);

        if ($punchIn <= $officeStart) {
            return 0;
        }

        $diff = $punchIn->getTimestamp() - $officeStart->getTimestamp();
        return (int) ceil($diff / 60);
    }

    /**
     * Get required work minutes based on employee type and shift settings
     */
    public function getRequiredMinutes(string $employeeType, ?array $employee = null): int
    {
        if (!empty($employee['expected_hours'])) {
            return (int) ($employee['expected_hours'] * 60);
        }
        return ($employeeType === 'intern') ? $this->internMinutes : $this->fullTimeMinutes;
    }

    /**
     * Group punch records by employee code
     */
    private function groupPunchesByEmployee(array $punches): array
    {
        $grouped = [];
        foreach ($punches as $punch) {
            $grouped[$punch['emp_code']][] = $punch;
        }
        return $grouped;
    }

    /**
     * Process a date range
     */
    public function processDateRange(string $fromDate, string $toDate): array
    {
        $results = [];
        $current = new \DateTime($fromDate);
        $end = new \DateTime($toDate);

        while ($current <= $end) {
            $date = $current->format('Y-m-d');
            $results[$date] = $this->processDay($date);
            $current->modify('+1 day');
        }

        return $results;
    }

    /**
     * Calculate monthly summary statistics from attendance records
     * 
     * Aggregates attendance data by employee:
     * - present_days, half_days, absent_days
     * - total_work_minutes, total_late_minutes, late_count
     * 
     * @param array $records Array of attendance records from getAllMonthly()
     * @return array Summary array grouped by employee
     */
    public function calculateMonthlySummary(array $records): array
    {
        $byEmployee = [];

        foreach ($records as $record) {
            $empCode = $record['emp_code'];

            if (!isset($byEmployee[$empCode])) {
                $byEmployee[$empCode] = [
                    'emp_code' => $empCode,
                    'name' => $record['name'] ?? $empCode,
                    'present_days' => 0,
                    'half_days' => 0,
                    'absent_days' => 0,
                    'total_work_minutes' => 0,
                    'total_late_minutes' => 0,
                    'late_count' => 0,
                ];
            }

            switch ($record['status']) {
                case 'present':
                    $byEmployee[$empCode]['present_days']++;
                    break;
                case 'half_day':
                    $byEmployee[$empCode]['half_days']++;
                    break;
                case 'absent':
                    $byEmployee[$empCode]['absent_days']++;
                    break;
                case 'leave':
                    if (!isset($byEmployee[$empCode]['leave_days']))
                        $byEmployee[$empCode]['leave_days'] = 0;
                    $byEmployee[$empCode]['leave_days']++;
                    break;
                case 'holiday':
                    if (!isset($byEmployee[$empCode]['holiday_days']))
                        $byEmployee[$empCode]['holiday_days'] = 0;
                    $byEmployee[$empCode]['holiday_days']++;
                    break;
            }

            $byEmployee[$empCode]['total_work_minutes'] += (int) ($record['work_minutes'] ?? 0);
            $lateMin = (int) ($record['late_minutes'] ?? 0);
            $byEmployee[$empCode]['total_late_minutes'] += $lateMin;
            if ($lateMin > 0) {
                $byEmployee[$empCode]['late_count']++;
            }
        }

        return array_values($byEmployee);
    }
}
