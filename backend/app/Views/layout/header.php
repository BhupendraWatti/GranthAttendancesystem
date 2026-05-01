<?php
$username = session()->get('username') ?? 'Administrator';
$initials = strtoupper(substr($username, 0, 1));
?>
<header class="header">
    <div style="display: flex; align-items: center; gap: 1rem; flex: 1;">
        <button id="sidebar-toggle" style="background: none; border: none; font-size: 1.25rem; color: var(--color-text-dim); cursor: pointer; display: none;" class="md-block">
            <i class="fa-solid fa-bars-staggered"></i>
        </button>
        <div>
            <h1 style="font-size: 1.125rem; font-weight: 700; color: var(--color-primary);"><?= esc($pageTitle ?? 'Management Console') ?></h1>
        </div>
    </div>

    <div style="display: flex; align-items: center; gap: 2rem;">
        <div style="display: flex; align-items: center; gap: 0.5rem; color: var(--color-text-dim); font-size: 0.8125rem; font-weight: 600;">
            <i class="fa-solid fa-calendar-day"></i>
            <?= date('D, d M Y') ?>
        </div>
        
        <div style="height: 24px; width: 1px; background: var(--color-border);"></div>

        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <div style="text-align: right; line-height: 1.2;">
                <div style="font-size: 0.875rem; font-weight: 700; color: var(--color-primary);"><?= esc($username) ?></div>
                <div style="font-size: 0.625rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--color-success);">System Operator</div>
            </div>
            <div style="width: 36px; height: 36px; border-radius: 50%; background: var(--color-primary); color: white; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.875rem; box-shadow: var(--shadow-sm);">
                <?= esc($initials) ?>
            </div>
        </div>
    </div>
</header>
