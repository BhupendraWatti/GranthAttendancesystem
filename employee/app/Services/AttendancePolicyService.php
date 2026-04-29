<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;

class AttendancePolicyService
{
    private BaseConnection $db;
    private float $fullTimePresentHours;
    private float $fullTimeHalfDayHours;
    private float $internPresentHours;
    private float $internHalfDayHours;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? \Config\Database::connect();
        $this->fullTimePresentHours = ((int) env('FULL_TIME_PRESENT_MINUTES', 420)) / 60;
        $this->fullTimeHalfDayHours = ((int) env('FULL_TIME_HALF_DAY_MINUTES', 264)) / 60;
        $this->internPresentHours = ((int) env('INTERN_PRESENT_MINUTES', 330)) / 60;
        $this->internHalfDayHours = ((int) env('INTERN_HALF_DAY_MINUTES', 150)) / 60;
    }

    public function generateForDate(string $date): array
    {
        $rows = $this->db->query(
            "SELECT
                p.emp_code,
                DATE(p.punch_time) AS summary_date,
                MIN(p.punch_time) AS first_in,
                MAX(p.punch_time) AS last_out,
                COUNT(*) AS punch_count,
                e.employee_type
            FROM punch_logs p
            LEFT JOIN employees e ON e.emp_code = p.emp_code
            WHERE DATE(p.punch_time) = ?
            GROUP BY p.emp_code, DATE(p.punch_time), e.employee_type",
            [$date]
        )->getResultArray();

        $upserted = 0;
        foreach ($rows as $row) {
            $firstIn = (string) $row['first_in'];
            $lastOut = (string) $row['last_out'];
            $employeeType = $row['employee_type'] === 'intern' ? 'intern' : 'full_time';
            $totalHours = max(0.0, (strtotime($lastOut) - strtotime($firstIn)) / 3600);
            $status = $this->classify($employeeType, $totalHours);

            $this->db->query(
                "INSERT INTO attendance_summary (emp_code, date, first_in, last_out, total_hours, status, updated_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE
                    first_in = VALUES(first_in),
                    last_out = VALUES(last_out),
                    total_hours = VALUES(total_hours),
                    status = VALUES(status),
                    updated_at = VALUES(updated_at)",
                [
                    $row['emp_code'],
                    $row['summary_date'],
                    $firstIn,
                    $lastOut,
                    round($totalHours, 2),
                    $status,
                    date('Y-m-d H:i:s'),
                ]
            );
            $upserted++;
            log_message('info', "[AttendancePolicyService] Upserted summary for {$row['emp_code']} {$row['summary_date']} => {$status}");
        }

        return ['date' => $date, 'upserted' => $upserted];
    }

    private function classify(string $employeeType, float $hours): string
    {
        if ($employeeType === 'intern') {
            if ($hours >= $this->internPresentHours) {
                return 'present';
            }
            if ($hours >= $this->internHalfDayHours) {
                return 'half_day';
            }
            return 'absent';
        }

        if ($hours >= $this->fullTimePresentHours) {
            return 'present';
        }
        if ($hours >= $this->fullTimeHalfDayHours) {
            return 'half_day';
        }
        return 'absent';
    }
}
