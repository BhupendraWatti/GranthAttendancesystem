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
            $totals     = $salaryService->getMonthlyTotals($salaryData);
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

            return view('pages/payslip', [
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
}
