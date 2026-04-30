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
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
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

            <!-- Flash Messages (Toasts) -->
            <div class="alert-container">
                <?php if (session()->getFlashdata('success')): ?>
                    <div class="alert alert--success">
                        <i class="fa-solid fa-circle-check"></i>
                        <span><?= esc(session()->getFlashdata('success')) ?></span>
                        <button class="alert-close">&times;</button>
                    </div>
                <?php endif; ?>

                <?php if (session()->getFlashdata('error')): ?>
                    <div class="alert alert--danger">
                        <i class="fa-solid fa-circle-xmark"></i>
                        <span><?= esc(session()->getFlashdata('error')) ?></span>
                        <button class="alert-close">&times;</button>
                    </div>
                <?php endif; ?>

                <?php if (session()->getFlashdata('warning')): ?>
                    <div class="alert alert--warning">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        <span><?= esc(session()->getFlashdata('warning')) ?></span>
                        <button class="alert-close">&times;</button>
                    </div>
                <?php endif; ?>

                <?php if (session()->getFlashdata('info')): ?>
                    <div class="alert alert--info">
                        <i class="fa-solid fa-circle-info"></i>
                        <span><?= esc(session()->getFlashdata('info')) ?></span>
                        <button class="alert-close">&times;</button>
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

    <script src="<?= base_url('assets/js/app.js') ?>"></script>
</body>

</html>