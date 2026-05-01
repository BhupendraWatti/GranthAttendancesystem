<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="AttendPro — Attendance Management System">
    <title><?= esc($pageTitle ?? 'Admin Portal') ?> — Granth Infotech</title>
    
    <!-- Professional Typography -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Design System -->
    <link rel="stylesheet" href="/assets/css/premium.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Third Party -->
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
</head>

<body>
    <div class="app-layout">

        <!-- Sidebar -->
        <?= $this->include('layout/sidebar') ?>

        <!-- Main Wrapper -->
        <div class="main-wrapper">
            <!-- Header -->
            <?= $this->include('layout/header') ?>

            <!-- Flash Messages (Enterprise Alerts) -->
            <div style="padding: 0 2.5rem; margin-top: 2rem;">
                <?php if (session()->getFlashdata('success')): ?>
                    <div class="badge badge--success" style="width: 100%; justify-content: flex-start; padding: 1rem; border-radius: 8px;">
                        <i class="fa-solid fa-circle-check" style="margin-right: 0.75rem;"></i>
                        <span><?= esc(session()->getFlashdata('success')) ?></span>
                    </div>
                <?php endif; ?>

                <?php if (session()->getFlashdata('error')): ?>
                    <div class="badge badge--danger" style="width: 100%; justify-content: flex-start; padding: 1rem; border-radius: 8px;">
                        <i class="fa-solid fa-circle-xmark" style="margin-right: 0.75rem;"></i>
                        <span><?= esc(session()->getFlashdata('error')) ?></span>
                    </div>
                <?php endif; ?>
            </div>

            <div class="main-content">
                <!-- Page Content -->
                <?= $this->renderSection('content') ?>
            </div>

            <!-- Footer -->
            <?= $this->include('layout/footer') ?>
        </div>
    </div>

    <script>
        // Global Routing Configuration
        window.siteUrl = '<?= site_url() ?>';
        if (window.siteUrl.endsWith('/')) {
            window.siteUrl = window.siteUrl.slice(0, -1);
        }
    </script>
    <script src="/assets/js/app.js"></script>
    <script>
        // Professional Sidebar Toggle
        const toggleBtn = document.getElementById('sidebar-toggle');
        const sidebar = document.getElementById('sidebar');
        
        if (toggleBtn && sidebar) {
            toggleBtn.addEventListener('click', () => {
                sidebar.classList.toggle('collapsed');
            });
        }
    </script>
</body>

</html>