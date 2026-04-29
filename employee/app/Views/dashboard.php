<?php
  $name = $employee['name'] ?? 'Employee';
  $designation = $employee['designation'] ?? '';
  $status = $todayRow['status'] ?? 'absent';
  $statusLabel = $status === 'present' ? 'Present' : ($status === 'half_day' ? 'Half Day' : 'Absent');
?>
<!DOCTYPE html>
<html class="light" lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>GranthInfotech Attendance - Dashboard</title>
  <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" rel="stylesheet"/>
  <script id="tailwind-config">
    tailwind.config = { darkMode: "class" }
  </script>
  <style> body { background-color: #F5F2EA; } </style>
</head>
<body class="font-['Manrope'] text-[#3A3A3A] antialiased">
  <?= view('partials/employee_topbar') ?>

  <div class="flex min-h-screen">
    <?= view('partials/employee_sidebar', ['activePage' => 'dashboard']) ?>

    <main class="flex-1 md:ml-64 p-8 pt-24 overflow-y-auto w-full max-w-[1440px] mx-auto">
      <div class="flex justify-between items-end mb-12">
        <div>
          <h2 class="text-4xl font-extrabold tracking-tight">Welcome back, <?= esc($name) ?></h2>
          <p class="text-lg text-[#3A3A3A]/70 mt-2">Here's your attendance overview for today.</p>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-12 gap-6 mb-12">
        <div class="md:col-span-8 grid grid-cols-1 sm:grid-cols-3 gap-6">
          <div class="bg-white rounded-lg p-6 shadow-[0px_40px_40px_rgba(58,58,58,0.04)] border border-[#E5E1D5]">
            <p class="text-xs font-semibold tracking-wider uppercase text-[#3A3A3A]/60 mb-2">Today's Status</p>
            <h3 class="text-2xl font-semibold"><?= esc($statusLabel) ?></h3>
          </div>
          <div class="bg-white rounded-lg p-6 shadow-[0px_40px_40px_rgba(58,58,58,0.04)] border border-[#E5E1D5]">
            <p class="text-xs font-semibold tracking-wider uppercase text-[#3A3A3A]/60 mb-2">Working Hours</p>
            <h3 class="text-2xl font-semibold"><?= esc($todayHours) ?></h3>
          </div>
          <div class="bg-white rounded-lg p-6 shadow-[0px_40px_40px_rgba(58,58,58,0.04)] border border-[#E5E1D5]">
            <p class="text-xs font-semibold tracking-wider uppercase text-[#3A3A3A]/60 mb-2">Monthly Summary</p>
            <h3 class="text-2xl font-semibold"><?= (int) ($counts['present'] ?? 0) ?> Present</h3>
            <p class="text-sm text-[#3A3A3A]/60 mt-4"><?= (int) ($counts['half_day'] ?? 0) ?> Half Day • <?= (int) ($counts['absent'] ?? 0) ?> Absent</p>
          </div>
        </div>

        <div class="md:col-span-4 bg-white rounded-lg p-6 shadow-[0px_40px_40px_rgba(58,58,58,0.04)] border border-[#E5E1D5] flex items-center justify-between">
          <div>
            <h4 class="text-lg font-semibold"><?= esc($name) ?></h4>
            <p class="text-sm text-[#3A3A3A]/60"><?= esc($designation) ?></p>
            <p class="text-xs text-[#3A3A3A]/60 mt-1">Emp Code: <?= esc($employee['emp_code'] ?? '') ?></p>
          </div>
          <a href="<?= site_url('profile') ?>" class="text-[#3A3A3A] hover:bg-[#F5F2EA] p-2 rounded-full transition-colors">
            <span class="material-symbols-outlined">chevron_right</span>
          </a>
        </div>

        <div class="md:col-span-12 bg-white rounded-lg p-6 shadow-[0px_40px_40px_rgba(58,58,58,0.04)] border border-[#E5E1D5]">
          <div class="flex justify-between items-center mb-6 pb-4 border-b border-[#E5E1D5]">
            <h3 class="text-lg font-semibold">Daily Attendance</h3>
            <a href="<?= site_url('attendance') ?>" class="text-sm font-medium hover:text-[#EB5C49] transition-colors flex items-center">
              View All <span class="material-symbols-outlined text-[16px] ml-1">chevron_right</span>
            </a>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr class="border-b border-[#E5E1D5] text-xs font-semibold tracking-wider uppercase text-[#3A3A3A]/60">
                  <th class="py-3 px-4">Date</th>
                  <th class="py-3 px-4">Status</th>
                  <th class="py-3 px-4">Check-in</th>
                  <th class="py-3 px-4">Check-out</th>
                  <th class="py-3 px-4">Hours</th>
                </tr>
              </thead>
              <tbody class="text-sm">
                <?php foreach ($recent as $row): ?>
                  <?php
                    $st = $row['status'] ?? 'absent';
                    $badge = $st === 'present' ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20' : ($st === 'half_day' ? 'bg-amber-50 text-amber-700 ring-1 ring-amber-600/20' : 'bg-red-50 text-red-700 ring-1 ring-red-600/20');
                    $label = $st === 'present' ? 'Present' : ($st === 'half_day' ? 'Half Day' : 'Absent');
                  ?>
                  <tr class="hover:bg-[#F5F2EA] transition-colors border-b border-[#E5E1D5]/50 last:border-0">
                    <td class="py-4 px-4 font-medium"><?= esc($row['date']) ?></td>
                    <td class="py-4 px-4"><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold <?= $badge ?>"><?= esc($label) ?></span></td>
                    <td class="py-4 px-4 text-[#3A3A3A]/80"><?= $row['first_in'] ? date('h:i A', strtotime($row['first_in'])) : '--' ?></td>
                    <td class="py-4 px-4 text-[#3A3A3A]/80"><?= $row['last_out'] ? date('h:i A', strtotime($row['last_out'])) : '--' ?></td>
                    <td class="py-4 px-4 text-[#3A3A3A]/80"><?= esc($row['total_hours'] ?? '0') ?></td>
                  </tr>
                <?php endforeach; ?>
                <?php if (empty($recent)): ?>
                  <tr><td class="py-6 px-4 text-[#3A3A3A]/60" colspan="5">No attendance records found.</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </main>
  </div>
</body>
</html>

