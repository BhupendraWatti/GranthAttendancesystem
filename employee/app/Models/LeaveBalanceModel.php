<?php

namespace App\Models;

use CodeIgniter\Model;

class LeaveBalanceModel extends Model
{
    protected $table            = 'leave_balance';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';

    protected $allowedFields = [
        'emp_code',
        'leave_type',
        'total',
        'used',
        'remaining',
    ];

    /**
     * Get balances for an employee
     */
    public function getByEmployee(string $empCode): array
    {
        return $this->where('emp_code', $empCode)->findAll();
    }

    /**
     * Get specific balance for an employee
     */
    public function getBalance(string $empCode, string $leaveType): ?array
    {
        return $this->where('emp_code', $empCode)->where('leave_type', $leaveType)->first();
    }
}
