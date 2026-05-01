<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<?php
  $name = $employee['name'] ?? 'Associate';
  $status = $todayRow['status'] ?? 'absent';
?>

<div class="page-header">
    <p class="text-muted" style="text-transform: uppercase; letter-spacing: 0.1em; font-size: 0.75rem; font-weight: 600; margin-bottom: 0.5rem;">Operational Overview</p>
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h2 class="font-display">Welcome, <?= esc($name) ?></h2>
        <div style="display: flex; align-items: center; gap: 0.5rem; background: var(--color-surface); padding: 0.5rem 1rem; border-radius: var(--radius-sm); border: 1px solid var(--color-border); box-shadow: var(--shadow-sm);">
            <div style="width: 8px; height: 8px; border-radius: 50%; background: var(--color-success); animation: pulse 2s infinite;"></div>
            <span style="font-size: 0.8125rem; font-weight: 600;"><?= date('l, d M Y') ?></span>
        </div>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <span class="stat-label">Hours Logged Today</span>
        <span class="stat-value"><?= esc($todayHours ?? '0h 00m') ?></span>
        <div style="margin-top: 1rem; height: 4px; background: var(--color-surface-muted); border-radius: 2px;">
            <?php 
                $h = (float)($todayRow['total_hours'] ?? 0);
                $progress = min(($h / 8) * 100, 100);
            ?>
            <div style="height: 100%; width: <?= $progress ?>%; background: var(--color-accent); border-radius: 2px;"></div>
        </div>
        <span class="stat-sub"><?= round($progress) ?>% of 8h daily target</span>
    </div>
    
    <div class="stat-card">
        <span class="stat-label">Monthly Presence</span>
        <div style="display: flex; align-items: baseline; gap: 0.5rem;">
            <span class="stat-value"><?= (int)($counts['present'] ?? 0) ?></span>
            <span class="text-muted" style="font-size: 0.875rem;">/ <?= (int)(($counts['present'] ?? 0) + ($counts['absent'] ?? 0) + ($counts['half_day'] ?? 0)) ?> days active</span>
        </div>
        <span class="stat-sub" style="color: var(--color-warning);"><?= (int)($counts['half_day'] ?? 0) ?> half-days recorded</span>
    </div>

    <div class="stat-card">
        <span class="stat-label">Registry Profile</span>
        <span class="stat-value" style="font-size: 1.125rem; letter-spacing: 0.02em;"><?= esc($employee['emp_code'] ?? 'N/A') ?></span>
        <span class="text-muted" style="font-size: 0.8125rem; margin-top: 0.25rem;"><?= esc($employee['designation'] ?? 'Personnel Associate') ?></span>
        <span class="stat-sub" style="color: var(--color-accent);"><?= esc($employee['department'] ?? 'General Ops') ?></span>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3>Recent Activity Log</h3>
        <a href="<?= site_url('attendance') ?>" class="btn btn-outline" style="padding: 0.375rem 0.75rem; font-size: 0.75rem;">View Archive</a>
    </div>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Service Date</th>
                    <th>Status</th>
                    <th>Check In</th>
                    <th>Check Out</th>
                    <th>Total Active</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent as $row): ?>
                    <?php $st = $row['status'] ?? 'absent'; ?>
                    <tr>
                        <td style="font-weight: 500;"><?= date('D, d M Y', strtotime($row['date'])) ?></td>
                        <td><span class="badge badge--<?= esc($st) ?>"><?= esc(ucfirst(str_replace('_', ' ', $st))) ?></span></td>
                        <td><?= $row['first_in'] ? date('H:i', strtotime($row['first_in'])) : '—' ?></td>
                        <td><?= $row['last_out'] ? date('H:i', strtotime($row['last_out'])) : '—' ?></td>
                        <td style="font-weight: 600; color: var(--color-primary);"><?= esc($row['total_hours'] ?? '0') ?>h</td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($recent)): ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 3rem; color: var(--color-text-dim);">No recent activity discovered in the registry.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
    @keyframes pulse {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
        70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
    }
</style>

<?= $this->endSection() ?>
