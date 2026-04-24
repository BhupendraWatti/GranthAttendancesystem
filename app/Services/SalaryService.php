<?php

namespace App\Services;

use App\Models\AttendanceDailyModel;
use App\Models\EmployeeModel;

/**
 * SalaryService — Core Salary Calculation Engine
 *
 * RULES (from specification):
 * - Full-time: 510 min/day × 23 working days = 11,730 expected minutes/month
 * - Intern:    330 min/day × 23 working days =  7,590 expected minutes/month
 *
 * FORMULA:
 *   salary = (actual_minutes / expected_minutes) × monthly_salary
 *   deduction = monthly_salary - salary
 *
 * This service NEVER uses mock data. All data comes from attendance_daily.
 */
class SalaryService
{
    private AttendanceDailyModel $attendanceModel;
    private EmployeeModel $employeeModel;

    private int $workingDaysPerMonth;
    private int $fullTimeMinutesPerDay;
    private int $internMinutesPerDay;

    public function __construct()
    {
        $this->attendanceModel = new AttendanceDailyModel();
        $this->employeeModel  = new EmployeeModel();

        $this->workingDaysPerMonth   = (int) env('WORKING_DAYS_PER_MONTH', 23);
        $this->fullTimeMinutesPerDay = (int) env('FULL_TIME_MINUTES', 510);
        $this->internMinutesPerDay   = (int) env('INTERN_MINUTES', 330);
    }

    /**
     * Calculate salary for ALL employees for a given month
     *
     * @return array Each element: [emp_code, name, employee_type, present_days, absent_days,
     *               half_days, late_count, total_work_minutes, total_late_minutes,
     *               expected_minutes, work_hours, expected_hours, monthly_salary,
     *               calculated_salary, deduction, net_salary]
     */
    public function calculateAllSalaries(int $year, int $month, ?float $defaultMonthlySalary = null): array
    {
        $monthlySalary = $defaultMonthlySalary ?? (float) env('DEFAULT_MONTHLY_SALARY', 25000);

        // Get all attendance records for the month with employee names
        $records = $this->attendanceModel->getAllMonthly($year, $month);

        if (empty($records)) {
            return [];
        }

        // Group by employee
        $byEmployee = [];
        foreach ($records as $record) {
            $empCode = $record['emp_code'];

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
                    'late_count'         => 0,
                    'total_work_minutes' => 0,
                    'total_late_minutes' => 0,
                    'records'            => [],
                ];
            }

            switch ($record['status']) {
                case 'present':  $byEmployee[$empCode]['present_days']++; break;
                case 'half_day': $byEmployee[$empCode]['half_days']++; break;
                case 'absent':   $byEmployee[$empCode]['absent_days']++; break;
            }

            $byEmployee[$empCode]['total_work_minutes'] += (int) ($record['work_minutes'] ?? 0);
            $lateMin = (int) ($record['late_minutes'] ?? 0);
            $byEmployee[$empCode]['total_late_minutes'] += $lateMin;
            if ($lateMin > 0) {
                $byEmployee[$empCode]['late_count']++;
            }
        }

        // Calculate salary for each employee
        $result = [];
        foreach ($byEmployee as $empCode => $data) {
            $empSalary = $data['base_salary'] ?? $monthlySalary;
            $result[] = $this->computeSalary($data, (float)$empSalary);
        }

        return $result;
    }

    /**
     * Calculate salary for a SINGLE employee for a given month
     */
    public function calculateEmployeeSalary(string $empCode, int $year, int $month, ?float $monthlySalary = null): ?array
    {
        $employee = $this->employeeModel->findByCode($empCode);
        if (!$employee) {
            return null;
        }

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
            'late_count'         => 0,
            'total_work_minutes' => 0,
            'total_late_minutes' => 0,
        ];

        foreach ($records as $record) {
            switch ($record['status']) {
                case 'present':  $data['present_days']++; break;
                case 'half_day': $data['half_days']++; break;
                case 'absent':   $data['absent_days']++; break;
            }
            $data['total_work_minutes'] += (int) ($record['work_minutes'] ?? 0);
            $lateMin = (int) ($record['late_minutes'] ?? 0);
            $data['total_late_minutes'] += $lateMin;
            if ($lateMin > 0) {
                $data['late_count']++;
            }
        }

        return $this->computeSalary($data, (float)$salary);
    }

    /**
     * Core salary computation (shared logic)
     *
     * Formula: salary = (actual_minutes / expected_minutes) × monthly_salary
     */
    private function computeSalary(array $data, float $monthlySalary): array
    {
        $type = $data['employee_type'] ?? 'full_time';
        $minutesPerDay = ($type === 'intern') ? $this->internMinutesPerDay : $this->fullTimeMinutesPerDay;
        $expectedMinutes = $minutesPerDay * $this->workingDaysPerMonth;

        $actualMinutes = $data['total_work_minutes'];
        $workHours     = round($actualMinutes / 60, 1);
        $expectedHours = round($expectedMinutes / 60, 1);

        // Core formula: salary = (actual / expected) × monthly
        // Cap at 100% — no overtime pay
        $ratio = ($expectedMinutes > 0) ? min(1, $actualMinutes / $expectedMinutes) : 0;
        $calculatedSalary = round($monthlySalary * $ratio, 2);
        $deduction = round($monthlySalary - $calculatedSalary, 2);

        // Effective days = present + (half_days × 0.5)
        $effectiveDays = $data['present_days'] + ($data['half_days'] * 0.5);

        return array_merge($data, [
            'expected_minutes'  => $expectedMinutes,
            'work_hours'        => $workHours,
            'expected_hours'    => $expectedHours,
            'monthly_salary'    => $monthlySalary,
            'calculated_salary' => $calculatedSalary,
            'deduction'         => $deduction,
            'net_salary'        => $calculatedSalary,
            'effective_days'    => $effectiveDays,
            'working_days'      => $this->workingDaysPerMonth,
            'ratio'             => round($ratio * 100, 1),
        ]);
    }

    /**
     * Get aggregate totals for all employees in a month
     */
    public function getMonthlyTotals(array $salaryData): array
    {
        $totalPaid      = 0;
        $totalDeduction = 0;
        $totalWorkHours = 0;
        $count          = count($salaryData);

        foreach ($salaryData as $emp) {
            $totalPaid      += $emp['net_salary'] ?? 0;
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
