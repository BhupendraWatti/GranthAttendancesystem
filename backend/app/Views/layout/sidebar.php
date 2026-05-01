<?php
$activePage = $activePage ?? '';
$methodName = service('router')->methodName();
?>
<aside class="sidebar" id="sidebar">
    <!-- Brand -->
    <div class="sidebar-brand">
        <div class="logo-icon">G</div>
        <div>
            <h2>Granth.</h2>
            <div
                style="font-size: 0.625rem; text-transform: uppercase; letter-spacing: 0.1em; color: rgba(255,255,255,0.4); font-weight: 700; margin-top: 2px;">
                Admin Interface</div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="sidebar-nav" style="padding: 1rem 0; flex: 1;">
        <div
            style="padding: 0 1.5rem 0.5rem; font-size: 0.65rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.1em; color: rgba(255,255,255,0.3);">
            Primary</div>

        <a href="<?= site_url('dashboard') ?>" class="<?= $activePage === 'dashboard' ? 'active' : '' ?>">
            <i class="fa-solid fa-chart-pie nav-icon"></i>
            Operational Insights
        </a>

        <a href="<?= site_url('employees') ?>" class="<?= $activePage === 'employees' ? 'active' : '' ?>">
            <i class="fa-solid fa-user-group nav-icon"></i>
            Workforce Registry
        </a>

        <a href="<?= site_url('salary') ?>" class="<?= $activePage === 'salary' ? 'active' : '' ?>">
            <i class="fa-solid fa-wallet nav-icon"></i>
            Payroll Records
        </a>

        <div
            style="padding: 1.5rem 1.5rem 0.5rem; font-size: 0.65rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.1em; color: rgba(255,255,255,0.3);">
            Assets & Workflow</div>

        <a href="<?= site_url('documents/employee') ?>"
            class="<?= $activePage === 'documents' && $methodName === 'employee' ? 'active' : '' ?>">
            <i class="fa-solid fa-file-invoice nav-icon"></i>
            personal Files
        </a>

        <a href="<?= site_url('documents/company') ?>"
            class="<?= $activePage === 'documents' && $methodName === 'company' ? 'active' : '' ?>">
            <i class="fa-solid fa-building-shield nav-icon"></i>
            Corporate Policies
        </a>

        <a href="<?= site_url('leave') ?>" class="<?= $activePage === 'leave' ? 'active' : '' ?>">
            <i class="fa-solid fa-calendar-day nav-icon"></i>
            Absence Requests
        </a>

        <a href="<?= site_url('holidays') ?>" class="<?= $activePage === 'holidays' ? 'active' : '' ?>">
            <i class="fa-solid fa-calendar-check nav-icon"></i>
            Event Calendar
        </a>

        <div
            style="padding: 1.5rem 1.5rem 0.5rem; font-size: 0.65rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.1em; color: rgba(255,255,255,0.3);">
            System</div>

        <a href="<?= site_url('sync') ?>" class="<?= $activePage === 'sync' ? 'active' : '' ?>">
            <i class="fa-solid fa-rotate nav-icon"></i>
            Data Synchronization
        </a>
    </nav>

    <!-- Footer -->
    <div style="padding: 1.5rem; border-top: 1px solid rgba(255,255,255,0.05);">
        <a href="<?= site_url('logout') ?>"
            style="display: flex; align-items: center; gap: 0.75rem; color: rgba(255,255,255,0.5); text-decoration: none; font-size: 0.8125rem; font-weight: 600; transition: color 0.2s;">
            <i class="fa-solid fa-arrow-right-from-bracket"></i>
            Terminate Session
        </a>
    </div>
</aside>