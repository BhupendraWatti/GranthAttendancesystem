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
     * Calculate salary for ALL employees for a given month
     */
    public function calculateAllSalaries(int $year, int $month, ?float $defaultMonthlySalary = null): array
    {
        helper('attendance');
        $monthlySalary = $defaultMonthlySalary ?? (float) env('DEFAULT_MONTHLY_SALARY', 25000);
        $records = $this->attendanceModel->getAllMonthly($year, $month);

        if (empty($records)) {
            return [];
        }

        $byEmployee = [];
        foreach ($records as $record) {
            $empCode = $record['emp_code'];
            $date = $record['date'];

            if (!isset($byEmployee[$empCode])) {
                $byEmployee[$empCode] = [
                    'emp_code'           => $empCode,
                    'name'               => $record['name'] ?? $empCode,
                    'department'         => $record['department'] ?? null,
                    'employee_type'      => $record['employee_type'] ?? 'full_time',
                    'base_salary'        => $record['salary'] ?? null,
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
            }

            $st = $record['status'];
            $isWeekend = isWeekendOff($date);

            // 1. Stats Counting (Only count as 'Absent' if it's a working day)
            switch ($st) {
                case 'present':  $byEmployee[$empCode]['present_days']++; break;
                case 'half_day': $byEmployee[$empCode]['half_days']++; break;
                case 'absent':   
                    if (!$isWeekend) $byEmployee[$empCode]['absent_days']++; 
                    break;
                case 'work_from_home': $byEmployee[$empCode]['wfh_days']++; break;
                case 'paid_leave': $byEmployee[$empCode]['paid_leave_days']++; break;
                case 'unpaid_leave': $byEmployee[$empCode]['unpaid_leave_days']++; break;
                case 'leave':    $byEmployee[$empCode]['paid_leave_days']++; break; 
                case 'holiday':  $byEmployee[$empCode]['holiday_days']++; break;
                case 'comp_off': $byEmployee[$empCode]['comp_off_days']++; break;
            }

            // 2. Minute Crediting Logic (Professional Standard)
            $fullCreditStatuses = ['work_from_home', 'paid_leave', 'holiday', 'comp_off', 'leave'];
            if (in_array($st, $fullCreditStatuses)) {
                // RULE: Only give 510m credit if the day was actually a TARGET working day.
                // This prevents "Double Credit" for Holidays/Leaves that fall on Sundays.
                if (!$isWeekend) {
                    $byEmployee[$empCode]['total_work_minutes'] += 510;
                } else {
                    // If they worked on a weekend, add actual minutes (punches) but no bonus credit
                    $byEmployee[$empCode]['total_work_minutes'] += (int) ($record['work_minutes'] ?? 0);
                }
            } else {
                $byEmployee[$empCode]['total_work_minutes'] += (int) ($record['work_minutes'] ?? 0);
            }
        }

        $result = [];
        foreach ($byEmployee as $empCode => $data) {
            $empSalary = $data['base_salary'] ?? $monthlySalary;
            $result[] = $this->computeSalary($data, (float)$empSalary, $year, $month);
        }

        return $result;
    }

    /**
     * Calculate salary for a SINGLE employee
     */
    public function calculateEmployeeSalary(string $empCode, int $year, int $month, ?float $monthlySalary = null): ?array
    {
        helper('attendance');
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
            $st = $record['status'];
            $isWeekend = isWeekendOff($record['date']);

            switch ($st) {
                case 'present':  $data['present_days']++; break;
                case 'half_day': $data['half_days']++; break;
                case 'absent':   
                    if (!$isWeekend) $data['absent_days']++; 
                    break;
                case 'work_from_home': $data['wfh_days']++; break;
                case 'paid_leave': $data['paid_leave_days']++; break;
                case 'unpaid_leave': $data['unpaid_leave_days']++; break;
                case 'leave':    $data['paid_leave_days']++; break;
                case 'holiday':  $data['holiday_days']++; break;
                case 'comp_off': $data['comp_off_days']++; break;
            }

            $fullCreditStatuses = ['work_from_home', 'paid_leave', 'holiday', 'comp_off', 'leave'];
            if (in_array($st, $fullCreditStatuses)) {
                if (!$isWeekend) {
                    $data['total_work_minutes'] += 510;
                } else {
                    $data['total_work_minutes'] += (int) ($record['work_minutes'] ?? 0);
                }
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
        helper('attendance');
        $today = date('Y-m-d');
        $isCurrentMonth = ($year == date('Y') && $month == date('m'));
        $daysInMonth = (int)date('t', strtotime("$year-$month-01"));
        $endDay = $isCurrentMonth ? (int)date('d') : $daysInMonth;

        // 1. Calculate Dynamic Target-to-Date
        $targetToDateMin = 0;
        for ($d = 1; $d <= $endDay; $d++) {
            $currentDate = sprintf('%04d-%02d-%02d', $year, $month, $d);
            
            // Exclude Weekend Offs (Sundays, 1st/3rd Saturdays)
            if (isWeekendOff($currentDate)) continue;
            
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

        // 3. Earnings based on Actual Days in Month (Professional Standard)
        $dailyRate = $monthlySalary / $daysInMonth;
        
        // Base earned up to today
        $baseEarned = round($dailyRate * $endDay, 2); 
        
        // Total deduction based on real daily rate
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

    public function getMonthlyTotals(array $salaryData): array
    {
        $totalPaid = 0; $totalDeduction = 0; $totalWorkHours = 0; $count = count($salaryData);
        foreach ($salaryData as $emp) {
            $totalPaid += $emp['net_salary'] ?? 0;
            $totalDeduction += $emp['deduction'] ?? 0;
            $totalWorkHours += $emp['work_hours'] ?? 0;
        }
        return [
            'total_salary_paid' => round($totalPaid, 2),
            'total_deduction'   => round($totalDeduction, 2),
            'avg_work_hours'    => $count > 0 ? round($totalWorkHours / $count, 1) : 0,
            'employee_count'    => $count,
        ];
    }
}
