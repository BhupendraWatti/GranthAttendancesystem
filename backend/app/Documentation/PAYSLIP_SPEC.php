<?php
/**
 * Suggested JSON Structure for Frontend Integration
 * This is what the SalaryService/Controller should return to the view.
 */

$payslipData = [
    "company" => [
        "name" => "GRANTH",
        "address" => "402, Signature Building, NR Road, Bangalore - 560001",
        "email" => "payroll@granth.com",
        "phone" => "+91 80 4567 8900",
        "logo" => "assets/img/logo_dark.png"
    ],
    "period" => [
        "month" => "February",
        "year" => 2026,
        "label" => "February 2026"
    ],
    "employee" => [
        "name" => "John Doe",
        "code" => "EMP-2024-001",
        "department" => "Software Engineering",
        "designation" => "Senior Full Stack Developer",
        "doj" => "2024-01-15",
        "payment_mode" => "Bank Transfer"
    ],
    "attendance" => [
        "working_days" => 24,
        "present_days" => 22,
        "absent_days" => 1.5,
        "leave_days" => 0.5,
        "late_count" => 3,
        "late_penalty_amount" => 500,
        "total_minutes" => 11220,
        "expected_minutes" => 12240
    ],
    "earnings" => [
        ["name" => "Basic Salary", "logic" => "Fixed Monthly", "amount" => 45000],
        ["name" => "HRA", "logic" => "40% of Basic", "amount" => 18000],
        ["name" => "Travel Allowance", "logic" => "Conveyance", "amount" => 5000],
        ["name" => "Special Bonus", "logic" => "Performance Based", "amount" => 2000]
    ],
    "deductions" => [
        ["name" => "Absent Deduction", "logic" => "1.5 days shortfall", "amount" => 3500],
        ["name" => "Late In Penalty", "logic" => "3 instances", "amount" => 500],
        ["name" => "Professional Tax", "logic" => "Statutory", "amount" => 200]
    ],
    "summary" => [
        "gross_salary" => 70000,
        "total_deductions" => 4200,
        "net_salary" => 65800,
        "net_salary_words" => "Sixty-Five Thousand Eight Hundred Rupees Only"
    ]
];

/**
 * Suggested Database Schema Improvements
 * 
 * 1. salary_structures
 *    - id, emp_code, component_name, component_type (earning/deduction), amount, is_fixed, created_at
 * 
 * 2. payslip_history (To lock values for historical records)
 *    - id, emp_code, month, year, json_data, status, generated_by, created_at
 */

/**
 * Calculation Flow Strategy:
 * 
 * 1. Fetch Employee Master (Salary, Dept, Desig)
 * 2. Query AttendanceDailyModel for the month range.
 * 3. Calculate Target Hours (excluding holidays/weekends).
 * 4. Calculate Actual Hours worked.
 * 5. Determine Shortfall -> Convert to Deduction Days.
 * 6. Map Employee's Salary Components (Basic, HRA, etc.).
 * 7. Subtract Auto-Deductions from Gross.
 * 8. Format results into the $payslipData structure above.
 */
