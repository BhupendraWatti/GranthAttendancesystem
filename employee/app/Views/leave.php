<!DOCTYPE html>
<html class="light" lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Leave Management - GranthInfotech Attendance</title>
  <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Inter:wght@600&display=swap" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" rel="stylesheet"/>
  <style> body { background-color: #F5F2EA; } </style>
</head>
<body class="min-h-screen flex font-['Manrope'] text-[#3A3A3A]">
  <?= view('partials/employee_sidebar', ['activePage' => 'leave', 'showLogoutButton' => true]) ?>

  <div class="flex-1 md:ml-64 flex flex-col min-w-0">
    <?= view('partials/employee_topbar', [
      'headerShiftClass' => 'md:pl-64',
    ]) ?>

    <main class="flex-1 p-8 mt-16 overflow-y-auto max-w-[1440px] mx-auto w-full">
      <div class="mb-12 flex flex-col gap-3">
        <nav class="flex items-center gap-2 text-sm font-medium text-[#747878] mb-2">
          <a class="hover:text-[#EB5C49]" href="<?= site_url('/') ?>">Dashboard</a>
          <span class="material-symbols-outlined text-[16px]">chevron_right</span>
          <span class="text-[#3A3A3A]">Leave & Holidays</span>
        </nav>
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
          <h1 class="text-4xl font-extrabold tracking-tight">Leave Management</h1>
        </div>
      </div>

      <?php if (session()->getFlashdata('success')): ?>
        <div class="mb-6 p-4 rounded-lg bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20">
          <?= esc(session()->getFlashdata('success')) ?>
        </div>
      <?php endif; ?>

      <?php if (session()->getFlashdata('error')): ?>
        <div class="mb-6 p-4 rounded-lg bg-red-50 text-red-700 ring-1 ring-red-600/20">
          <?= esc(session()->getFlashdata('error')) ?>
        </div>
      <?php endif; ?>

      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Leave Balance & Holidays -->
        <div class="lg:col-span-4 flex flex-col gap-6">
          <!-- Leave Balance -->
          <div class="bg-white rounded-lg p-6 shadow-[0px_40px_40px_rgba(58,58,58,0.04)] border border-[#E5E1D5]">
            <h2 class="text-xl font-bold mb-6 border-b border-[#E5E1D5] pb-4">Leave Balance</h2>
            <div class="space-y-6">
              <?php if (!empty($balances)): ?>
                <?php foreach ($balances as $bal): ?>
                  <div>
                    <div class="flex justify-between items-center mb-2">
                      <span class="font-semibold text-sm uppercase tracking-wider text-[#3A3A3A]/70"><?= esc(ucwords(str_replace('_', ' ', $bal['leave_type']))) ?></span>
                      <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-[#F5F2EA] text-[#EB5C49]"><?= esc($bal['remaining']) ?> days left</span>
                    </div>
                    <div class="h-2 w-full bg-[#F5F2EA] rounded-full overflow-hidden">
                      <?php $percent = ($bal['total'] > 0) ? round(($bal['used'] / $bal['total']) * 100) : 0; ?>
                      <div class="h-full bg-[#EB5C49]" style="width: <?= $percent ?>%"></div>
                    </div>
                    <div class="flex justify-between mt-1 text-[10px] text-[#3A3A3A]/50 font-bold uppercase tracking-tighter">
                      <span>Used: <?= esc($bal['used']) ?></span>
                      <span>Total: <?= esc($bal['total']) ?></span>
                    </div>
                  </div>
                <?php endforeach; ?>
              <?php else: ?>
                <p class="text-sm text-[#3A3A3A]/60 italic">No leave balances found.</p>
              <?php endif; ?>
            </div>
          </div>

          <!-- Upcoming Holidays -->
          <div class="bg-white rounded-lg p-6 shadow-[0px_40px_40px_rgba(58,58,58,0.04)] border border-[#E5E1D5]">
            <h2 class="text-xl font-bold mb-6 border-b border-[#E5E1D5] pb-4">Upcoming Holidays</h2>
            <div class="space-y-4">
              <?php if (!empty($holidays)): ?>
                <?php foreach (array_slice($holidays, 0, 5) as $h): ?>
                  <div class="flex justify-between items-center group">
                    <div>
                      <div class="font-bold text-[#3A3A3A] group-hover:text-[#EB5C49] transition-colors"><?= esc($h['title']) ?></div>
                      <div class="text-xs text-[#3A3A3A]/60"><?= date('D, d M Y', strtotime($h['date'])) ?></div>
                    </div>
                    <span class="text-[10px] font-extrabold uppercase tracking-widest px-2 py-1 rounded border border-[#E5E1D5] text-[#3A3A3A]/40"><?= esc($h['type']) ?></span>
                  </div>
                <?php endforeach; ?>
              <?php else: ?>
                <p class="text-sm text-[#3A3A3A]/60 italic text-center">No upcoming holidays</p>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <!-- Apply Leave & History -->
        <div class="lg:col-span-8 flex flex-col gap-6">
          <!-- Apply Leave Form -->
          <div class="bg-white rounded-lg p-6 shadow-[0px_40px_40px_rgba(58,58,58,0.04)] border border-[#E5E1D5]">
            <h2 class="text-xl font-bold mb-6 border-b border-[#E5E1D5] pb-4">Apply for Leave</h2>
            <form action="<?= site_url('leave/apply') ?>" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="col-span-full">
                <label class="block text-xs font-bold uppercase tracking-widest text-[#3A3A3A]/60 mb-2">Leave Type</label>
                <select name="leave_type" class="w-full rounded-lg border border-[#E5E1D5] bg-[#F5F2EA] px-4 py-3 text-sm focus:ring-2 focus:ring-[#EB5C49] focus:outline-none" required>
                  <option value="casual_leave">Casual Leave</option>
                  <option value="sick_leave">Sick Leave</option>
                  <option value="earned_leave">Earned Leave</option>
                </select>
              </div>

              <div>
                <label class="block text-xs font-bold uppercase tracking-widest text-[#3A3A3A]/60 mb-2">From Date</label>
                <input type="date" name="from_date" class="w-full rounded-lg border border-[#E5E1D5] bg-[#F5F2EA] px-4 py-3 text-sm focus:ring-2 focus:ring-[#EB5C49] focus:outline-none" required min="<?= date('Y-m-d') ?>">
              </div>

              <div>
                <label class="block text-xs font-bold uppercase tracking-widest text-[#3A3A3A]/60 mb-2">Session</label>
                <select name="from_session" class="w-full rounded-lg border border-[#E5E1D5] bg-[#F5F2EA] px-4 py-3 text-sm focus:ring-2 focus:ring-[#EB5C49] focus:outline-none">
                  <option value="full">Full Day</option>
                  <option value="half_morning">Half Day (AM)</option>
                  <option value="half_afternoon">Half Day (PM)</option>
                </select>
              </div>

              <div>
                <label class="block text-xs font-bold uppercase tracking-widest text-[#3A3A3A]/60 mb-2">To Date</label>
                <input type="date" name="to_date" class="w-full rounded-lg border border-[#E5E1D5] bg-[#F5F2EA] px-4 py-3 text-sm focus:ring-2 focus:ring-[#EB5C49] focus:outline-none" required min="<?= date('Y-m-d') ?>">
              </div>

              <div>
                <label class="block text-xs font-bold uppercase tracking-widest text-[#3A3A3A]/60 mb-2">Session</label>
                <select name="to_session" class="w-full rounded-lg border border-[#E5E1D5] bg-[#F5F2EA] px-4 py-3 text-sm focus:ring-2 focus:ring-[#EB5C49] focus:outline-none">
                  <option value="full">Full Day</option>
                  <option value="half_morning">Half Day (AM)</option>
                  <option value="half_afternoon">Half Day (PM)</option>
                </select>
              </div>

              <div class="col-span-full">
                <label class="block text-xs font-bold uppercase tracking-widest text-[#3A3A3A]/60 mb-2">Reason</label>
                <textarea name="reason" rows="3" class="w-full rounded-lg border border-[#E5E1D5] bg-[#F5F2EA] px-4 py-3 text-sm focus:ring-2 focus:ring-[#EB5C49] focus:outline-none" required placeholder="Why are you taking leave?"></textarea>
              </div>

              <div class="col-span-full flex justify-end">
                <button type="submit" class="px-8 py-3 rounded-lg bg-[#EB5C49] text-white font-bold text-sm hover:bg-[#d54e3d] transition-colors shadow-lg shadow-[#EB5C49]/20">Submit Request</button>
              </div>
            </form>
          </div>

          <!-- Leave History -->
          <div class="bg-white rounded-lg p-6 shadow-[0px_40px_40px_rgba(58,58,58,0.04)] border border-[#E5E1D5]">
            <h2 class="text-xl font-bold mb-6 border-b border-[#E5E1D5] pb-4">Leave History</h2>
            <div class="overflow-x-auto">
              <table class="w-full text-left border-collapse">
                <thead>
                  <tr class="border-b border-[#E5E1D5] text-xs font-semibold tracking-wider uppercase text-[#3A3A3A]/60">
                    <th class="py-3 px-4">Dates</th>
                    <th class="py-3 px-4">Type</th>
                    <th class="py-3 px-4 text-center">Status</th>
                    <th class="py-3 px-4">Reason</th>
                  </tr>
                </thead>
                <tbody class="text-sm">
                  <?php if (!empty($history)): ?>
                    <?php foreach ($history as $req): ?>
                      <?php
                        $st = $req['status'] ?? 'pending';
                        $badge = $st === 'approved' ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20' : ($st === 'rejected' ? 'bg-red-50 text-red-700 ring-1 ring-red-600/20' : 'bg-amber-50 text-amber-700 ring-1 ring-amber-600/20');
                      ?>
                      <tr class="border-b border-[#F1EDE3] hover:bg-[#F5F2EA] transition-colors last:border-0">
                        <td class="py-4 px-4">
                          <div class="font-bold"><?= date('d M', strtotime($req['from_date'])) ?> - <?= date('d M Y', strtotime($req['to_date'])) ?></div>
                          <div class="text-[10px] text-[#3A3A3A]/50 font-bold uppercase tracking-tight"><?= ($req['from_session'] !== 'full' || $req['to_session'] !== 'full') ? 'Half Day' : 'Full Days' ?></div>
                        </td>
                        <td class="py-4 px-4 font-medium"><?= esc(ucwords(str_replace('_', ' ', $req['leave_type']))) ?></td>
                        <td class="py-4 px-4 text-center"><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-widest <?= $badge ?>"><?= esc($st) ?></span></td>
                        <td class="py-4 px-4 text-[#3A3A3A]/80">
                          <div class="max-w-[200px] truncate" title="<?= esc($req['reason']) ?>"><?= esc($req['reason']) ?></div>
                          <?php if (!empty($req['admin_comment'])): ?>
                            <div class="text-[10px] text-red-500 font-bold mt-1">Note: <?= esc($req['admin_comment']) ?></div>
                          <?php endif; ?>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <tr><td class="py-8 px-4 text-center text-[#3A3A3A]/60 italic" colspan="4">No leave history found.</td></tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</body>
</html>
