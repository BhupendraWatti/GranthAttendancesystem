<?php
/**
 * Sidebar Navigation
 * Renders the left-side navigation panel.
 * 
 * Uses $activePage variable (set by controllers) to highlight current page.
 */
$activePage = $activePage ?? '';
?>
<aside class="sidebar" id="sidebar">
    <!-- Brand -->
    <div class="sidebar-brand">
        <div class="brand-icon"><img src="<?= base_url('assets/img/image-1776985023902.png') ?>" alt="Logo" style="max-width: 32px;"
                onerror="this.onerror=null; this.parentNode.innerText='G';"></div>
        <div>
            <h2 class="text-xl">Granth Infotech</h2>
            <small>System</small>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="sidebar-nav">
        <div class="nav-label">Main</div>

        <a href="<?= site_url('dashboard') ?>" class="<?= $activePage === 'dashboard' ? 'active' : '' ?>">
            <span class="nav-icon">📊</span>
            Dashboard
        </a>

        <a href="<?= site_url('employees') ?>" class="<?= $activePage === 'employees' ? 'active' : '' ?>">
            <span class="nav-icon">👥</span>
            Employees
        </a>

        <a href="<?= site_url('salary') ?>" class="<?= $activePage === 'salary' ? 'active' : '' ?>">
            <span class="nav-icon">💰</span>
            Salary
        </a>

        <div class="nav-label">Management</div>

        <a href="<?= site_url('documents/employee') ?>" class="<?= $activePage === 'documents' && service('router')->methodName() === 'employee' ? 'active' : '' ?>">
            <span class="nav-icon">📄</span>
            Employee Docs
        </a>

        <a href="<?= site_url('documents/company') ?>" class="<?= $activePage === 'documents' && service('router')->methodName() === 'company' ? 'active' : '' ?>">
            <span class="nav-icon">🏢</span>
            Company Docs
        </a>

        <a href="<?= site_url('leave') ?>" class="<?= $activePage === 'leave' ? 'active' : '' ?>">
            <span class="nav-icon">⏳</span>
            Leave Requests
        </a>

        <a href="<?= site_url('holidays') ?>" class="<?= $activePage === 'holidays' ? 'active' : '' ?>">
            <span class="nav-icon">📅</span>
            Holidays
        </a>

        <div class="nav-label">System</div>

        <a href="<?= site_url('sync') ?>" class="<?= $activePage === 'sync' ? 'active' : '' ?>">
            <span class="nav-icon">🔄</span>
            Sync Data
        </a>
    </nav>

    <!-- Footer -->
    <div class="sidebar-footer">
        <a href="<?= site_url('logout') ?>">
            <span>🚪</span>
            Sign Out
        </a>
    </div>
</aside>