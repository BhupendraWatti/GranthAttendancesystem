<?php

namespace App\Models;

use CodeIgniter\Model;

class EmployeeSalaryComponentModel extends Model
{
    protected $table            = 'employee_salary_components';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['emp_code', 'component_name', 'amount', 'type', 'is_active'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getByEmployee(string $empCode)
    {
        return $this->where('emp_code', $empCode)
                    ->where('is_active', true)
                    ->findAll();
    }
}
