<?php
  $monthName = date('F', strtotime(sprintf('%04d-%02d-01', $year, $month)));
  $pad = $firstDow;
  $totalCells = $pad + $daysInMonth;
  $weeks = (int) ceil($totalCells / 7);
?>
<!DOCTYPE html>
<html class="light" lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Attendance Details - GranthInfotech Attendance</title>
  <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Inter:wght@600&display=swap" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" rel="stylesheet"/>
  <style> body { background-color: #F5F2EA; } </style>
</head>
<body class="min-h-screen flex font-['Manrope'] text-[#3A3A3A]">
  <?= view('partials/employee_sidebar', ['activePage' => 'attendance', 'showLogoutButton' => true]) ?>

  <div class="flex-1 md:ml-64 flex flex-col min-w-0">
    <?= view('partials/employee_topbar', [
      'headerShiftClass' => 'md:pl-64',
      'headerRight' => '<div class="text-sm text-[#3A3A3A]/70">' . esc($monthName) . ' ' . esc((string) $year) . '</div>',
    ]) ?>

    <main class="flex-1 p-8 mt-16 overflow-y-auto max-w-[1440px] mx-auto w-full">
      <div class="mb-12 flex flex-col gap-3">
        <nav class="flex items-center gap-2 text-sm font-medium text-[#747878] mb-2">
          <a class="hover:text-[#EB5C49]" href="<?= site_url('/') ?>">Dashboard</a>
          <span class="material-symbols-outlined text-[16px]">chevron_right</span>
          <span class="text-[#3A3A3A]">Attendance</span>
        </nav>
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
          <h1 class="text-4xl font-extrabold tracking-tight">Attendance Details</h1>
          <form class="flex gap-2" method="get" action="<?= site_url('attendance') ?>">
            <input type="number" name="year" value="<?= esc((string)$year) ?>" class="w-24 rounded-lg border border-[#E5E1D5] bg-[#F5F2EA] px-3 py-2 text-sm focus:ring-2 focus:ring-[#EB5C49] focus:outline-none" />
            <select name="month" class="w-28 rounded-lg border border-[#E5E1D5] bg-[#F5F2EA] px-3 py-2 text-sm focus:ring-2 focus:ring-[#EB5C49] focus:outline-none">
              <?php for ($m = 1; $m <= 12; $m++): ?>
                <option value="<?= $m ?>" <?= $m == $month ? 'selected' : '' ?>><?= date('F', mktime(0,0,0,$m,1)) ?></option>
              <?php endfor; ?>
            </select>
            <button class="px-4 py-2 rounded-lg bg-[#EB5C49] text-white font-semibold text-sm hover:bg-[#d54e3d] transition-colors">Go</button>
          </form>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <div class="lg:col-span-8 flex flex-col gap-6">
          <div class="bg-white rounded-lg p-6 shadow-[0px_40px_40px_rgba(58,58,58,0.04)] border border-[#E5E1D5]">
            <div class="flex justify-between items-center mb-6 border-b border-[#E5E1D5] pb-4">
              <h2 class="text-2xl font-semibold">Monthly Overview</h2>
              <div class="flex gap-4 text-sm text-[#3A3A3A]/70">
                <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-emerald-500"></span> Present</div>
                <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-amber-500"></span> Half Day</div>
                <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-red-500"></span> Absent</div>
              </div>
            </div>

            <div class="grid grid-cols-7 gap-2 text-center mb-2 text-xs font-semibold tracking-wider uppercase text-[#3A3A3A]/60">
              <div>Sun</div><div>Mon</div><div>Tue</div><div>Wed</div><div>Thu</div><div>Fri</div><div>Sat</div>
            </div>

            <div class="grid grid-cols-7 gap-2">
              <?php for ($i = 0; $i < $weeks * 7; $i++): ?>
                <?php
                  $dayNum = $i - $pad + 1;
                  if ($dayNum < 1 || $dayNum > $daysInMonth) {
                    echo '<div class="aspect-square rounded-lg bg-gray-50/50"></div>';
                    continue;
                  }
                  $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $dayNum);
                  $row = $byDate[$dateStr] ?? null;
                  $st = $row['status'] ?? null;
                  $bgClass = $st === 'present' ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20' : ($st === 'half_day' ? 'bg-amber-50 text-amber-700 ring-1 ring-amber-600/20' : ($st === 'absent' ? 'bg-red-50 text-red-700 ring-1 ring-red-600/20' : 'bg-white border border-[#E5E1D5] text-[#3A3A3A]'));
                ?>
                <div class="aspect-square rounded-lg flex flex-col items-center justify-center font-semibold <?= $bgClass ?>">
                  <?= (int)$dayNum ?>
                </div>
              <?php endfor; ?>
            </div>
          </div>

          <div class="bg-white rounded-lg p-6 shadow-[0px_40px_40px_rgba(58,58,58,0.04)] border border-[#E5E1D5]">
            <h2 class="text-2xl font-semibold mb-6 border-b border-[#E5E1D5] pb-4">Attendance History</h2>
            <div class="overflow-x-auto">
              <table class="w-full text-left border-collapse">
                <thead>
                  <tr class="border-b border-[#E5E1D5] text-xs font-semibold tracking-wider uppercase text-[#3A3A3A]/60">
                    <th class="py-3 px-4">Date</th>
                    <th class="py-3 px-4">Status</th>
                    <th class="py-3 px-4">First In</th>
                    <th class="py-3 px-4">Last Out</th>
                    <th class="py-3 px-4 text-right">Total Hours</th>
                  </tr>
                </thead>
                <tbody class="text-sm">
                  <?php foreach (array_reverse($rows) as $r): ?>
                    <?php
                      $st = $r['status'] ?? 'absent';
                      $badge = $st === 'present' ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20' : ($st === 'half_day' ? 'bg-amber-50 text-amber-700 ring-1 ring-amber-600/20' : 'bg-red-50 text-red-700 ring-1 ring-red-600/20');
                      $label = $st === 'present' ? 'Present' : ($st === 'half_day' ? 'Half Day' : 'Absent');
                    ?>
                    <tr class="border-b border-[#F1EDE3] hover:bg-[#F5F2EA] transition-colors last:border-0">
                      <td class="py-4 px-4 font-medium"><?= esc($r['date']) ?></td>
                      <td class="py-4 px-4"><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold <?= $badge ?>"><?= esc($label) ?></span></td>
                      <td class="py-4 px-4 text-[#3A3A3A]/80"><?= $r['first_in'] ? date('h:i A', strtotime($r['first_in'])) : '--' ?></td>
                      <td class="py-4 px-4 text-[#3A3A3A]/80"><?= $r['last_out'] ? date('h:i A', strtotime($r['last_out'])) : '--' ?></td>
                      <td class="py-4 px-4 text-right"><?= esc((string)($r['total_hours'] ?? '0')) ?></td>
                    </tr>
                  <?php endforeach; ?>
                  <?php if (empty($rows)): ?>
                    <tr><td class="py-6 px-4 text-[#3A3A3A]/60" colspan="5">No records for this month.</td></tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <div class="lg:col-span-4">
          <div class="bg-white rounded-lg p-6 shadow-[0px_40px_40px_rgba(58,58,58,0.04)] border border-[#E5E1D5] sticky top-24">
            <h3 class="text-lg font-semibold border-b border-[#E5E1D5] pb-4">Notes</h3>
            <ul class="text-sm text-[#3A3A3A]/70 mt-4 space-y-2 list-disc pl-4">
              <li>Present: Full working day completed.</li>
              <li>Half Day: Minimum 4 hours of working hours logged.</li>
              <li>Absent: No show or less than half day hours logged.</li>
              <li>Contact HR for any discrepancies in your attendance marking.</li>
            </ul>
          </div>
        </div>
      </div>
    </main>
  </div>
</body>
</html>

