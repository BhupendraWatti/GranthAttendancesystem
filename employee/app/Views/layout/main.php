<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($pageTitle ?? 'Granth Infotech') ?> — Portal</title>
    
    <!-- Premium Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Manrope:wght@200..800&display=swap" rel="stylesheet">
    
    <!-- Material Symbols -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" rel="stylesheet" />
    
    <!-- Premium Stylesheet -->
    <link rel="stylesheet" href="<?= base_url('assets/css/premium.css') ?>">
    
    <style>
        /* Contextual Helpers */
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: var(--radius-sm);
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .badge--present { background: rgba(16, 185, 129, 0.1); color: #10B981; }
        .badge--absent { background: rgba(239, 68, 68, 0.1); color: #EF4444; }
        .badge--half_day { background: rgba(245, 158, 11, 0.1); color: #F59E0B; }
    </style>
</head>
<body>
    <div class="app-layout">
        <!-- Sidebar Navigation -->
        <aside class="sidebar">
            <div class="sidebar-brand">
                <h2 class="font-display">Granth.</h2>
                <div style="font-size: 0.6rem; text-transform: uppercase; letter-spacing: 0.2em; color: var(--color-text-dim); margin-top: 0.5rem;">Personnel System</div>
            </div>

            <nav class="sidebar-nav">
                <?php $activePage = $activePage ?? ''; ?>
                <a href="<?= site_url('/') ?>" class="<?= $activePage === 'dashboard' ? 'active' : '' ?>">
                    <span class="nav-icon material-symbols-outlined">dashboard</span>
                    Dashboard
                </a>
                <a href="<?= site_url('attendance') ?>" class="<?= $activePage === 'attendance' ? 'active' : '' ?>">
                    <span class="nav-icon material-symbols-outlined">event_available</span>
                    Attendance
                </a>
                <a href="<?= site_url('leave') ?>" class="<?= $activePage === 'leave' ? 'active' : '' ?>">
                    <span class="nav-icon material-symbols-outlined">calendar_month</span>
                    Leaves
                </a>
                <a href="<?= site_url('salary') ?>" class="<?= $activePage === 'salary' ? 'active' : '' ?>">
                    <span class="nav-icon material-symbols-outlined">payments</span>
                    Payroll
                </a>
                <a href="<?= site_url('documents') ?>" class="<?= $activePage === 'documents' ? 'active' : '' ?>">
                    <span class="nav-icon material-symbols-outlined">folder_open</span>
                    Documents
                </a>
                <a href="<?= site_url('profile') ?>" class="<?= $activePage === 'profile' ? 'active' : '' ?>">
                    <span class="nav-icon material-symbols-outlined">person</span>
                    Profile
                </a>
            </nav>

            <div style="margin-top: auto;">
                <a href="<?= site_url('auth/logout') ?>" class="sidebar-nav" style="text-decoration: none; color: var(--color-text-dim); font-size: 0.9rem; display: flex; align-items: center; gap: 1rem; padding: 1rem;">
                    <span class="material-symbols-outlined">logout</span>
                    Sign Out
                </a>
            </div>
        </aside>

        <!-- Main Wrapper -->
        <main class="main-wrapper">
            <!-- Flash Messages -->
            <?php if (session()->getFlashdata('success')): ?>
                <div style="background: rgba(16, 185, 129, 0.1); color: #10B981; padding: 1rem 2rem; border-radius: var(--radius-md); margin-bottom: 2rem; border: 1px solid rgba(16, 185, 129, 0.2);">
                    <?= esc(session()->getFlashdata('success')) ?>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('error')): ?>
                <div style="background: rgba(239, 68, 68, 0.1); color: #EF4444; padding: 1rem 2rem; border-radius: var(--radius-md); margin-bottom: 2rem; border: 1px solid rgba(239, 68, 68, 0.2);">
                    <?= esc(session()->getFlashdata('error')) ?>
                </div>
            <?php endif; ?>

            <!-- Page Content -->
            <?= $this->renderSection('content') ?>
            
            <footer style="margin-top: 6rem; padding-top: 2rem; border-top: 1px solid var(--color-border); color: var(--color-text-dim); font-size: 0.8rem; text-align: center;">
                &copy; <?= date('Y') ?> Granth Infotech. All rights reserved.
            </footer>
        </main>
    </div>
</body>
</html>
