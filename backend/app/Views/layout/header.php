<?php
/**
 * Header / Top Bar
 * 
 * Shows page title, breadcrumb, user info and mobile hamburger toggle.
 */
$username = session()->get('username') ?? 'Admin';
$initials = strtoupper(substr($username, 0, 2));
?>
<header class="header">
    <div class="header-left flex items-center">
        <button class="btn-sidebar-toggle block md:hidden mr-4" id="sidebar-toggle" style="background: none; border: none; font-size: 1.5rem; cursor: pointer;">☰</button>
        <div>
            <h1><?= esc($pageTitle ?? 'Granth Infotech Dashboard') ?></h1>
        </div>
    </div>

    <div class="header-right">
        <div class="date-display">
            📅 <?= date('d M Y') ?>
        </div>
        <div class="header-user">
            <div class="user-avatar"><?= esc($initials) ?></div>
            <span><?= esc($username) ?></span>
        </div>
    </div>
</header>
