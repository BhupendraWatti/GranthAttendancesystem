<?php

namespace App\Models;

use CodeIgniter\Model;

class AttendanceDailyModel extends Model
{
    protected $table            = 'attendance_daily';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';

    protected $allowedFields = [
        'emp_code',
        'date',
        'first_in',
        'last_out',
        'work_minutes',
        'late_minutes',
        'status',
        'punch_count',
        'employee_type',
        'required_minutes',
        'validation_errors',
    ];

    /**
     * Upsert attendance — insert or update by (emp_code, date)
     */
    public function upsertAttendance(array $data): bool
    {
        if (is_array($data['validation_errors'] ?? null)) {
            $data['validation_errors'] = json_encode($data['validation_errors']);
        }

        $now = date('Y-m-d H:i:s');
        $db = \Config\Database::connect();

        // Single atomic query — no race condition between SELECT and INSERT
        $sql = "INSERT INTO {$this->table} 
                    (emp_code, date, first_in, last_out, work_minutes, late_minutes, status, punch_count, employee_type, required_minutes, validation_errors, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    first_in = VALUES(first_in),
                    last_out = VALUES(last_out),
                    work_minutes = VALUES(work_minutes),
                    late_minutes = VALUES(late_minutes),
                    status = VALUES(status),
                    punch_count = VALUES(punch_count),
                    employee_type = VALUES(employee_type),
                    required_minutes = VALUES(required_minutes),
                    validation_errors = VALUES(validation_errors),
                    updated_at = VALUES(updated_at)";

        return $db->query($sql, [
            $data['emp_code'],
            $data['date'],
            $data['first_in'] ?? null,
            $data['last_out'] ?? null,
            $data['work_minutes'] ?? 0,
            $data['late_minutes'] ?? 0,
            $data['status'],
            $data['punch_count'] ?? 0,
            $data['employee_type'] ?? 'full_time',
            $data['required_minutes'] ?? 510,
            $data['validation_errors'] ?? null,
            $now,
            $now,
        ]);
    }

    /**
     * Get daily attendance for a specific date
     */
    public function getForDate(string $date): array
    {
        return $this->select('attendance_daily.*, employees.name, employees.department, employees.designation')
                    ->join('employees', 'employees.emp_code = attendance_daily.emp_code', 'left')
                    ->where('attendance_daily.date', $date)
                    ->orderBy('employees.name', 'ASC')
                    ->findAll();
    }

    /**
     * Get monthly attendance for an employee
     */
    public function getMonthly(string $empCode, int $year, int $month): array
    {
        return $this->where('emp_code', $empCode)
                    ->where('YEAR(date)', $year)
                    ->where('MONTH(date)', $month)
                    ->orderBy('date', 'ASC')
                    ->findAll();
    }

    /**
     * Get monthly attendance for all employees
     */
    public function getAllMonthly(int $year, int $month): array
    {
        return $this->select('attendance_daily.*, employees.name, employees.department, employees.salary')
                    ->join('employees', 'employees.emp_code = attendance_daily.emp_code', 'left')
                    ->where('YEAR(attendance_daily.date)', $year)
                    ->where('MONTH(attendance_daily.date)', $month)
                    ->orderBy('employees.name', 'ASC')
                    ->orderBy('attendance_daily.date', 'ASC')
                    ->findAll();
    }

    /**
     * Count by status for a specific date
     */
    public function countByStatus(string $date): array
    {
        $results = $this->select('status, COUNT(*) as count')
                        ->where('date', $date)
                        ->groupBy('status')
                        ->findAll();

        $counts = ['present' => 0, 'half_day' => 0, 'absent' => 0];
        foreach ($results as $row) {
            $counts[$row['status']] = (int) $row['count'];
        }

        return $counts;
    }

    /**
     * Count late employees for a specific date
     */
    public function countLate(string $date): int
    {
        return $this->where('date', $date)
                    ->where('late_minutes >', 0)
                    ->countAllResults();
    }
}
