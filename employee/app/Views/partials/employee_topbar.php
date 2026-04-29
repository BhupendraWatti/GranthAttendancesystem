<?php
  $headerShiftClass = $headerShiftClass ?? '';
  $headerRight = $headerRight ?? '';
?>
<header class="fixed top-0 w-full md:left-64 md:w-[calc(100%-16rem)] z-30 border-b border-[#E5E1D5] bg-white bg-opacity-95 backdrop-blur">
  <div class="flex justify-between items-center h-16 px-8">
    <div class="hidden md:block">
      <div class="text-lg font-bold tracking-tight text-[#3A3A3A]">GranthInfotech Attendance</div>
    </div>
    <div class="flex items-center gap-4 ml-auto">
      <?= $headerRight ?>
      <a href="<?= site_url('auth/logout') ?>" class="md:hidden text-sm font-semibold text-[#3A3A3A]/70 hover:text-[#EB5C49]">Logout</a>
    </div>
  </div>
</header>

