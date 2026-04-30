<?php

namespace App\Models;

use CodeIgniter\Model;

class LeaveRequestModel extends Model
{
    protected $table            = 'leave_requests';
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
        'from_date',
        'to_date',
        'from_session',
        'to_session',
        'reason',
        'status',
        'admin_comment',
    ];

    /**
     * Get pending leave requests
     */
    public function getPending(): array
    {
        return $this->where('status', 'pending')->findAll();
    }

    /**
     * Get leave requests by employee code
     */
    public function getByEmployee(string $empCode): array
    {
        return $this->where('emp_code', $empCode)->orderBy('created_at', 'DESC')->findAll();
    }

    /**
     * Check for overlapping leave requests
     */
    public function hasOverlap(string $empCode, string $fromDate, string $toDate): bool
    {
        return $this->where('emp_code', $empCode)
            ->where('status !=', 'rejected')
            ->groupStart()
                ->where("from_date BETWEEN '{$fromDate}' AND '{$toDate}'")
                ->orWhere("to_date BETWEEN '{$fromDate}' AND '{$toDate}'")
                ->orWhere("'{$fromDate}' BETWEEN from_date AND to_date")
            ->groupEnd()
            ->countAllResults() > 0;
    }
}
