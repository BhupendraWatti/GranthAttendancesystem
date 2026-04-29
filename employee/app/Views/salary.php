<?php
  $monthName = date('F', strtotime(sprintf('%04d-%02d-01', $year, $month)));
  $net = $salary['net_salary'] ?? 0;
  $base = $salary['monthly_salary'] ?? 0;
  $ded = $salary['deduction'] ?? 0;
  $ratio = $salary['ratio'] ?? null;
?>
<!DOCTYPE html>
<html class="light" lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Salary - GranthInfotech Attendance</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@600&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" rel="stylesheet"/>
  <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
  <style>
    body { background-color: #F5F2EA; }
  </style>
</head>
<body class="text-[#1c1b1b] font-['Manrope'] antialiased overflow-x-hidden">
  <?= view('partials/employee_topbar') ?>

  <?= view('partials/employee_sidebar', ['activePage' => 'salary']) ?>

  <main class="md:pl-64 pt-16 min-h-screen">
    <div class="p-8 max-w-[1440px] mx-auto">
      <div class="mb-12">
        <nav class="flex items-center gap-2 text-sm font-medium text-[#747878] mb-2">
          <a class="hover:text-[#EB5C49]" href="<?= site_url('/') ?>">Dashboard</a>
          <span class="material-symbols-outlined text-[16px]">chevron_right</span>
          <span class="text-[#3A3A3A]">Salary</span>
        </nav>
        <div class="flex justify-between items-end">
          <div>
            <h1 class="text-4xl font-extrabold tracking-tight">Salary & Compensation</h1>
            <p class="text-lg text-[#444748] mt-2 max-w-2xl">Overview of your current earnings and attendance impact.</p>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-12">
        <div class="lg:col-span-2 bg-white rounded-lg p-6 shadow-[0px_40px_40px_rgba(58,58,58,0.04)] border border-[#E5E1D5]">
          <div class="flex justify-between items-center mb-6 border-b border-[#E5E1D5] pb-4">
            <h2 class="text-lg font-semibold">Current Month Est.</h2>
            <span class="px-3 py-1 bg-[#F1EDE3] rounded-full text-xs font-semibold tracking-wider uppercase text-[#444748]"><?= esc($monthName) ?> <?= esc((string)$year) ?></span>
          </div>
          <div class="flex flex-col md:flex-row gap-12 items-center justify-between">
            <div class="flex-1 text-center md:text-left">
              <p class="text-sm text-[#444748] mb-1">Estimated Net Payable</p>
              <div class="text-5xl font-extrabold text-[#242525]">
                ₹<?= esc(number_format((float)$net, 2)) ?>
              </div>
              <?php if ($ratio !== null): ?>
                <div class="mt-4 inline-flex items-center gap-2 px-3 py-1 bg-[#F5F2EA] rounded-full text-sm text-[#605e55]">
                  <span class="material-symbols-outlined text-[16px] text-[#EB5C49]">info</span>
                  <span>Attendance ratio: <?= esc(number_format((float)$ratio, 2)) ?>%</span>
                </div>
              <?php endif; ?>
            </div>
            <div class="w-full md:w-auto min-w-[280px] space-y-4">
              <div class="flex justify-between items-center py-2">
                <span class="text-sm text-[#444748]">Base Salary</span>
                <span class="text-lg font-medium">₹<?= esc(number_format((float)$base, 2)) ?></span>
              </div>
              <div class="flex justify-between items-center py-2 border-t border-dashed border-[#C4C7C7]">
                <span class="text-sm text-[#444748] flex items-center gap-1">
                  <span class="material-symbols-outlined text-[16px] text-red-600">trending_down</span>
                  Deductions
                </span>
                <span class="text-lg font-medium text-red-700">-₹<?= esc(number_format((float)$ded, 2)) ?></span>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg p-6 shadow-[0px_40px_40px_rgba(58,58,58,0.04)] border border-[#E5E1D5] flex flex-col justify-center items-center">
          <h2 class="text-lg font-semibold mb-4">Attendance Impact</h2>
          <p class="text-sm text-[#444748] text-center">Salary is calculated based on your daily attendance, late arrivals, and total working hours.</p>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow-[0px_40px_40px_rgba(58,58,58,0.04)] border border-[#E5E1D5] overflow-hidden">
        <div class="p-6 border-b border-[#E5E1D5] flex justify-between items-center">
          <div>
            <h2 class="text-2xl font-semibold">Payslip</h2>
            <p class="text-sm text-[#444748] mt-1">Download your monthly payslip as PDF.</p>
          </div>
          <div class="flex gap-2">
            <a href="<?= site_url('salary/payslip?month=' . (int) $month . '&year=' . (int) $year) ?>" class="px-4 py-2 border border-[#E5E1D5] rounded-lg text-sm font-semibold hover:bg-[#F5F2EA]">
              View Payslip
            </a>
            <a href="<?= site_url('salary/payslip/print?month=' . (int) $month . '&year=' . (int) $year) ?>" target="_blank" class="px-4 py-2 bg-[#3A3A3A] text-white rounded-lg text-sm font-semibold hover:bg-black">
              Download PDF
            </a>
          </div>
        </div>
        <div class="p-6 text-sm text-[#444748] border-t border-[#E5E1D5] bg-[#F5F2EA]/30">
          This is a system generated document. Real-time changes in attendance might take up to 24 hours to reflect on the printable PDF.
        </div>
      </div>
    </div>
  </main>
</body>
</html>

