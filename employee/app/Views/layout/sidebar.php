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
        <div class="brand-icon"><img src="/assets/img/image-1776985023902.png" alt="Logo" style="max-width: 32px;"
                onerror="this.onerror=null; this.parentNode.innerText='G';"></div>
        <div>
            <h2 class="text-xl">Granth Infotech</h2>
            <small>System</small>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="sidebar-nav">
        <div class="nav-label">Main</div>

        <a href="<?= site_url('/') ?>" class="<?= $activePage === 'dashboard' ? 'active' : '' ?>">
            <span class="nav-icon">📊</span>
            Dashboard
        </a>

        <a href="<?= site_url('attendance') ?>" class="<?= $activePage === 'attendance' ? 'active' : '' ?>">
            <span class="nav-icon">📋</span>
            My Attendance
        </a>

        <a href="<?= site_url('salary') ?>" class="<?= $activePage === 'salary' ? 'active' : '' ?>">
            <span class="nav-icon">💰</span>
            My Salary
        </a>

        <a href="<?= site_url('leave') ?>" class="<?= $activePage === 'leave' ? 'active' : '' ?>">
            <span class="nav-icon">📅</span>
            Leave & Holidays
        </a>

        <div class="nav-label">Account</div>

        <a href="<?= site_url('notifications') ?>" class="<?= $activePage === 'notifications' ? 'active' : '' ?>">
            <span class="nav-icon">🔔</span>
            Notifications
        </a>

        <a href="<?= site_url('profile') ?>" class="<?= $activePage === 'profile' ? 'active' : '' ?>">
            <span class="nav-icon">👤</span>
            My Profile
        </a>
    </nav>

    <!-- Footer -->
    <div class="sidebar-footer">
        <a href="/logout">
            <span>🚪</span>
            Sign Out
        </a>
    </div>
</aside>