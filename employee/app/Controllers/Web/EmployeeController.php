<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;
use App\Models\EmployeeModel;
use App\Models\AttendanceDailyModel;
use App\Services\SalaryService;

/**
 * Web EmployeeController — Server-rendered Employee pages
 *
 * GET /employees           — List all employees
 * GET /employees/:empCode  — Show employee detail + attendance + salary
 */
class EmployeeController extends BaseController
{
    /**
     * GET /employees — List all employees with optional filters
     */
    public function index()
    {
        $status = $this->request->getGet('status');
        $type = $this->request->getGet('type');
        $employees = [];

        try {
            $model = new EmployeeModel();
            $builder = $model;

            if (!empty($status)) {
                $builder = $builder->where('status', $status);
            }
            if (!empty($type)) {
                $builder = $builder->where('employee_type', $type);
            }

            $employees = $builder->orderBy('name', 'ASC')->findAll();
        } catch (\Throwable $e) {
            log_message('error', '[Web\\EmployeeController] Error: ' . $e->getMessage());
            if (strpos($e->getMessage(), 'Unable to connect') !== false || strpos($e->getMessage(), 'Connection refused') !== false) {
                session()->setFlashdata('error', 'Database connection failed. Please ensure MySQL is running in XAMPP Control Panel.');
            } else {
                session()->setFlashdata('error', 'Failed to load employees: ' . $e->getMessage());
            }
        }

        return view('pages/employees', [
            'pageTitle' => 'Employees',
            'activePage' => 'employees',
            'employees' => $employees,
            'total' => count($employees),
            'filters' => [
                'status' => $status ?? '',
                'type' => $type ?? '',
            ],
        ]);
    }

    /**
     * GET /employees/:empCode — Employee detail with attendance + salary
     */
    public function show($empCode = null)
    {
        if (empty($empCode)) {
            return redirect()->to('/employees')->with('error', 'Employee code is required.');
        }

        try {
            $employeeModel = new EmployeeModel();
            $attendanceModel = new AttendanceDailyModel();
            $salaryService = new SalaryService();

            $employee = $employeeModel->findByCode($empCode);

            if (!$employee) {
                return redirect()->to('/employees')->with('error', "Employee {$empCode} not found.");
            }

            // Get month/year from query params (default = current month)
            $month = (int) ($this->request->getGet('month') ?? date('n'));
            $year = (int) ($this->request->getGet('year') ?? date('Y'));

            // Fetch attendance records for selected month
            $attendanceRecords = $attendanceModel->getMonthly($empCode, $year, $month);

            // Calculate salary for this employee
            $salarySummary = $salaryService->calculateEmployeeSalary($empCode, $year, $month);

            return view('pages/employee_detail', [
                'pageTitle' => $employee['name'],
                'activePage' => 'employees',
                'employee' => $employee,
                'attendanceRecords' => $attendanceRecords,
                'salarySummary' => $salarySummary,
                'month' => $month,
                'year' => $year,
            ]);
        } catch (\Throwable $e) {
            log_message('error', '[Web\\EmployeeController] show error: ' . $e->getMessage());
            return redirect()->to('/employees')->with('error', 'Failed to load employee details. Please ensure the database is connected.');
        }
    }

    /**
     * POST /employees/salary — Update employee base salary
     */
    public function updateSalary()
    {
        $empCode = $this->request->getPost('emp_code');
        $salary = $this->request->getPost('salary');

        if (empty($empCode) || !is_numeric($salary) || $salary <= 0) {
            return redirect()->back()->with('error', 'Invalid salary value provided.');
        }

        try {
            $employeeModel = new EmployeeModel();
            $employee = $employeeModel->findByCode($empCode);

            if (!$employee) {
                return redirect()->back()->with('error', 'Employee not found.');
            }

            $employeeModel->update($employee['id'], ['salary' => $salary]);

            return redirect()->back()->with('success', 'Salary updated successfully.');
        } catch (\Throwable $e) {
            log_message('error', '[Web\\EmployeeController] updateSalary error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update salary.');
        }
    }
}
