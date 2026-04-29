<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\EmployeeModel;

/**
 * EmployeeController — Employee management endpoints
 * 
 * GET    /api/employees           — List all employees
 * GET    /api/employees/:empCode  — Get single employee  
 * POST   /api/employees           — Create employee
 * PUT    /api/employees/:empCode  — Update employee
 */
class EmployeeController extends ResourceController
{
    protected $format = 'json';

    /**
     * GET /api/employees
     * 
     * Query params: ?status=active&type=full_time
     */
    public function index()
    {
        try {
            $model = new EmployeeModel();
            $status = $this->request->getGet('status');
            $type   = $this->request->getGet('type');

            $builder = $model;

            if (!empty($status)) {
                $builder = $builder->where('status', $status);
            }
            if (!empty($type)) {
                $builder = $builder->where('employee_type', $type);
            }

            $employees = $builder->orderBy('name', 'ASC')->findAll();

            return $this->respond([
                'status' => 'success',
                'data'   => $employees,
                'total'  => count($employees),
            ]);
        } catch (\Throwable $e) {
            log_message('error', '[EmployeeController] Index error: ' . $e->getMessage());
            return $this->failServerError('Failed to fetch employees');
        }
    }

    /**
     * GET /api/employees/:empCode
     */
    public function show($empCode = null)
    {
        if (empty($empCode)) {
            return $this->failValidationErrors('Employee code is required');
        }

        try {
            $model = new EmployeeModel();
            $employee = $model->findByCode($empCode);

            if (!$employee) {
                return $this->failNotFound("Employee {$empCode} not found");
            }

            return $this->respond([
                'status' => 'success',
                'data'   => $employee,
            ]);
        } catch (\Throwable $e) {
            log_message('error', '[EmployeeController] Show error: ' . $e->getMessage());
            return $this->failServerError('Failed to fetch employee');
        }
    }

    /**
     * POST /api/employees
     * 
     * Body: { emp_code, name, department, designation, employee_type, status }
     */
    public function create()
    {
        $input = $this->request->getJSON(true);

        if (empty($input['emp_code']) || empty($input['name'])) {
            return $this->failValidationErrors('emp_code and name are required');
        }

        try {
            $model = new EmployeeModel();

            // Check if employee already exists
            $existing = $model->findByCode($input['emp_code']);
            if ($existing) {
                return $this->fail("Employee {$input['emp_code']} already exists", 409);
            }

            $model->insert([
                'emp_code'      => $input['emp_code'],
                'name'          => $input['name'],
                'department'    => $input['department'] ?? null,
                'designation'   => $input['designation'] ?? null,
                'employee_type' => $input['employee_type'] ?? 'full_time',
                'status'        => $input['status'] ?? 'active',
            ]);

            return $this->respondCreated([
                'status'  => 'success',
                'message' => 'Employee created successfully',
            ]);
        } catch (\Throwable $e) {
            log_message('error', '[EmployeeController] Create error: ' . $e->getMessage());
            return $this->failServerError('Failed to create employee');
        }
    }

    /**
     * PUT /api/employees/:empCode
     */
    public function update($empCode = null)
    {
        if (empty($empCode)) {
            return $this->failValidationErrors('Employee code is required');
        }

        $input = $this->request->getJSON(true);

        try {
            $model = new EmployeeModel();
            $employee = $model->findByCode($empCode);

            if (!$employee) {
                return $this->failNotFound("Employee {$empCode} not found");
            }

            $model->update($employee['id'], array_filter([
                'name'          => $input['name'] ?? null,
                'department'    => $input['department'] ?? null,
                'designation'   => $input['designation'] ?? null,
                'employee_type' => $input['employee_type'] ?? null,
                'status'        => $input['status'] ?? null,
            ]));

            return $this->respond([
                'status'  => 'success',
                'message' => 'Employee updated successfully',
            ]);
        } catch (\Throwable $e) {
            log_message('error', '[EmployeeController] Update error: ' . $e->getMessage());
            return $this->failServerError('Failed to update employee');
        }
    }
}
