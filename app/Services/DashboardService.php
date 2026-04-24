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

        // Calculate absent (total active - present - half_day)
        $presentCount = $statusCounts['present'] ?? 0;
        $halfDayCount = $statusCounts['half_day'] ?? 0;
        $absentCount  = $statusCounts['absent'] ?? 0;

        // If attendance hasn't been processed yet, everyone is absent
        $processedCount = $presentCount + $halfDayCount + $absentCount;
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
            'late_today'       => $lateCount,
            'attendance_rate'  => $totalEmployees > 0
                ? round(($presentCount + $halfDayCount * 0.5) / $totalEmployees * 100, 1)
                : 0,
        ];
    }

    /**
     * Get live punch feed (recent punches)
     *
     * @param int $limit Number of records to return
     * @return array Recent punch entries
     */
    public function getLivePunches(int $limit = 20): array
    {
        $punches = $this->punchLogModel->getLatestPunches($limit);

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
