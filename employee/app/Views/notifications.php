<!DOCTYPE html>
<html class="light" lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Notifications - GranthInfotech Attendance</title>
  <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Inter:wght@600&display=swap" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" rel="stylesheet"/>
  <style> body { background-color: #F5F2EA; } </style>
</head>
<body class="min-h-screen flex font-['Manrope'] text-[#3A3A3A]">
  <?= view('partials/employee_sidebar', ['activePage' => 'notifications', 'showLogoutButton' => true]) ?>

  <div class="flex-1 md:ml-64 flex flex-col min-w-0">
    <?= view('partials/employee_topbar', [
      'headerShiftClass' => 'md:pl-64',
    ]) ?>

    <main class="flex-1 p-8 mt-16 overflow-y-auto max-w-[1440px] mx-auto w-full">
      <div class="mb-12 flex flex-col gap-3">
        <nav class="flex items-center gap-2 text-sm font-medium text-[#747878] mb-2">
          <a class="hover:text-[#EB5C49]" href="<?= site_url('/') ?>">Dashboard</a>
          <span class="material-symbols-outlined text-[16px]">chevron_right</span>
          <span class="text-[#3A3A3A]">Notifications</span>
        </nav>
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
          <h1 class="text-4xl font-extrabold tracking-tight">Notifications</h1>
          <button id="mark-all-read" class="px-4 py-2 rounded-lg bg-[#EB5C49] text-white font-semibold text-sm hover:bg-[#d54e3d] transition-colors">Mark all as read</button>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow-[0px_40px_40px_rgba(58,58,58,0.04)] border border-[#E5E1D5] overflow-hidden">
        <div class="divide-y divide-[#F1EDE3]">
          <?php if (!empty($notifications)): ?>
            <?php foreach ($notifications as $n): ?>
              <div class="p-6 hover:bg-[#F5F2EA]/30 transition-colors <?= $n['is_read'] ? 'opacity-60' : '' ?>">
                <div class="flex gap-4">
                  <div class="w-10 h-10 rounded-full bg-[#F5F2EA] flex items-center justify-center text-[#EB5C49] shrink-0">
                    <span class="material-symbols-outlined"><?= $n['type'] === 'leave' ? 'event_note' : 'notifications' ?></span>
                  </div>
                  <div class="flex-1">
                    <div class="flex justify-between items-start mb-1">
                      <h3 class="font-bold text-[#3A3A3A]"><?= esc($n['title']) ?></h3>
                      <span class="text-[10px] font-bold uppercase tracking-widest text-[#3A3A3A]/40"><?= date('d M Y, h:i A', strtotime($n['created_at'])) ?></span>
                    </div>
                    <p class="text-sm text-[#3A3A3A]/70 leading-relaxed"><?= esc($n['message']) ?></p>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="py-20 text-center">
              <span class="material-symbols-outlined text-5xl text-[#3A3A3A]/20 mb-4">notifications_off</span>
              <p class="text-[#3A3A3A]/60 font-medium">No notifications yet.</p>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </main>
  </div>

  <script>
    document.getElementById('mark-all-read')?.addEventListener('click', async function() {
      try {
        const response = await fetch('<?= site_url('notifications/mark-read') ?>', {
          method: 'POST',
          headers: {
            'X-Requested-With': 'XMLHttpRequest'
          }
        });
        if (response.ok) {
          location.reload();
        }
      } catch (error) {
        console.error('Error marking notifications as read:', error);
      }
    });
  </script>
</body>
</html>
