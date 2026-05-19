<?php

namespace App\Models;

use CodeIgniter\Model;

class EmployeeModel extends Model
{
    protected $table            = 'employees';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';

    protected $allowedFields = [
        'emp_code',
        'email',
        'name',
        'date_of_joining',
        'department',
        'department_id',
        'designation',
        'designation_id',
        'shift_id',
        'employee_type',
        'salary',
        'status',
        'employment_status',
        'is_profile_locked',
    ];

    protected $validationRules = [
        'emp_code' => 'required|max_length[50]',
        'name'     => 'required|max_length[255]',
    ];

    /**
     * Get all active employees sorted alphabetically with Master data
     */
    public function getActiveWithMaster(): array
    {
        return $this->select('employees.*, departments.name as dept_name, designations.name as desig_name, shifts.name as shift_name')
                    ->join('departments', 'departments.id = employees.department_id', 'left')
                    ->join('designations', 'designations.id = employees.designation_id', 'left')
                    ->join('shifts', 'shifts.id = employees.shift_id', 'left')
                    ->where('employees.status', 'active')
                    ->orderBy('employees.name', 'ASC')
                    ->findAll();
    }

    /**
     * Get all employees sorted alphabetically with Master data (ignores status)
     * Useful when query builder filters (like status) are dynamically applied.
     */
    public function getAllWithMaster(): array
    {
        return $this->select('employees.*, departments.name as dept_name, designations.name as desig_name, shifts.name as shift_name')
                    ->join('departments', 'departments.id = employees.department_id', 'left')
                    ->join('designations', 'designations.id = employees.designation_id', 'left')
                    ->join('shifts', 'shifts.id = employees.shift_id', 'left')
                    ->orderBy('employees.name', 'ASC')
                    ->findAll();
    }

    /**
     * Get all active employees (Alias for getActiveWithMaster for backward compatibility)
     */
    public function getActive(): array
    {
        return $this->getActiveWithMaster();
    }

    /**
     * Find employee by emp_code with Master data
     */
    public function findByCodeWithMaster(string $empCode): ?array
    {
        return $this->select('employees.*, departments.name as dept_name, designations.name as desig_name, shifts.name as shift_name, shifts.start_time, shifts.end_time, shifts.grace_minutes, shifts.expected_hours')
                    ->join('departments', 'departments.id = employees.department_id', 'left')
                    ->join('designations', 'designations.id = employees.designation_id', 'left')
                    ->join('shifts', 'shifts.id = employees.shift_id', 'left')
                    ->where('employees.emp_code', $empCode)
                    ->first();
    }

    /**
     * Find employee by emp_code
     */
    public function findByCode(string $empCode): ?array
    {
        return $this->where('employees.emp_code', $empCode)->first();
    }

    /**
     * Upsert employee — insert or update by emp_code
     * Respects is_profile_locked: if locked, only updates updated_at.
     */
    public function upsertByCode(array $data): bool
    {
        $db = \Config\Database::connect();
        $now = date('Y-m-d H:i:s');

        // Atomic upsert with conditional update based on is_profile_locked
        $sql = "INSERT INTO {$this->table} (emp_code, name, department, designation, employee_type, status, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    name = IF(is_profile_locked = 0, COALESCE(VALUES(name), name), name),
                    department = IF(is_profile_locked = 0, VALUES(department), department),
                    designation = IF(is_profile_locked = 0, VALUES(designation), designation),
                    employee_type = IF(is_profile_locked = 0, VALUES(employee_type), employee_type),
                    status = IF(is_profile_locked = 0, VALUES(status), status),
                    updated_at = VALUES(updated_at)";

        return $db->query($sql, [
            $data['emp_code'],
            $data['name'] ?? $data['emp_code'],
            $data['department'] ?? null,
            $data['designation'] ?? null,
            $data['employee_type'] ?? 'full_time',
            $data['status'] ?? 'active',
            $now,
            $now,
        ]);
    }

    /**
     * Count active employees
     */
    public function countActive(): int
    {
        return $this->where('status', 'active')->countAllResults();
    }
}
