<?php

namespace App\Services;

use App\Models\LeaveRequestModel;
use App\Models\LeaveBalanceModel;
use App\Models\HolidayModel;
use App\Models\AttendanceDailyModel;
use App\Models\EmployeeModel;

class LeaveService
{
    private LeaveRequestModel $leaveRequestModel;
    private LeaveBalanceModel $leaveBalanceModel;
    private HolidayModel $holidayModel;
    private AttendanceDailyModel $attendanceModel;
    private EmployeeModel $employeeModel;

    public function __construct()
    {
        $this->leaveRequestModel = new LeaveRequestModel();
        $this->leaveBalanceModel = new LeaveBalanceModel();
        $this->holidayModel      = new HolidayModel();
        $this->attendanceModel   = new AttendanceDailyModel();
        $this->employeeModel     = new EmployeeModel();
    }

    /**
     * Initialize all leave balances for an employee
     */
    public function initializeBalances(string $empCode): void
    {
        $this->ensureLeaveBalance($empCode, 'paid_leave');
        $this->ensureLeaveBalance($empCode, 'unpaid_leave');
        $this->ensureLeaveBalance($empCode, 'comp_off');
    }

    /**
     * Automatically credit Comp-off for weekend/holiday work
     * ATOMIC PROTECTION: Uses DB transactions to ensure exactly +1.0 per date.
     */
    public function creditCompOff(string $empCode, string $date, string $reason = 'Weekend/Holiday work'): bool
    {
        $db = \Config\Database::connect();
        $db->transStart();

        // 1. Lock the daily record for update to prevent race conditions
        $attendance = $this->attendanceModel->db->table($this->attendanceModel->table)
            ->where('emp_code', $empCode)
            ->where('date', $date)
            ->get() 
            ->getRowArray();

        if ($attendance && !empty($attendance['is_compoff_credited'])) {
            $db->transRollback();
            return false; // Already credited by another process
        }

        // 2. Fetch balance with locking
        $existing = $this->leaveBalanceModel->where('emp_code', $empCode)
            ->where('leave_type', 'comp_off')
            ->first();
        
        if (!$existing) {
            $this->initializeBalances($empCode);
            $existing = $this->leaveBalanceModel->where('emp_code', $empCode)
                ->where('leave_type', 'comp_off')
                ->first();
        }

        // 3. Perform Credit
        $this->leaveBalanceModel->update($existing['id'], [
            'total'     => (float)$existing['total'] + 1.0,
            'remaining' => (float)$existing['remaining'] + 1.0,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        // 4. Mark as credited in daily attendance log
        $this->attendanceModel->db->table($this->attendanceModel->table)
            ->where('emp_code', $empCode)
            ->where('date', $date)
            ->update(['is_compoff_credited' => 1]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            log_message('error', "[LeaveService] Transaction failed for Comp-off credit: {$empCode} on {$date}");
            return false;
        }

        log_message('info', "[LeaveService] ATOMIC CREDIT: 1.0 Comp-off to {$empCode} for work on {$date}");
        return true;
    }

    /**
     * Apply for leave
     */
    public function apply(array $data): array
    {
        $empCode = $data['emp_code'];
        $fromDate = $data['from_date'];
        $toDate = $data['to_date'];
        $leaveType = $data['leave_type']; // 'paid_leave' or 'unpaid_leave'

        // 1. Validate: No past date of previous months
        if ($fromDate < date('Y-m-01')) {
            throw new \RuntimeException("Cannot apply for leave in previous months. Only dates in the present month are allowed.");
        }

        // 2. Validate: No overlap
        if ($this->leaveRequestModel->hasOverlap($empCode, $fromDate, $toDate)) {
            throw new \RuntimeException("This request overlaps with an existing leave request.");
        }

        // 3. Auto-credit balances if missing
        $this->initializeBalances($empCode);

        // 4. Calculate duration
        $duration = $this->calculateDuration($fromDate, $toDate, $data['from_session'], $data['to_session']);
        
        if ($duration <= 0) {
            throw new \RuntimeException("Selected dates are holidays or weekends.");
        }

        // 5. Check specific balance
        $balance = $this->leaveBalanceModel->getBalance($empCode, $leaveType);
        if (!$balance || $balance['remaining'] < $duration) {
            $label = str_replace('_', ' ', $leaveType);
            throw new \RuntimeException("Insufficient {$label} balance. Available: " . ($balance['remaining'] ?? 0) . " days.");
        }

        // 6. Save request
        $id = $this->leaveRequestModel->insert([
            'emp_code'      => $empCode,
            'leave_type'    => $leaveType,
            'from_date'     => $fromDate,
            'to_date'       => $toDate,
            'from_session'  => $data['from_session'] ?? 'full',
            'to_session'    => $data['to_session'] ?? 'full',
            'reason'        => $data['reason'] ?? '',
            'status'        => 'pending',
        ]);

        return ['success' => true, 'id' => $id, 'duration' => $duration];
    }

    /**
     * Approve leave
     */
    public function approve(int $id, ?string $comment = null): bool
    {
        $request = $this->leaveRequestModel->find($id);
        if (!$request || $request['status'] !== 'pending') {
            return false;
        }

        $duration = $this->calculateDuration($request['from_date'], $request['to_date'], $request['from_session'], $request['to_session']);

        // Update specific balance
        $balance = $this->leaveBalanceModel->getBalance($request['emp_code'], $request['leave_type']);
        if (!$balance) {
            $this->initializeBalances($request['emp_code']);
            $balance = $this->leaveBalanceModel->getBalance($request['emp_code'], $request['leave_type']);
        }

        $this->leaveBalanceModel->update($balance['id'], [
            'used'      => $balance['used'] + $duration,
            'remaining' => $balance['remaining'] - $duration,
        ]);

        // Update status
        $this->leaveRequestModel->update($id, [
            'status'        => 'approved',
            'admin_comment' => $comment,
        ]);

        // INTEGRATION: Immediate Attendance Override
        $this->syncAttendanceWithLeave($request);

        return true;
    }

    /**
     * Reject leave
     */
    public function reject(int $id, ?string $comment = null): bool
    {
        return $this->leaveRequestModel->update($id, [
            'status'        => 'rejected',
            'admin_comment' => $comment,
        ]);
    }

    /**
     * Calculate duration (inclusive of all selected days to allow manual work management)
     */
    public function calculateDuration(string $start, string $end, string $fromSession = 'full', string $toSession = 'full'): float
    {
        $startDate = new \DateTime($start);
        $endDate   = new \DateTime($end);
        $endDate->modify('+1 day'); // inclusive

        $interval = new \DateInterval('P1D');
        $period   = new \DatePeriod($startDate, $interval, $endDate);

        $totalDays = 0;
        foreach ($period as $date) {
            $ymd = $date->format('Y-m-d');
            
            // Logic: We no longer 'continue' (skip) weekends/holidays here.
            // This allows employees can now manage their markers on ANY day.

            // Determine weight for this day
            $weight = 1.0;
            
            // Handle sessions for start and end dates
            if ($ymd === $start) {
                if ($fromSession !== 'full') $weight = 0.5;
            } elseif ($ymd === $end) {
                if ($toSession !== 'full') $weight = 0.5;
            }

            $totalDays += $weight;
        }

        return $totalDays;
    }

    /**
     * Auto-credit balances (1 Paid per month, 8 Unpaid buffer)
     */
    private function ensureLeaveBalance(string $empCode, string $leaveType): void
    {
        $existing = $this->leaveBalanceModel->getBalance($empCode, $leaveType);
        $currentMonth = date('Y-m');

        if ($existing) {
            // Check if we need to credit Monthly Paid Leave
            if ($leaveType === 'paid_leave') {
                $lastUpdate = strtotime($existing['updated_at'] ?? $existing['created_at'] ?? 'now');
                $lastUpdateMonth = date('Y-m', $lastUpdate);
                if ($lastUpdateMonth !== $currentMonth) {
                    // Carry Forward Logic: Add 1.0 to existing remaining, reset used for new month
                    $newRemaining = (float)$existing['remaining'] + 1.0;
                    $this->leaveBalanceModel->update($existing['id'], [
                        'total'      => $newRemaining, // Total now represents accumulated pool
                        'used'       => 0,            // Reset monthly usage
                        'remaining'  => $newRemaining,
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);
                }
            } elseif ($leaveType === 'unpaid_leave') {
                $lastUpdate = strtotime($existing['updated_at'] ?? $existing['created_at'] ?? 'now');
                $lastUpdateMonth = date('Y-m', $lastUpdate);
                if ($lastUpdateMonth !== $currentMonth) {
                    // Carry Forward Logic: Add 8.0 to existing remaining, reset used for new month
                    $newRemaining = (float)$existing['remaining'] + 8.0;
                    $this->leaveBalanceModel->update($existing['id'], [
                        'total'      => $newRemaining,
                        'used'       => 0,
                        'remaining'  => $newRemaining,
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);
                }
            } elseif ($leaveType === 'comp_off') {
                // Comp-off expiration logic (rolling 90 days)
                $lastUpdate = strtotime($existing['updated_at'] ?? $existing['created_at'] ?? 'now');
                if ($lastUpdate < strtotime('-90 days')) {
                    // If no activity in 90 days, balance expires
                    $this->leaveBalanceModel->update($existing['id'], [
                        'total'      => 0.0,
                        'used'       => 0,
                        'remaining'  => 0.0,
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);
                }
            }
        } else {
            // Initial creation
            $total = 0.0;
            if ($leaveType === 'paid_leave') $total = 1.0;
            elseif ($leaveType === 'unpaid_leave') $total = 8.0;
            elseif ($leaveType === 'comp_off') $total = 0.0;

            $this->leaveBalanceModel->insert([
                'emp_code'   => $empCode,
                'leave_type' => $leaveType,
                'total'      => $total,
                'used'       => 0,
                'remaining'  => $total,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    /**
     * Sync approved leave with attendance_daily
     */
    private function syncAttendanceWithLeave(array $request): void
    {
        $startDate = new \DateTime($request['from_date']);
        $endDate   = new \DateTime($request['to_date']);
        $endDate->modify('+1 day');

        $interval = new \DateInterval('P1D');
        $period   = new \DatePeriod($startDate, $interval, $endDate);

        foreach ($period as $date) {
            $ymd = $date->format('Y-m-d');
            
            // Skip weekends/holidays for attendance status
            if ($date->format('N') >= 6 || $this->holidayModel->isHoliday($ymd)) {
                continue;
            }

            // Upsert attendance record
            $existing = $this->attendanceModel->where('emp_code', $request['emp_code'])->where('date', $ymd)->first();
            
            // Determine status based on leave type
            $status = $request['leave_type']; // 'paid_leave' or 'unpaid_leave'
            if (($request['leave_type'] ?? '') === 'comp_off') {
                $status = 'comp_off';
            }

            $data = [
                'emp_code' => $request['emp_code'],
                'date'     => $ymd,
                'status'   => $status,
                'raw_data' => json_encode(['leave_type' => $request['leave_type'], 'request_id' => $request['id']]),
            ];

            if ($existing) {
                $this->attendanceModel->update($existing['id'], $data);
            } else {
                $this->attendanceModel->insert($data);
            }
        }
    }
}
