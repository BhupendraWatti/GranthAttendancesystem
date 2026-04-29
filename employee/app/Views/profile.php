<?php
  $name = $employee['name'] ?? 'Employee';
  $empCode = $employee['emp_code'] ?? '';
  $email = $employee['email'] ?? '--';
  $department = $employee['department'] ?? '--';
  $designation = $employee['designation'] ?? '--';
  $employeeType = ucwords(strtolower(str_replace('_', ' ', $employee['employee_type'] ?? '--')));
?>
<!DOCTYPE html>
<html class="light" lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Profile - GranthInfotech Attendance</title>
  <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@600&family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" rel="stylesheet"/>
  <style> body { background-color: #F5F2EA; } </style>
</head>
<body class="font-['Manrope'] text-[#3A3A3A] antialiased">
  <?= view('partials/employee_topbar') ?>

  <?= view('partials/employee_sidebar', ['activePage' => 'profile']) ?>

  <main class="md:ml-64 pt-16 min-h-screen">
    <div class="max-w-[1440px] mx-auto p-8 space-y-12">
      <div class="space-y-3">
        <nav class="flex items-center gap-2 text-sm font-medium text-[#747878] mb-2">
          <a class="hover:text-[#EB5C49]" href="<?= site_url('/') ?>">Dashboard</a>
          <span class="material-symbols-outlined text-[16px]">chevron_right</span>
          <span class="text-[#3A3A3A]">Profile</span>
        </nav>
        <h1 class="text-4xl font-extrabold tracking-tight">Employee Profile</h1>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
        <div class="col-span-12 md:col-span-8 bg-white rounded-xl p-6 shadow-[0px_40px_40px_rgba(58,58,58,0.04)] border border-[#E5E1D5] flex flex-col sm:flex-row gap-6 items-start sm:items-center">
          <div class="w-24 h-24 rounded-full bg-gradient-to-tr from-[#EB5C49] to-[#f08577] text-white flex items-center justify-center text-3xl font-extrabold shadow-inner border-[3px] border-white ring-1 ring-[#EB5C49]/20">
            <?= esc(strtoupper(substr($name, 0, 1))) ?>
          </div>
          <div class="flex-1 space-y-3">
            <div>
              <h2 class="text-2xl font-semibold"><?= esc($name) ?></h2>
              <p class="text-lg text-[#605e55] mt-1"><?= esc($designation) ?></p>
            </div>
            <div class="flex flex-wrap gap-4 text-sm text-[#3A3A3A]/70 pt-2">
              <div class="flex items-center gap-2"><span class="material-symbols-outlined text-[18px]">badge</span><span><?= esc($empCode) ?></span></div>
              <div class="flex items-center gap-2"><span class="material-symbols-outlined text-[18px]">mail</span><span><?= esc($email) ?></span></div>
            </div>
          </div>
        </div>

        <div class="col-span-12 md:col-span-4 bg-white rounded-xl p-6 shadow-[0px_40px_40px_rgba(58,58,58,0.04)] border border-[#E5E1D5]">
          <h3 class="text-lg font-semibold border-b border-[#E5E1D5] pb-4 mb-6">Work Details</h3>
          <div class="flex flex-col gap-6">
            <div class="space-y-1">
              <p class="text-sm font-medium text-[#747878]">Department</p>
              <p class="text-lg"><?= esc($department) ?></p>
            </div>
            <div class="space-y-1">
              <p class="text-sm font-medium text-[#747878]">Designation</p>
              <p class="text-lg"><?= esc($designation) ?></p>
            </div>
            <div class="space-y-1">
              <p class="text-sm font-medium text-[#747878]">Employee Type</p>
              <span class="inline-flex items-center px-3 py-1 rounded-full bg-[#e3dfd4] text-[#656359] text-sm font-medium">
                <?= esc($employeeType) ?>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
</body>
</html>

