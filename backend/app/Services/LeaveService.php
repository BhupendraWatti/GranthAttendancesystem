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
     * Apply for leave
     */
    public function apply(array $data): array
    {
        $empCode = $data['emp_code'];
        $fromDate = $data['from_date'];
        $toDate = $data['to_date'];
        $leaveType = $data['leave_type'];

        // 1. Validate: No past date
        if ($fromDate < date('Y-m-d')) {
            throw new \RuntimeException("Cannot apply for leave in the past.");
        }

        // 2. Validate: No overlap
        if ($this->leaveRequestModel->hasOverlap($empCode, $fromDate, $toDate)) {
            throw new \RuntimeException("This request overlaps with an existing leave request.");
        }

        // 3. Auto-credit balance if missing
        $this->ensureLeaveBalance($empCode, $leaveType);

        // 4. Calculate duration
        $duration = $this->calculateDuration($fromDate, $toDate, $data['from_session'], $data['to_session']);
        
        if ($duration <= 0) {
            throw new \RuntimeException("Selected dates are holidays or weekends.");
        }

        // 5. Check balance
        $balance = $this->leaveBalanceModel->getBalance($empCode, $leaveType);
        if ($balance['remaining'] < $duration) {
            throw new \RuntimeException("Insufficient leave balance. Available: {$balance['remaining']} days.");
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

        // Update balance
        $balance = $this->leaveBalanceModel->getBalance($request['emp_code'], $request['leave_type']);
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
     * Calculate duration omitting weekends and holidays
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
            
            // Skip weekends (assuming Saturday/Sunday)
            if ($date->format('N') >= 6) {
                continue;
            }

            // Skip holidays
            if ($this->holidayModel->isHoliday($ymd)) {
                continue;
            }

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
     * Auto-credit balance (Default 12 days per year)
     */
    private function ensureLeaveBalance(string $empCode, string $leaveType): void
    {
        $existing = $this->leaveBalanceModel->getBalance($empCode, $leaveType);
        if (!$existing) {
            $total = 12.0; // Default
            $this->leaveBalanceModel->insert([
                'emp_code'   => $empCode,
                'leave_type' => $leaveType,
                'total'      => $total,
                'used'       => 0,
                'remaining'  => $total,
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
            
            $data = [
                'emp_code' => $request['emp_code'],
                'date'     => $ymd,
                'status'   => 'leave',
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
