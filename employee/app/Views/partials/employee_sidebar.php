<?php
  $activePage = $activePage ?? 'dashboard';
  $showLogoutButton = $showLogoutButton ?? false;
?>
<nav class="hidden md:flex flex-col pt-8 pb-8 px-4 h-screen fixed left-0 top-0 w-64 border-r border-[#E5E1D5] bg-[#F5F2EA]/30 z-40 text-sm font-medium">
  <div class="mb-10 px-4">
    <h2 class="text-lg font-black text-[#3A3A3A]">Employee Portal</h2>
    <p class="text-xs text-[#3A3A3A]/60 mt-1">Self Service</p>
  </div>
  <ul class="flex flex-col gap-2 flex-grow">
    <li>
      <a class="flex items-center gap-3 px-4 py-3 rounded-lg <?= $activePage === 'dashboard' ? 'text-[#EB5C49] font-bold border-r-4 border-[#EB5C49] bg-[#F5F2EA]/50' : 'text-[#3A3A3A]/60 hover:text-[#3A3A3A] hover:bg-[#F5F2EA]' ?>" href="<?= site_url('/') ?>">
        <span class="material-symbols-outlined">dashboard</span>Dashboard
      </a>
    </li>
    <li>
      <a class="flex items-center gap-3 px-4 py-3 rounded-lg <?= $activePage === 'attendance' ? 'text-[#EB5C49] font-bold border-r-4 border-[#EB5C49] bg-[#F5F2EA]/50' : 'text-[#3A3A3A]/60 hover:text-[#3A3A3A] hover:bg-[#F5F2EA]' ?>" href="<?= site_url('attendance') ?>">
        <span class="material-symbols-outlined">how_to_reg</span>Attendance
      </a>
    </li>
    <li>
      <a class="flex items-center gap-3 px-4 py-3 rounded-lg <?= $activePage === 'salary' ? 'text-[#EB5C49] font-bold border-r-4 border-[#EB5C49] bg-[#F5F2EA]/50' : 'text-[#3A3A3A]/60 hover:text-[#3A3A3A] hover:bg-[#F5F2EA]' ?>" href="<?= site_url('salary') ?>">
        <span class="material-symbols-outlined">payments</span>Salary
      </a>
    </li>
    <li>
      <a class="flex items-center gap-3 px-4 py-3 rounded-lg <?= $activePage === 'profile' ? 'text-[#EB5C49] font-bold border-r-4 border-[#EB5C49] bg-[#F5F2EA]/50' : 'text-[#3A3A3A]/60 hover:text-[#3A3A3A] hover:bg-[#F5F2EA]' ?>" href="<?= site_url('profile') ?>">
        <span class="material-symbols-outlined">account_circle</span>Profile
      </a>
    </li>
  </ul>
  <?php if ($showLogoutButton): ?>
    <div class="px-4 mt-auto">
      <a class="block text-center w-full bg-[#EB5C49] text-white py-3 rounded-lg font-semibold hover:bg-[#d54e3d]" href="<?= site_url('auth/logout') ?>">Logout</a>
    </div>
  <?php endif; ?>
</nav>

