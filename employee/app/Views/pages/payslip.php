<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<?php
    $emp = $employee ?? [];
    $sal = $salary ?? [];
    $empName = $emp['name'] ?? 'Unknown';
    $empCode = $emp['emp_code'] ?? '';
    $monthName = date('F', mktime(0, 0, 0, $month ?? date('n'), 1));
    $yearVal = $year ?? date('Y');
    $currency = '₹';

    $grossSalary = $sal['monthly_salary'] ?? 0;
    $netSalary = $sal['net_salary'] ?? 0;
    $deduction = $sal['deduction'] ?? 0;
    $ratio = $sal['ratio'] ?? 0;
?>

<!-- Breadcrumb -->
<div class="emp-breadcrumb mb-3">
    <a href="/salary">Salary</a>
    <span class="sep">›</span>
    <a href="/employees/<?= esc($empCode) ?>"><?= esc($empName) ?></a>
    <span class="sep">›</span>
    <span>Payslip</span>
</div>

<div class="page-header">
    <div>
        <h2>Payslip — <?= esc($empName) ?></h2>
        <p><?= esc($monthName) ?> <?= esc($yearVal) ?></p>
    </div>
    <div class="page-header-actions">
        <a href="/payslip/<?= esc($empCode) ?>/print?month=<?= $month ?>&year=<?= $year ?>" target="_blank" class="btn btn--primary">
            🖨 Print Payslip
        </a>
    </div>
</div>

<!-- Payslip Document -->
<div class="card payslip-document">
    <div class="card-body" style="padding:32px;">

        <!-- Company Header -->
        <div class="payslip-company-header">
            <div>
                <h2 style="font-size:1.4rem;font-weight:800;color:var(--text-primary);">Granth Infotech</h2>
                <p class="text-muted" style="font-size:0.82rem;">Indore, Madhya Pradesh, India</p>
            </div>
            <div class="payslip-title-block">
                <span class="payslip-title">PAYSLIP</span>
                <div class="payslip-period">
                    <span class="payslip-period-label">PAY PERIOD</span>
                    <span class="payslip-period-value"><?= esc($monthName) ?> <?= esc($yearVal) ?></span>
                </div>
            </div>
        </div>

        <hr class="payslip-divider">

        <!-- Employee Info Grid -->
        <div class="payslip-info-grid">
            <div class="payslip-info-row">
                <span class="payslip-info-label">Employee Name</span>
                <span class="payslip-info-value">: <?= esc($empName) ?></span>
            </div>
            <div class="payslip-info-row">
                <span class="payslip-info-label">Employee ID</span>
                <span class="payslip-info-value">: <?= esc($empCode) ?></span>
            </div>
            <div class="payslip-info-row">
                <span class="payslip-info-label">Department</span>
                <span class="payslip-info-value">: <?= esc($emp['department'] ?? 'General') ?></span>
            </div>
            <div class="payslip-info-row">
                <span class="payslip-info-label">Designation</span>
                <span class="payslip-info-value">: <?= esc($emp['designation'] ?? ucwords(str_replace('_', ' ', $emp['employee_type'] ?? 'Full Time'))) ?></span>
            </div>
        </div>

        <hr class="payslip-divider">

        <!-- Attendance Summary -->
        <h4 class="payslip-section-title">ATTENDANCE SUMMARY</h4>
        <div class="payslip-attendance-grid">
            <div class="payslip-att-box">
                <span class="payslip-att-label">Total Days</span>
                <span class="payslip-att-value"><?= ($sal['present_days']??0)+($sal['half_days']??0)+($sal['absent_days']??0) ?></span>
            </div>
            <div class="payslip-att-box">
                <span class="payslip-att-label">Present</span>
                <span class="payslip-att-value" style="color:var(--success-dark);"><?= $sal['present_days'] ?? 0 ?></span>
            </div>
            <div class="payslip-att-box">
                <span class="payslip-att-label">Absent</span>
                <span class="payslip-att-value" style="color:var(--danger);"><?= $sal['absent_days'] ?? 0 ?></span>
            </div>
            <div class="payslip-att-box">
                <span class="payslip-att-label">Half Days</span>
                <span class="payslip-att-value" style="color:var(--warning-dark);"><?= $sal['half_days'] ?? 0 ?></span>
            </div>
            <div class="payslip-att-box">
                <span class="payslip-att-label">Late Marks</span>
                <span class="payslip-att-value"><?= $sal['late_count'] ?? 0 ?></span>
            </div>
        </div>

        <hr class="payslip-divider">

        <!-- Earnings & Deductions Table -->
        <div class="payslip-earnings-grid">
            <div>
                <h4 class="payslip-section-title">EARNINGS</h4>
                <table class="payslip-table">
                    <tr><td>Gross Monthly Salary</td><td class="text-right font-mono"><?= $currency ?><?= number_format($grossSalary, 2) ?></td></tr>
                    <tr><td>Work Ratio</td><td class="text-right font-mono"><?= $ratio ?>%</td></tr>
                    <tr><td>Work Hours (Actual)</td><td class="text-right font-mono"><?= $sal['work_hours'] ?? 0 ?>h</td></tr>
                    <tr><td>Expected Hours</td><td class="text-right font-mono"><?= $sal['expected_hours'] ?? 0 ?>h</td></tr>
                    <tr class="payslip-total-row"><td><strong>Gross Earnings</strong></td><td class="text-right font-mono font-bold"><?= $currency ?><?= number_format($netSalary, 2) ?></td></tr>
                </table>
            </div>
            <div>
                <h4 class="payslip-section-title">DEDUCTIONS</h4>
                <table class="payslip-table">
                    <tr><td>Attendance Deduction</td><td class="text-right font-mono"><?= $currency ?><?= number_format($deduction, 2) ?></td></tr>
                    <tr><td>Late Arrival Fine</td><td class="text-right font-mono"><?= $currency ?>0.00</td></tr>
                    <tr class="payslip-total-row"><td><strong>Total Deductions</strong></td><td class="text-right font-mono font-bold"><?= $currency ?><?= number_format($deduction, 2) ?></td></tr>
                </table>
            </div>
        </div>

        <hr class="payslip-divider">

        <!-- Net Salary Payable -->
        <div class="payslip-net-salary">
            <div>
                <span class="payslip-net-label">NET SALARY PAYABLE</span>
            </div>
            <div class="payslip-net-amount"><?= $currency ?><?= number_format($netSalary, 2) ?></div>
        </div>

    </div>
</div>

<?= $this->endSection() ?>
