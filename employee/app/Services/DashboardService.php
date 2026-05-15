<?php

namespace App\Services;

use App\Models\EmployeeModel;
use App\Models\AttendanceDailyModel;
use App\Models\PunchLogModel;

/**
 * DashboardService — Metrics Aggregation
 * 
 * Provides pre-computed dashboard data for the React frontend.
 * All business logic stays here — frontend only renders.
 */
class DashboardService
{
    private EmployeeModel $employeeModel;
    private AttendanceDailyModel $attendanceModel;
    private PunchLogModel $punchLogModel;

    public function __construct()
    {
        $this->employeeModel   = new EmployeeModel();
        $this->attendanceModel = new AttendanceDailyModel();
        $this->punchLogModel   = new PunchLogModel();
    }

    /**
     * Get dashboard summary for today
     *
     * @param string|null $date Date to summarize (default: today)
     * @return array Dashboard metrics
     */
    public function getSummary(?string $date = null): array
    {
        $date = $date ?? date('Y-m-d');

        // Total and active employees
        $totalEmployees  = $this->employeeModel->countActive();

        // Attendance counts
        $statusCounts = $this->attendanceModel->countByStatus($date);
        $lateCount    = $this->attendanceModel->countLate($date);

        // Calculate counts
        $presentCount = $statusCounts['present'] ?? 0;
        $halfDayCount = $statusCounts['half_day'] ?? 0;
        $absentCount  = $statusCounts['absent'] ?? 0;
        $wfhCount     = $statusCounts['work_from_home'] ?? 0;

        // If attendance hasn't been processed yet, everyone is absent
        $processedCount = $presentCount + $halfDayCount + $absentCount + $wfhCount;
        if ($processedCount === 0 && $totalEmployees > 0) {
            $absentCount = $totalEmployees;
        }

        return [
            'date'             => $date,
            'total_employees'  => $totalEmployees,
            'active_employees' => $totalEmployees,
            'present_today'    => $presentCount,
            'half_day_today'   => $halfDayCount,
            'absent_today'     => $absentCount,
            'wfh_today'        => $wfhCount,
            'late_today'       => $lateCount,
            'attendance_rate'  => $totalEmployees > 0
                ? round(($presentCount + $wfhCount + $halfDayCount * 0.5) / $totalEmployees * 100, 1)
                : 0,
        ];
    }

    /**
     * Get live punch feed (recent punches)
     *
     * @param int $limit Number of records to return
     * @return array Recent punch entries
     */
    public function getLivePunches(int $limit = 20, ?string $forDate = null): array
    {
        $punches = ($forDate !== null && $forDate !== '')
            ? $this->punchLogModel->getLatestPunchesForDate($forDate, $limit)
            : $this->punchLogModel->getLatestPunches($limit);

        return array_map(function ($punch) {
            return [
                'id'         => $punch['id'],
                'emp_code'   => $punch['emp_code'],
                'name'       => $punch['name'] ?? $punch['emp_code'],
                'punch_time' => $punch['punch_time'],
                'source'     => $punch['source'],
                'time_ago'   => $this->timeAgo($punch['punch_time']),
            ];
        }, $punches);
    }

    /**
     * Get attendance table data for today
     *
     * @param string|null $date Date (default: today)
     * @return array Employee attendance records
     */
    public function getAttendanceTable(?string $date = null): array
    {
        $date = $date ?? date('Y-m-d');
        return $this->attendanceModel->getForDate($date);
    }

    /**
     * Get personal summary for an employee
     */
    public function getPersonalSummary(string $empCode): array
    {
        $year = (int) date('Y');
        $month = (int) date('m');
        $monthRows = $this->attendanceModel->getMonthly($empCode, $year, $month);

        $totalMinutesMonth = 0;
        foreach ($monthRows as $r) {
            $totalMinutesMonth += (int) ($r['work_minutes'] ?? 0);
        }

        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $todayDay = (int)date('d');
        
        $workingDays = 0;
        $satCount = 0;
        for ($d = 1; $d <= $todayDay; $d++) {
            $time = mktime(0, 0, 0, $month, $d, $year);
            $dow = (int)date('w', $time);
            
            if ($dow === 0) continue; // Sunday OFF
            if ($dow === 6) { // Saturday
                $satCount++;
                if ($satCount === 1 || $satCount === 3) continue; // 1st/3rd Sat OFF
            }
            $workingDays++;
        }
        $requiredHoursMonth = $workingDays * 8.5;
        $totalHoursMonth = round($totalMinutesMonth / 60, 2);

        $counts = ['present' => 0, 'half_day' => 0, 'absent' => 0, 'work_from_home' => 0];
        foreach ($monthRows as $r) {
            $status = $r['status'] ?? '';
            if (isset($counts[$status])) {
                $counts[$status]++;
            }
        }

        $todayRow = $this->attendanceModel->where('emp_code', $empCode)->where('date', date('Y-m-d'))->first();

        return [
            'totalHoursMonth' => $totalHoursMonth,
            'requiredHoursMonth' => $requiredHoursMonth,
            'counts' => $counts,
            'monthProgress' => min(($totalHoursMonth / $requiredHoursMonth) * 100, 100),
            'remainingHours' => max(0, $requiredHoursMonth - $totalHoursMonth),
            'todayStatus' => $todayRow['status'] ?? 'absent',
        ];
    }

    /**
     * Calculate human-readable "time ago" string
     */
    private function timeAgo(string $datetime): string
    {
        $now  = new \DateTime();
        $then = new \DateTime($datetime);
        $diff = $now->getTimestamp() - $then->getTimestamp();

        if ($diff < 60) {
            return 'Just now';
        }
        if ($diff < 3600) {
            $mins = floor($diff / 60);
            return $mins . ' min' . ($mins > 1 ? 's' : '') . ' ago';
        }
        if ($diff < 86400) {
            $hours = floor($diff / 3600);
            return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
        }

        $days = floor($diff / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    }
}
