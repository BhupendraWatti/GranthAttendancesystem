<?php

namespace App\Models;

use CodeIgniter\Model;

class AttendanceOverrideModel extends Model
{
    protected $table            = 'attendance_overrides';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'emp_code',
        'attendance_date',
        'override_type',
        'approved_by',
        'remarks'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Get override for a specific employee and date
     */
    public function getOverride(string $empCode, string $date)
    {
        return $this->where('emp_code', $empCode)
                    ->where('attendance_date', $date)
                    ->first();
    }
}
