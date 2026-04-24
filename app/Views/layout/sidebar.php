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
        <div class="brand-icon"><img src="/assets/img/image-1776985023902.png" alt="Logo" style="max-width: 32px;" onerror="this.onerror=null; this.parentNode.innerText='G';"></div>
        <div>
            <h2 class="text-xl">Granth Infotech</h2>
            <small>System</small>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="sidebar-nav">
        <div class="nav-label">Main</div>

        <a href="/dashboard" class="<?= $activePage === 'dashboard' ? 'active' : '' ?>">
            <span class="nav-icon">📊</span>
            Dashboard
        </a>

        <a href="/employees" class="<?= $activePage === 'employees' ? 'active' : '' ?>">
            <span class="nav-icon">👥</span>
            Employees
        </a>

        <a href="/salary" class="<?= $activePage === 'salary' ? 'active' : '' ?>">
            <span class="nav-icon">💰</span>
            Salary
        </a>

        <div class="nav-label">System</div>

        <a href="/sync" class="<?= $activePage === 'sync' ? 'active' : '' ?>">
            <span class="nav-icon">🔄</span>
            Sync Data
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
