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

    private int $fullTimeMinutes;
    private int $internMinutes;
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
        $this->punchLogModel      = $punchLogModel ?? new PunchLogModel();
        $this->attendanceModel  = $attendanceModel ?? new AttendanceDailyModel();
        $this->employeeModel  = $employeeModel ?? new EmployeeModel();
        $this->validationService = new ValidationService();

        $this->fullTimeMinutes = (int) env('FULL_TIME_MINUTES', 510);
        $this->internMinutes   = (int) env('INTERN_MINUTES', 330);
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

        // Get all punches for this date
        $allPunches = $this->punchLogModel->getAllPunchesForDate($date);

        // Group punches by employee code
        $grouped = $this->groupPunchesByEmployee($allPunches);

        // Get all active employees
        $employees = $this->employeeModel->getActive();

        $processed = 0;
        $errors = [];

        foreach ($employees as $employee) {
            $empCode = $employee['emp_code'];
            $punches = $grouped[$empCode] ?? [];
            $employeeType = $employee['employee_type'] ?? 'full_time';

            $result = $this->processEmployee($empCode, $date, $punches, $employeeType);

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
            'date'      => $date,
            'processed' => $processed,
            'errors'    => $errors,
        ];
    }

    /**
     * Process attendance for a single employee on a date
     *
     * @param string $empCode Employee code
     * @param string $date Date in Y-m-d format
     * @param array $punches Array of punch records for this employee/date
     * @param string $employeeType 'full_time' or 'intern'
     * @return array Attendance record ready for upsert
     */
    public function processEmployee(string $empCode, string $date, array $punches, string $employeeType = 'full_time'): array
    {
        $requiredMinutes = $this->getRequiredMinutes($employeeType);
        $punchCount = count($punches);

        // No punches → Absent
        if ($punchCount === 0) {
            return [
                'emp_code'         => $empCode,
                'date'             => $date,
                'first_in'         => null,
                'last_out'         => null,
                'work_minutes'     => 0,
                'late_minutes'     => 0,
                'status'           => 'absent',
                'punch_count'      => 0,
                'employee_type'    => $employeeType,
                'required_minutes' => $requiredMinutes,
                'validation_errors' => null,
            ];
        }

        // Sort punches chronologically
        usort($punches, function ($a, $b) {
            return strtotime($a['punch_time']) - strtotime($b['punch_time']);
        });

        // First IN = first punch, Last OUT = last punch
        $firstIn  = $punches[0]['punch_time'];
        $lastOut  = $punches[count($punches) - 1]['punch_time'];

        // Calculate work minutes
        $workMinutes = $this->calculateWorkMinutes($firstIn, $lastOut);

        // Single punch → Half-day (force status)
        if ($punchCount === 1) {
            $lateMinutes = $this->calculateLateMinutes($firstIn, $date);

            return [
                'emp_code'         => $empCode,
                'date'             => $date,
                'first_in'         => $firstIn,
                'last_out'         => null, // No OUT punch
                'work_minutes'     => 0,
                'late_minutes'     => $lateMinutes,
                'status'           => 'half_day',
                'punch_count'      => 1,
                'employee_type'    => $employeeType,
                'required_minutes' => $requiredMinutes,
                'validation_errors' => json_encode([['type' => 'missing_out', 'message' => 'Only one punch recorded']]),
            ];
        }

        // Multiple punches — calculate everything
        $lateMinutes = $this->calculateLateMinutes($firstIn, $date);
        $status = $this->determineStatus($workMinutes, $requiredMinutes);

        return [
            'emp_code'         => $empCode,
            'date'             => $date,
            'first_in'         => $firstIn,
            'last_out'         => $lastOut,
            'work_minutes'     => max(0, $workMinutes),
            'late_minutes'     => $lateMinutes,
            'status'           => $status,
            'punch_count'      => $punchCount,
            'employee_type'    => $employeeType,
            'required_minutes' => $requiredMinutes,
            'validation_errors' => null,
        ];
    }

    /**
     * Determine attendance status based on work minutes vs required
     *
     * @param int $workMinutes Actual work minutes
     * @param int $requiredMinutes Required minutes for this employee type
     * @return string 'present', 'half_day', or 'absent'
     */
    public function determineStatus(int $workMinutes, int $requiredMinutes): string
    {
        $percentage = ($requiredMinutes > 0) ? ($workMinutes / $requiredMinutes) : 0;

        if ($percentage >= 0.90) {
            return 'present';
        }

        if ($percentage >= 0.50) {
            return 'half_day';
        }

        return 'absent';
    }

    /**
     * Calculate work minutes between two datetimes
     */
    private function calculateWorkMinutes(string $firstIn, string $lastOut): int
    {
        $in  = new \DateTime($firstIn);
        $out = new \DateTime($lastOut);
        $diff = $out->getTimestamp() - $in->getTimestamp();

        return (int) floor($diff / 60);
    }

    /**
     * Calculate late minutes (how much after office start time)
     *
     * @param string $firstIn First punch time (Y-m-d H:i:s)
     * @param string $date Date (Y-m-d)
     * @return int Late minutes (0 if on time or early)
     */
    private function calculateLateMinutes(string $firstIn, string $date): int
    {
        $officeStart = new \DateTime($date . ' ' . $this->officeStartTime . ':00');
        $punchIn     = new \DateTime($firstIn);

        if ($punchIn <= $officeStart) {
            return 0;
        }

        $diff = $punchIn->getTimestamp() - $officeStart->getTimestamp();
        return (int) ceil($diff / 60);
    }

    /**
     * Get required work minutes based on employee type
     */
    public function getRequiredMinutes(string $employeeType): int
    {
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
        $end     = new \DateTime($toDate);

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
                    'emp_code'              => $empCode,
                    'name'                 => $record['name'] ?? $empCode,
                    'present_days'         => 0,
                    'half_days'            => 0,
                    'absent_days'          => 0,
                    'total_work_minutes'   => 0,
                    'total_late_minutes'    => 0,
                    'late_count'          => 0,
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
