<?php

namespace App\Models;

use CodeIgniter\Model;

class AttendanceSummaryModel extends Model
{
    protected $table            = 'attendance_summary';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $useTimestamps    = false;

    protected $allowedFields = [
        'emp_code',
        'date',
        'first_in',
        'last_out',
        'total_hours',
        'status',
        'updated_at',
    ];

    public function getForDate(string $empCode, string $date): ?array
    {
        return $this->where('emp_code', $empCode)->where('date', $date)->first();
    }

    public function getMonthly(string $empCode, int $year, int $month): array
    {
        $start = sprintf('%04d-%02d-01', $year, $month);
        $end   = date('Y-m-t', strtotime($start));

        return $this->where('emp_code', $empCode)
            ->where('date >=', $start)
            ->where('date <=', $end)
            ->orderBy('date', 'ASC')
            ->findAll();
    }
}

