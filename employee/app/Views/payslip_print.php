<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payslip — <?= esc($employee['name'] ?? '') ?> — <?= date('F Y', mktime(0,0,0,$month??1,1,$year??date('Y'))) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background: #f0f2f5; color: #111827; line-height: 1.6; }
        .payslip-page { max-width: 800px; margin: 24px auto; background: white; border-radius: 12px; box-shadow: 0 4px 24px rgba(0,0,0,0.08); padding: 48px; }
        .company-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; }
        .company-header h1 { font-size: 1.5rem; font-weight: 800; color: #111827; }
        .company-header p { font-size: 0.8rem; color: #6b7280; }
        .payslip-badge { text-align: right; }
        .payslip-badge .title { font-size: 2rem; font-weight: 800; color: #c7d2fe; letter-spacing: 0.1em; }
        .payslip-badge .period { background: #eef2ff; border: 1px solid #c7d2fe; border-radius: 8px; padding: 8px 16px; display: inline-block; margin-top: 8px; text-align: center; }
        .payslip-badge .period-label { font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.1em; color: #6b7280; font-weight: 600; }
        .payslip-badge .period-value { font-size: 1.1rem; font-weight: 700; color: #111827; }
        hr { border: none; border-top: 1px solid #e5e7eb; margin: 20px 0; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px 40px; margin: 16px 0; }
        .info-row { display: flex; font-size: 0.85rem; }
        .info-label { font-weight: 600; text-transform: uppercase; font-size: 0.72rem; letter-spacing: 0.04em; color: #6b7280; min-width: 140px; padding: 4px 0; }
        .info-value { padding: 4px 0; font-weight: 500; }
        .section-title { font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: #6b7280; margin-bottom: 12px; }
        .att-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 12px; margin: 12px 0; }
        .att-box { text-align: center; padding: 14px 8px; border: 1px solid #e5e7eb; border-radius: 8px; }
        .att-box .label { font-size: 0.7rem; color: #6b7280; text-transform: uppercase; letter-spacing: 0.04em; }
        .att-box .value { font-size: 1.5rem; font-weight: 800; margin-top: 4px; }
        .earnings-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }
        .earn-table { width: 100%; border-collapse: collapse; font-size: 0.85rem; }
        .earn-table td { padding: 8px 12px; border-bottom: 1px solid #f3f4f6; }
        .earn-table .total-row td { border-top: 2px solid #e5e7eb; font-weight: 700; background: #f9fafb; }
        .text-right { text-align: right; }
        .font-mono { font-family: 'SF Mono', 'Fira Code', monospace; }
        .net-salary-box { display: flex; justify-content: space-between; align-items: center; background: linear-gradient(135deg, #1e1b4b, #312e81); color: white; padding: 20px 28px; border-radius: 12px; margin-top: 24px; }
        .net-salary-box .label { font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.06em; opacity: 0.8; }
        .net-salary-box .amount { font-size: 2rem; font-weight: 800; letter-spacing: -0.02em; }
        .print-controls { text-align: center; margin: 24px auto; }
        .print-controls button { background: #6366f1; color: white; border: none; padding: 12px 32px; border-radius: 8px; font-family: inherit; font-size: 0.9rem; font-weight: 600; cursor: pointer; transition: all 0.2s ease; }
        .print-controls button:hover { background: #4f46e5; }
        @media print {
            body { background: white; }
            .payslip-page { box-shadow: none; margin: 0; padding: 24px; max-width: 100%; }
            .print-controls { display: none; }
        }
    </style>
</head>
<body>
<?php
    $emp = $employee ?? [];
    $sal = $salary ?? [];
    $empName = $emp['name'] ?? 'Unknown';
    $empCode = $emp['emp_code'] ?? '';
    $monthName = date('F', mktime(0, 0, 0, $month ?? 1, 1));
    $yearVal = $year ?? date('Y');
    $currency = '₹';
    $grossSalary = $sal['monthly_salary'] ?? 0;
    $netSalary = $sal['net_salary'] ?? 0;
    $deduction = $sal['deduction'] ?? 0;
    $ratio = $sal['ratio'] ?? 0;
    $totalDays = ($sal['present_days']??0)+($sal['half_days']??0)+($sal['absent_days']??0);
?>

<div class="print-controls">
    <button onclick="window.print()">Print / Download PDF</button>
</div>

<div class="payslip-page">
    <div class="company-header">
        <div>
            <h1>Granth Infotech</h1>
            <p>Indore, Madhya Pradesh, India</p>
        </div>
        <div class="payslip-badge">
            <div class="title">PAYSLIP</div>
            <div class="period">
                <div class="period-label">PAY PERIOD</div>
                <div class="period-value"><?= esc($monthName) ?> <?= esc($yearVal) ?></div>
            </div>
        </div>
    </div>

    <hr>

    <div class="info-grid">
        <div class="info-row"><span class="info-label">Employee Name</span><span class="info-value">: <?= esc($empName) ?></span></div>
        <div class="info-row"><span class="info-label">Employee ID</span><span class="info-value">: <?= esc($empCode) ?></span></div>
        <div class="info-row"><span class="info-label">Department</span><span class="info-value">: <?= esc($emp['department'] ?? 'General') ?></span></div>
        <div class="info-row"><span class="info-label">Designation</span><span class="info-value">: <?= esc($emp['designation'] ?? ucwords(str_replace('_', ' ', $emp['employee_type'] ?? 'Full Time'))) ?></span></div>
    </div>

    <hr>

    <div class="section-title">ATTENDANCE SUMMARY</div>
    <div class="att-grid">
        <div class="att-box"><div class="label">Total Days</div><div class="value"><?= $totalDays ?></div></div>
        <div class="att-box"><div class="label">Present</div><div class="value" style="color:#059669;"><?= $sal['present_days'] ?? 0 ?></div></div>
        <div class="att-box"><div class="label">Absent</div><div class="value" style="color:#ef4444;"><?= $sal['absent_days'] ?? 0 ?></div></div>
        <div class="att-box"><div class="label">Half Days</div><div class="value" style="color:#d97706;"><?= $sal['half_days'] ?? 0 ?></div></div>
        <div class="att-box"><div class="label">Late Marks</div><div class="value"><?= $sal['late_count'] ?? 0 ?></div></div>
    </div>

    <hr>

    <div class="earnings-grid">
        <div>
            <div class="section-title">EARNINGS</div>
            <table class="earn-table">
                <tr><td>Gross Monthly Salary</td><td class="text-right font-mono"><?= $currency ?><?= number_format($grossSalary, 2) ?></td></tr>
                <tr><td>Attendance Ratio</td><td class="text-right font-mono"><?= $ratio ?>%</td></tr>
                <tr><td>Work Hours (Actual)</td><td class="text-right font-mono"><?= $sal['work_hours'] ?? 0 ?>h</td></tr>
                <tr><td>Expected Hours</td><td class="text-right font-mono"><?= $sal['expected_hours'] ?? 0 ?>h</td></tr>
                <tr class="total-row"><td>Gross Earnings (A)</td><td class="text-right font-mono"><?= $currency ?><?= number_format($grossSalary, 2) ?></td></tr>
            </table>
        </div>
        <div>
            <div class="section-title">DEDUCTIONS</div>
            <table class="earn-table">
                <tr><td>Attendance Deduction</td><td class="text-right font-mono"><?= $currency ?><?= number_format($deduction, 2) ?></td></tr>
                <tr><td>Late Arrival Fine</td><td class="text-right font-mono"><?= $currency ?>0.00</td></tr>
                <tr><td>Loss of Pay (LOP)</td><td class="text-right font-mono"><?= $currency ?>0.00</td></tr>
                <tr class="total-row"><td>Total Deductions (B)</td><td class="text-right font-mono"><?= $currency ?><?= number_format($deduction, 2) ?></td></tr>
            </table>
        </div>
    </div>

    <div class="net-salary-box">
        <div><div class="label">NET SALARY PAYABLE (A - B)</div></div>
        <div class="amount"><?= $currency ?><?= number_format($netSalary, 2) ?></div>
    </div>
</div>

</body>
</html>

