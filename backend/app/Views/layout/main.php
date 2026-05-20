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
    <link
        href="https://fonts.googleapis.com/css2?family=Manrope:wght@600;700;800&family=Inter:wght@400;500;600;700&display=swap"
        rel="stylesheet">

    <!-- Design System -->
    <link rel="stylesheet" href="<?= base_url('assets/css/premium.css') ?>?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Third Party -->
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>

    <style>
        /* Global Modal System */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(8px);
            z-index: 2000;
            display: none;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .modal-overlay.active {
            display: flex;
            opacity: 1;
        }

        .modal-container {
            background: white;
            border-radius: var(--radius-xl);
            width: 100%;
            max-width: 500px;
            box-shadow: var(--shadow-xl);
            transform: scale(0.95);
            transition: transform 0.3s ease;
            max-height: 90vh;
            display: flex;
            flex-direction: column;
        }

        .modal-overlay.active .modal-container {
            transform: scale(1);
        }

        .modal-header {
            padding: 1.5rem 2rem;
            border-bottom: 1px solid var(--color-border);
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            flex-shrink: 0;
        }

        .modal-body {
            padding: 2rem;
            overflow-y: auto;
            flex: 1;
        }

        .modal-close {
            width: 32px;
            height: 32px;
            border-radius: var(--radius-sm);
            border: none;
            background: var(--color-surface-muted);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--color-text-dim);
            transition: all 0.2s;
        }

        .modal-close:hover {
            background: var(--color-border);
            color: var(--color-primary);
        }
    </style>
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
                    <div class="badge badge--success"
                        style="width: 100%; justify-content: flex-start; padding: 1rem; border-radius: 8px;">
                        <i class="fa-solid fa-circle-check" style="margin-right: 0.75rem;"></i>
                        <span><?= esc(session()->getFlashdata('success')) ?></span>
                    </div>
                <?php endif; ?>

                <?php if (session()->getFlashdata('error')): ?>
                    <div class="badge badge--danger"
                        style="width: 100%; justify-content: flex-start; padding: 1rem; border-radius: 8px;">
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
        window.siteUrl = '<?= rtrim(site_url(), '/') ?>';
    </script>
    <script src="<?= base_url('assets/js/app.js') ?>?v=<?= time() ?>"></script>
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