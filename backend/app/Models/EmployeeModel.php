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
        'department',
        'designation',
        'employee_type',
        'salary',
        'status',
    ];

    protected $validationRules = [
        'emp_code' => 'required|max_length[50]',
        'name'     => 'required|max_length[255]',
    ];

    /**
     * Get all active employees sorted alphabetically
     */
    public function getActive(): array
    {
        return $this->where('status', 'active')->orderBy('name', 'ASC')->findAll();
    }

    /**
     * Find employee by emp_code
     */
    public function findByCode(string $empCode): ?array
    {
        return $this->where('emp_code', $empCode)->first();
    }

    /**
     * Upsert employee — insert or update by emp_code
     */
    public function upsertByCode(array $data): bool
    {
        $db = \Config\Database::connect();
        $now = date('Y-m-d H:i:s');

        // Atomic upsert — no race condition between SELECT and INSERT
        $sql = "INSERT INTO {$this->table} (emp_code, name, department, designation, employee_type, status, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    name = COALESCE(VALUES(name), name),
                    department = VALUES(department),
                    designation = VALUES(designation),
                    employee_type = VALUES(employee_type),
                    status = VALUES(status),
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
