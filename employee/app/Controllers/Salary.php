<?php

namespace App\Controllers;

use App\Models\EmployeeModel;
use App\Services\SalaryService;

class Salary extends BaseController
{
    public function index()
    {
        $empCode = (string) session()->get('empcode');
        $year = (int) ($this->request->getGet('year') ?: date('Y'));
        $month = (int) ($this->request->getGet('month') ?: date('m'));

        $salary = (new SalaryService())->calculateEmployeeSalary($empCode, $year, $month);

        // Fetch and apply admin deduction & bonus (table may not exist yet if admin hasn't opened salary page)
        $adminDeduction = 0.0;
        $bonusAmount = 0.0;
        try {
            $db = \Config\Database::connect();
            $dRecord = $db->table('monthly_deductions')
                ->where('emp_code', $empCode)
                ->where('year', $year)
                ->where('month', $month)
                ->get()
                ->getRowArray();
            $adminDeduction = $dRecord ? (float) $dRecord['deduction_amount'] : 0.0;
            $bonusAmount = $dRecord ? (float) ($dRecord['bonus_amount'] ?? 0.0) : 0.0;
        } catch (\Throwable $e) {
            log_message('warning', '[Salary] monthly_deductions not available: ' . $e->getMessage());
        }
        $salary['admin_deduction'] = $adminDeduction;
        $salary['bonus_amount'] = $bonusAmount;
        $salary['net_salary'] = round($salary['net_salary'] - $adminDeduction + $bonusAmount, 2);

        return view('salary', [
            'salary' => $salary,
            'year' => $year,
            'month' => $month,
        ]);
    }

    public function payslip()
    {
        $empCode = (string) session()->get('empcode');
        $month = (int) ($this->request->getGet('month') ?: date('m'));
        $year = (int) ($this->request->getGet('year') ?: date('Y'));

        $employee = (new EmployeeModel())->findByCode($empCode);
        if (!$employee) {
            return redirect()->to(base_url('salary'))->with('error', 'Employee not found.');
        }

        $salary = (new SalaryService())->calculateEmployeeSalary($empCode, $year, $month);

        // Fetch and apply admin deduction & bonus
        $adminDeduction = 0.0;
        $bonusAmount = 0.0;
        try {
            $db = \Config\Database::connect();
            $dRecord = $db->table('monthly_deductions')
                ->where('emp_code', $empCode)
                ->where('year', $year)
                ->where('month', $month)
                ->get()
                ->getRowArray();
            $adminDeduction = $dRecord ? (float) $dRecord['deduction_amount'] : 0.0;
            $bonusAmount = $dRecord ? (float) ($dRecord['bonus_amount'] ?? 0.0) : 0.0;
        } catch (\Throwable $e) {
            log_message('warning', '[Salary] monthly_deductions not available: ' . $e->getMessage());
        }
        $salary['admin_deduction'] = $adminDeduction;
        $salary['bonus_amount'] = $bonusAmount;
        $salary['net_salary'] = round($salary['net_salary'] - $adminDeduction + $bonusAmount, 2);

        return view('payslip', [
            'employee' => $employee,
            'salary'   => $salary,
            'month'    => $month,
            'year'     => $year,
        ]);
    }

    public function payslipPrint()
    {
        $empCode = (string) session()->get('empcode');
        $month = (int) ($this->request->getGet('month') ?: date('m'));
        $year = (int) ($this->request->getGet('year') ?: date('Y'));

        $employee = (new EmployeeModel())->findByCode($empCode);
        if (!$employee) {
            return redirect()->to(base_url('salary'));
        }

        $salary = (new SalaryService())->calculateEmployeeSalary($empCode, $year, $month);

        // Fetch and apply admin deduction & bonus
        $adminDeduction = 0.0;
        $bonusAmount = 0.0;
        try {
            $db = \Config\Database::connect();
            $dRecord = $db->table('monthly_deductions')
                ->where('emp_code', $empCode)
                ->where('year', $year)
                ->where('month', $month)
                ->get()
                ->getRowArray();
            $adminDeduction = $dRecord ? (float) $dRecord['deduction_amount'] : 0.0;
            $bonusAmount = $dRecord ? (float) ($dRecord['bonus_amount'] ?? 0.0) : 0.0;
        } catch (\Throwable $e) {
            log_message('warning', '[Salary] monthly_deductions not available: ' . $e->getMessage());
        }
        $salary['admin_deduction'] = $adminDeduction;
        $salary['bonus_amount'] = $bonusAmount;
        $salary['net_salary'] = round($salary['net_salary'] - $adminDeduction + $bonusAmount, 2);

        return view('payslip_print', [
            'employee' => $employee,
            'salary'   => $salary,
            'month'    => $month,
            'year'     => $year,
        ]);
    }
}

