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

        return view('payslip_print', [
            'employee' => $employee,
            'salary'   => $salary,
            'month'    => $month,
            'year'     => $year,
        ]);
    }
}

