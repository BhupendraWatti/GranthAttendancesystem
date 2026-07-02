<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;
use App\Services\SalaryService;
use App\Models\EmployeeModel;

/**
 * Web SalaryController — Salary Dashboard + Payslip Generation
 *
 * GET /salary              — Monthly salary dashboard for all employees
 * GET /payslip/{emp_code}  — Individual payslip view
 * GET /payslip/{emp_code}/print — Printable payslip
 */
class SalaryController extends BaseController
{
    private $db;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        
        $this->db = \Config\Database::connect();
        $this->db->query("CREATE TABLE IF NOT EXISTS monthly_deductions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            emp_code VARCHAR(50) NOT NULL,
            year INT NOT NULL,
            month INT NOT NULL,
            deduction_amount DECIMAL(15,2) DEFAULT 0.00,
            created_at DATETIME,
            updated_at DATETIME,
            UNIQUE KEY unique_emp_year_month (emp_code, year, month)
        )");
    }

    /**
     * GET /salary — Salary Management Dashboard (Stitch design)
     */
    public function index()
    {
        $month = (int) ($this->request->getGet('month') ?? date('n'));
        $year  = (int) ($this->request->getGet('year') ?? date('Y'));
        $salaryData = [];
        $totals = [];

        try {
            $salaryService = new SalaryService();
            $salaryData = $salaryService->calculateAllSalaries($year, $month);

            // Fetch and apply monthly deductions
            $deductions = $this->db->table('monthly_deductions')
                ->where('year', $year)
                ->where('month', $month)
                ->get()
                ->getResultArray();
            
            $deductionsMap = [];
            foreach ($deductions as $d) {
                $deductionsMap[$d['emp_code']] = (float) $d['deduction_amount'];
            }

            foreach ($salaryData as &$row) {
                $empCode = $row['emp_code'];
                $adminDeduction = $deductionsMap[$empCode] ?? 0.0;
                $row['admin_deduction'] = $adminDeduction;
                $row['net_salary'] = round($row['net_salary'] - $adminDeduction, 2);
            }
            unset($row); // prevent dangling reference

            $totals = $salaryService->getMonthlyTotals($salaryData);
            
            // Recalculate total deductions including admin deduction
            $totalAdminDeductions = array_sum(array_column($salaryData, 'admin_deduction'));
            $totals['total_deduction'] = round($totals['total_deduction'] + $totalAdminDeductions, 2);

        } catch (\Throwable $e) {
            log_message('error', '[Web\\SalaryController] Error: ' . $e->getMessage());
            session()->setFlashdata('error', 'Failed to load salary data: ' . $e->getMessage());
        }

        return view('pages/salary', [
            'pageTitle'  => 'Salary Management',
            'activePage' => 'salary',
            'salaryData' => $salaryData,
            'totals'     => $totals,
            'month'      => $month,
            'year'       => $year,
        ]);
    }

    /**
     * GET /payslip/{emp_code} — Individual payslip view
     */
    public function payslip($empCode = null)
    {
        if (empty($empCode)) {
            return redirect()->to(base_url('salary'))->with('error', 'Employee code is required.');
        }

        $month = (int) ($this->request->getGet('month') ?? date('n'));
        $year  = (int) ($this->request->getGet('year') ?? date('Y'));

        try {
            $salaryService = new SalaryService();
            $employeeModel = new EmployeeModel();

            $employee = $employeeModel->findByCode($empCode);
            if (!$employee) {
                return redirect()->to(base_url('salary'))->with('error', "Employee {$empCode} not found.");
            }

            $salaryData = $salaryService->calculateEmployeeSalary($empCode, $year, $month);

            // Fetch and apply admin deduction
            $dRecord = $this->db->table('monthly_deductions')
                ->where('emp_code', $empCode)
                ->where('year', $year)
                ->where('month', $month)
                ->get()
                ->getRowArray();
            $adminDeduction = $dRecord ? (float) $dRecord['deduction_amount'] : 0.0;

            $salaryData['admin_deduction'] = $adminDeduction;
            $salaryData['net_salary'] = round($salaryData['net_salary'] - $adminDeduction, 2);

            return view('pages/payslip_professional', [
                'pageTitle'  => "Payslip — {$employee['name']}",
                'activePage' => 'salary',
                'employee'   => $employee,
                'salary'     => $salaryData,
                'month'      => $month,
                'year'       => $year,
            ]);
        } catch (\Throwable $e) {
            log_message('error', '[Web\\SalaryController] payslip error: ' . $e->getMessage());
            return redirect()->to(base_url('salary'))->with('error', 'Failed to generate payslip.');
        }
    }

    /**
     * GET /payslip/{emp_code}/print — Standalone printable payslip (no sidebar)
     */
    public function payslipPrint($empCode = null)
    {
        if (empty($empCode)) {
            return redirect()->to(base_url('salary'));
        }

        $month = (int) ($this->request->getGet('month') ?? date('n'));
        $year  = (int) ($this->request->getGet('year') ?? date('Y'));

        try {
            $salaryService = new SalaryService();
            $employeeModel = new EmployeeModel();

            $employee = $employeeModel->findByCode($empCode);
            if (!$employee) {
                return redirect()->to(base_url('salary'));
            }

            $salaryData = $salaryService->calculateEmployeeSalary($empCode, $year, $month);

            // Fetch and apply admin deduction
            $dRecord = $this->db->table('monthly_deductions')
                ->where('emp_code', $empCode)
                ->where('year', $year)
                ->where('month', $month)
                ->get()
                ->getRowArray();
            $adminDeduction = $dRecord ? (float) $dRecord['deduction_amount'] : 0.0;

            $salaryData['admin_deduction'] = $adminDeduction;
            $salaryData['net_salary'] = round($salaryData['net_salary'] - $adminDeduction, 2);

            return view('pages/payslip_print', [
                'employee' => $employee,
                'salary'   => $salaryData,
                'month'    => $month,
                'year'     => $year,
            ]);
        } catch (\Throwable $e) {
            log_message('error', '[Web\\SalaryController] payslipPrint error: ' . $e->getMessage());
            return redirect()->to(base_url('salary'));
        }
    }

    /**
     * POST /salary/save-deduction — Save monthly deduction
     */
    public function saveDeduction()
    {
        $empCode = $this->request->getPost('emp_code');
        $month   = (int) $this->request->getPost('month');
        $year    = (int) $this->request->getPost('year');
        $amount  = (float) $this->request->getPost('deduction_amount');

        if (empty($empCode) || empty($month) || empty($year)) {
            return redirect()->back()->with('error', 'Invalid parameters.');
        }

        try {
            $now = date('Y-m-d H:i:s');

            $existing = $this->db->table('monthly_deductions')
                ->where('emp_code', $empCode)
                ->where('year', $year)
                ->where('month', $month)
                ->get()
                ->getRowArray();

            if ($existing) {
                $this->db->table('monthly_deductions')
                    ->where('id', $existing['id'])
                    ->update([
                        'deduction_amount' => $amount,
                        'updated_at'       => $now
                    ]);
            } else {
                $this->db->table('monthly_deductions')
                    ->insert([
                        'emp_code'         => $empCode,
                        'year'             => $year,
                        'month'            => $month,
                        'deduction_amount' => $amount,
                        'created_at'       => $now,
                        'updated_at'       => $now
                    ]);
            }

            return redirect()->to(base_url("salary?month={$month}&year={$year}"))->with('success', 'Deduction updated successfully.');
        } catch (\Throwable $e) {
            log_message('error', '[Web\\SalaryController] saveDeduction error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to save deduction.');
        }
    }
}
