<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="AttendPro — Attendance Management System">
    <title><?= esc($pageTitle ?? 'Granth Infotech System') ?> — Granth Infotech</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            corePlugins: {
                preflight: false,
            }
        }
    </script>
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
</head>

<body>
    <div class="app-layout">

        <!-- Sidebar Overlay (Mobile) -->
        <div class="sidebar-overlay" id="sidebar-overlay"></div>

        <!-- Sidebar -->
        <?= $this->include('layout/sidebar') ?>

        <!-- Main Wrapper -->
        <div class="main-wrapper">
            <!-- Header -->
            <?= $this->include('layout/header') ?>

            <!-- Flash Messages -->
            <div class="main-content">
                <?php if (session()->getFlashdata('success')): ?>
                    <div class="alert alert--success">
                        ✅ <?= esc(session()->getFlashdata('success')) ?>
                        <button class="alert-close">&times;</button>
                    </div>
                <?php endif; ?>

                <?php if (session()->getFlashdata('error')): ?>
                    <div class="alert alert--danger">
                        ❌ <?= esc(session()->getFlashdata('error')) ?>
                        <button class="alert-close">&times;</button>
                    </div>
                <?php endif; ?>

                <?php if (session()->getFlashdata('warning')): ?>
                    <div class="alert alert--warning">
                        ⚠️ <?= esc(session()->getFlashdata('warning')) ?>
                        <button class="alert-close">&times;</button>
                    </div>
                <?php endif; ?>

                <?php if (session()->getFlashdata('info')): ?>
                    <div class="alert alert--info">
                        ℹ️ <?= esc(session()->getFlashdata('info')) ?>
                        <button class="alert-close">&times;</button>
                    </div>
                <?php endif; ?>

                <!-- Page Content -->
                <?= $this->renderSection('content') ?>
            </div>

            <!-- Footer -->
            <?= $this->include('layout/footer') ?>
        </div>
    </div>

    <script src="<?= base_url('assets/js/app.js') ?>"></script>
</body>

</html>