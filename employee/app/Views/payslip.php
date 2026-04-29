<?php
  $emp = $employee ?? [];
  $sal = $salary ?? [];
  $empName = $emp['name'] ?? 'Unknown';
  $empCode = $emp['emp_code'] ?? '';
  $monthName = date('F', mktime(0, 0, 0, $month ?? date('n'), 1));
  $yearVal = $year ?? date('Y');
  $currency = '₹';

  $grossSalary = (float) ($sal['monthly_salary'] ?? 0);
  $netSalary = (float) ($sal['net_salary'] ?? 0);
  $deduction = (float) ($sal['deduction'] ?? 0);
  $ratio = (float) ($sal['ratio'] ?? 0);
?>
<!DOCTYPE html>
<html class="light" lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Payslip - GranthInfotech Attendance</title>
  <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@600&family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" rel="stylesheet"/>
  <style> body { background-color: #F5F2EA; } </style>
</head>
<body class="font-['Manrope'] text-[#3A3A3A] antialiased">
  <?= view('partials/employee_topbar') ?>
  <?= view('partials/employee_sidebar', ['activePage' => 'salary']) ?>

  <main class="md:pl-64 pt-16 min-h-screen">
    <div class="max-w-[1200px] mx-auto p-8">
      <div class="flex items-center justify-between mb-6">
        <div>
          <h1 class="text-3xl font-extrabold">Payslip</h1>
          <p class="text-sm text-[#3A3A3A]/70"><?= esc($monthName) ?> <?= esc((string) $yearVal) ?></p>
        </div>
        <a href="<?= site_url('salary/payslip/print?month=' . (int) $month . '&year=' . (int) $year) ?>" target="_blank" class="px-4 py-2 bg-[#3A3A3A] text-white rounded-lg text-sm font-semibold hover:bg-black">
          Print / Download PDF
        </a>
      </div>

      <div class="bg-white rounded-xl border border-[#E5E1D5] shadow-[0px_40px_40px_rgba(58,58,58,0.04)] p-6">
        <div class="grid grid-cols-2 gap-4 text-sm mb-6">
          <div><span class="text-[#3A3A3A]/60">Employee Name:</span> <span class="font-semibold"><?= esc($empName) ?></span></div>
          <div><span class="text-[#3A3A3A]/60">Employee Code:</span> <span class="font-semibold"><?= esc($empCode) ?></span></div>
          <div><span class="text-[#3A3A3A]/60">Department:</span> <span class="font-semibold"><?= esc($emp['department'] ?? 'General') ?></span></div>
          <div><span class="text-[#3A3A3A]/60">Designation:</span> <span class="font-semibold"><?= esc($emp['designation'] ?? ucwords(str_replace('_', ' ', $emp['employee_type'] ?? 'Full Time'))) ?></span></div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="rounded-lg border border-[#E5E1D5] p-4 h-full flex flex-col">
            <h3 class="text-xs uppercase tracking-wider text-[#3A3A3A]/60 mb-3">Earnings</h3>
            <div class="flex justify-between py-1"><span>Gross Monthly Salary</span><span class="font-mono"><?= $currency ?><?= number_format($grossSalary, 2) ?></span></div>
            <div class="flex justify-between py-1"><span>Attendance Ratio</span><span class="font-mono"><?= number_format($ratio, 2) ?>%</span></div>
            <div class="mt-auto flex justify-between py-1 border-t border-dashed pt-2"><span class="font-semibold">Gross Earnings</span><span class="font-mono font-semibold"><?= $currency ?><?= number_format($grossSalary, 2) ?></span></div>
          </div>
          <div class="rounded-lg border border-[#E5E1D5] p-4 h-full flex flex-col">
            <h3 class="text-xs uppercase tracking-wider text-[#3A3A3A]/60 mb-3">Deductions</h3>
            <div class="flex justify-between py-1"><span>Attendance Deduction</span><span class="font-mono"><?= $currency ?><?= number_format($deduction, 2) ?></span></div>
            <div class="flex justify-between py-1"><span>Late Arrival Fine</span><span class="font-mono"><?= $currency ?>0.00</span></div>
            <div class="mt-auto flex justify-between py-1 border-t border-dashed pt-2"><span class="font-semibold">Total Deductions</span><span class="font-mono font-semibold"><?= $currency ?><?= number_format($deduction, 2) ?></span></div>
          </div>
        </div>

        <div class="mt-6 rounded-xl bg-[#1f1f1f] text-white px-5 py-4 flex justify-between items-center">
          <span class="text-sm uppercase tracking-wider">Net Salary Payable</span>
          <span class="text-3xl font-extrabold"><?= $currency ?><?= number_format($netSalary, 2) ?></span>
        </div>
      </div>
    </div>
  </main>
</body>
</html>

