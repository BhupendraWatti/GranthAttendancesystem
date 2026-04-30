<!DOCTYPE html>
<html class="light" lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>My Documents - GranthInfotech Attendance</title>
  <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Inter:wght@600&display=swap" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" rel="stylesheet"/>
  <style> body { background-color: #F5F2EA; } </style>
</head>
<body class="min-h-screen flex font-['Manrope'] text-[#3A3A3A]">
  <?= view('partials/employee_sidebar', ['activePage' => 'documents', 'showLogoutButton' => true]) ?>

  <div class="flex-1 md:ml-64 flex flex-col min-w-0">
    <?= view('partials/employee_topbar', [
      'headerShiftClass' => 'md:pl-64',
    ]) ?>

    <main class="flex-1 p-8 mt-16 overflow-y-auto max-w-[1440px] mx-auto w-full">
      <div class="mb-12 flex flex-col gap-3">
        <nav class="flex items-center gap-2 text-sm font-medium text-[#747878] mb-2">
          <a class="hover:text-[#EB5C49]" href="<?= site_url('/') ?>">Dashboard</a>
          <span class="material-symbols-outlined text-[16px]">chevron_right</span>
          <span class="text-[#3A3A3A]">My Documents</span>
        </nav>
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
          <h1 class="text-4xl font-extrabold tracking-tight">Documents</h1>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        
        <!-- Employee Specific Documents -->
        <div class="bg-white rounded-lg p-6 shadow-[0px_40px_40px_rgba(58,58,58,0.04)] border border-[#E5E1D5]">
          <div class="flex items-center gap-3 mb-6 border-b border-[#E5E1D5] pb-4">
            <span class="material-symbols-outlined text-[#EB5C49]">person</span>
            <h2 class="text-2xl font-semibold">Personal Documents</h2>
          </div>
          
          <div class="space-y-4">
            <?php if (!empty($employeeDocuments)): ?>
              <?php foreach ($employeeDocuments as $doc): ?>
                <div class="flex items-center justify-between p-4 rounded-lg bg-[#F5F2EA]/50 border border-[#E5E1D5] hover:border-[#EB5C49] transition-colors group">
                  <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-lg bg-white flex items-center justify-center text-[#EB5C49] shadow-sm">
                      <span class="material-symbols-outlined">description</span>
                    </div>
                    <div>
                      <div class="font-bold text-sm group-hover:text-[#EB5C49] transition-colors"><?= esc($doc['title']) ?></div>
                      <div class="text-[10px] text-[#3A3A3A]/50 font-bold uppercase tracking-widest mt-0.5">Uploaded: <?= date('d M Y', strtotime($doc['created_at'])) ?></div>
                    </div>
                  </div>
                  <a href="<?= site_url("documents/download/employee/{$doc['id']}") ?>" class="p-2 rounded-full hover:bg-[#EB5C49] hover:text-white text-[#3A3A3A]/40 transition-all">
                    <span class="material-symbols-outlined">download</span>
                  </a>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="py-12 text-center text-[#3A3A3A]/40">
                <span class="material-symbols-outlined text-4xl mb-2">folder_off</span>
                <p class="text-sm font-medium">No personal documents found.</p>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- Company Documents -->
        <div class="bg-white rounded-lg p-6 shadow-[0px_40px_40px_rgba(58,58,58,0.04)] border border-[#E5E1D5]">
          <div class="flex items-center gap-3 mb-6 border-b border-[#E5E1D5] pb-4">
            <span class="material-symbols-outlined text-[#EB5C49]">corporate_fare</span>
            <h2 class="text-2xl font-semibold">Company Documents</h2>
          </div>
          
          <div class="space-y-4">
            <?php if (!empty($companyDocuments)): ?>
              <?php foreach ($companyDocuments as $doc): ?>
                <div class="flex items-center justify-between p-4 rounded-lg bg-[#F5F2EA]/50 border border-[#E5E1D5] hover:border-[#EB5C49] transition-colors group">
                  <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-lg bg-white flex items-center justify-center text-[#EB5C49] shadow-sm">
                      <span class="material-symbols-outlined">policy</span>
                    </div>
                    <div>
                      <div class="font-bold text-sm group-hover:text-[#EB5C49] transition-colors"><?= esc($doc['title']) ?></div>
                      <div class="text-[10px] text-[#3A3A3A]/50 font-bold uppercase tracking-widest mt-0.5"><?= esc($doc['category'] ?? 'General') ?></div>
                    </div>
                  </div>
                  <a href="<?= site_url("documents/download/company/{$doc['id']}") ?>" class="p-2 rounded-full hover:bg-[#EB5C49] hover:text-white text-[#3A3A3A]/40 transition-all">
                    <span class="material-symbols-outlined">download</span>
                  </a>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="py-12 text-center text-[#3A3A3A]/40">
                <span class="material-symbols-outlined text-4xl mb-2">work_off</span>
                <p class="text-sm font-medium">No company documents available.</p>
              </div>
            <?php endif; ?>
          </div>
        </div>

      </div>
    </main>
  </div>
</body>
</html>
