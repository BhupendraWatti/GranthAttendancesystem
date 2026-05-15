<?php

namespace App\Services;

use App\Models\AttendanceDailyModel;
use App\Models\EmployeeModel;

/**
 * SalaryService — Core Salary Calculation Engine (Dynamic Hourly Model)
 *
 * This service calculates salary based on:
 * 1. Target Hours to Date (excluding Sundays and 1st/3rd Saturdays)
 * 2. Deductive model for mid-month fairness
 */
class SalaryService
{
    private AttendanceDailyModel $attendanceModel;
    private EmployeeModel $employeeModel;

    private int $salaryBaseDays;
    private float $workDayHours;

    public function __construct()
    {
        $this->attendanceModel = new AttendanceDailyModel();
        $this->employeeModel  = new EmployeeModel();

        $this->salaryBaseDays = (int) env('SALARY_BASE_DAYS', 30);
        $this->workDayHours   = 8.5; 
    }

    /**
     * Calculate salary for a SINGLE employee
     */
    public function calculateEmployeeSalary(string $empCode, int $year, int $month, ?float $monthlySalary = null): ?array
    {
        $employee = $this->employeeModel->findByCode($empCode);
        if (!$employee) return null;

        $salary = $employee['salary'] ?? $monthlySalary ?? (float) env('DEFAULT_MONTHLY_SALARY', 25000);
        $records = $this->attendanceModel->getMonthly($empCode, $year, $month);

        $data = [
            'emp_code'           => $empCode,
            'name'               => $employee['name'],
            'department'         => $employee['department'] ?? null,
            'employee_type'      => $employee['employee_type'] ?? 'full_time',
            'base_salary'        => $employee['salary'] ?? null,
            'present_days'       => 0,
            'half_days'          => 0,
            'absent_days'        => 0,
            'wfh_days'           => 0,
            'paid_leave_days'    => 0,
            'unpaid_leave_days'  => 0,
            'holiday_days'       => 0,
            'comp_off_days'      => 0,
            'total_work_minutes' => 0,
        ];

        foreach ($records as $record) {
            switch ($record['status']) {
                case 'present':  $data['present_days']++; break;
                case 'half_day': $data['half_days']++; break;
                case 'absent':   $data['absent_days']++; break;
                case 'work_from_home': $data['wfh_days']++; break;
                case 'paid_leave': $data['paid_leave_days']++; break;
                case 'unpaid_leave': $data['unpaid_leave_days']++; break;
                case 'leave':    $data['paid_leave_days']++; break;
                case 'holiday':  $data['holiday_days']++; break;
                case 'comp_off': $data['comp_off_days']++; break;
            }
            $fullCreditStatuses = ['work_from_home', 'paid_leave', 'holiday', 'comp_off', 'leave'];
            if (in_array($record['status'], $fullCreditStatuses)) {
                $data['total_work_minutes'] += 510;
            } else {
                $data['total_work_minutes'] += (int) ($record['work_minutes'] ?? 0);
            }
        }

        return $this->computeSalary($data, (float)$salary, $year, $month);
    }

    /**
     * Dynamic Hourly Model Computation
     */
    private function computeSalary(array $data, float $monthlySalary, int $year, int $month): array
    {
        $today = date('Y-m-d');
        $isCurrentMonth = ($year == date('Y') && $month == date('m'));
        $endDay = $isCurrentMonth ? (int)date('d') : (int)date('t', strtotime("$year-$month-01"));

        // 1. Calculate Dynamic Target-to-Date
        $targetToDateMin = 0;
        $satCount = 0;
        for ($d = 1; $d <= $endDay; $d++) {
            $time = mktime(0, 0, 0, $month, $d, $year);
            $dow = (int)date('w', $time); // 0=Sun, 6=Sat
            
            if ($dow === 0) continue; // Sunday OFF
            
            if ($dow === 6) { // Saturday
                $satCount++;
                if ($satCount === 1 || $satCount === 3) continue; // 1st/3rd Sat OFF
            }
            
            $targetToDateMin += 510; // Working day (8.5h)
        }

        $targetToDateHours = $targetToDateMin / 60;
        $actualWorkMin = (int) ($data['total_work_minutes'] ?? 0);
        $actualWorkHours = round($actualWorkMin / 60, 2);

        // 2. Shortfall & Deduction Logic
        $shortfallHours = max(0, $targetToDateHours - $actualWorkHours);
        $deductionDays = 0;
        if ($shortfallHours > 4.5) {
            $halfDayUnit = 4.25;
            $units = round($shortfallHours / $halfDayUnit);
            $deductionDays = $units * 0.5;
        }

        // 3. Earnings based on Days Elapsed (e.g. 10 days)
        $dailyRate = $monthlySalary / 30;
        $baseEarned = $dailyRate * $endDay; 
        $totalDeduction = round($deductionDays * $dailyRate, 2);
        $netPayable = round($baseEarned - $totalDeduction, 2);

        // Effective days for display (e.g. 10 - deduction)
        $effectiveDays = max(0, $endDay - $deductionDays);

        return array_merge($data, [
            'expected_minutes'  => $targetToDateMin,
            'work_hours'        => $actualWorkHours,
            'expected_hours'    => $targetToDateHours,
            'shortfall_hours'   => $shortfallHours,
            'monthly_salary'    => $monthlySalary,
            'calculated_salary' => $netPayable,
            'deduction'         => $totalDeduction,
            'net_salary'        => $netPayable,
            'effective_days'    => $effectiveDays,
            'days_elapsed'      => $endDay,
            'working_days'      => round($targetToDateHours / 8.5, 1),
            'ratio'             => $targetToDateMin > 0 ? round(min(1, $actualWorkMin / $targetToDateMin) * 100, 1) : 0,
        ]);
    }
}
