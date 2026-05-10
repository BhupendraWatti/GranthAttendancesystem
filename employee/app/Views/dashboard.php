<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<?php
$name = $employee['name'] ?? 'Associate';
$status = $todayRow['status'] ?? 'absent';
?>

<div class="page-header">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h2 class="font-display">Welcome, <?= esc($name) ?></h2>
        <div
            style="display: flex; align-items: center; gap: 0.5rem; background: var(--color-surface); padding: 0.5rem 1rem; border-radius: var(--radius-sm); border: 1px solid var(--color-border); box-shadow: var(--shadow-sm);">
            <div
                style="width: 8px; height: 8px; border-radius: 50%; background: var(--color-success); animation: pulse 2s infinite;">
            </div>
            <span style="font-size: 0.8125rem; font-weight: 600;"><?= date('l, d M Y') ?></span>
        </div>
    </div>
</div>

<div class="stats-grid" style="display: grid; grid-template-columns: repeat(12, 1fr); gap: 1.5rem; margin-bottom: 2.5rem;">
    <!-- Monthly Presence -->
    <div class="stat-card" style="grid-column: span 3;">
        <span class="stat-label">Monthly Presence</span>
        <div style="display: flex; align-items: baseline; gap: 0.5rem;">
            <span class="stat-value"><?= (int) ($counts['present'] ?? 0) ?></span>
            <span class="text-muted" style="font-size: 0.875rem;">/
                <?= (int) (($counts['present'] ?? 0) + ($counts['absent'] ?? 0) + ($counts['half_day'] ?? 0)) ?> days
            </span>
        </div>
        <span class="stat-sub" style="color: var(--color-warning);"><?= (int) ($counts['half_day'] ?? 0) ?> half-days recorded</span>
    </div>

    <!-- Registry Profile -->
    <div class="stat-card" style="grid-column: span 3;">
        <span class="stat-label">Registry Profile</span>
        <span class="stat-value"
            style="font-size: 1.125rem; letter-spacing: 0.02em;"><?= esc($employee['emp_code'] ?? 'N/A') ?></span>
        <span class="text-muted"
            style="font-size: 0.8125rem; margin-top: 0.25rem;"><?= esc($employee['designation'] ?? 'Associate') ?></span>
        <span class="stat-sub"
            style="color: var(--color-accent);"><?= esc($employee['department'] ?? 'General Ops') ?></span>
    </div>

    <!-- Monthly Hourglass (Extended) -->
    <div class="stat-card" style="grid-column: span 6; border: 1px solid var(--color-accent); position: relative; overflow: hidden; display: flex; flex-direction: row; gap: 2rem; align-items: center; background: linear-gradient(135deg, #fff 0%, var(--color-accent-soft) 100%);">
        <div style="position: absolute; top: -10px; right: -10px; width: 80px; height: 80px; background: var(--color-accent-soft); border-radius: 50%; z-index: 0;"></div>
        
        <div style="flex: 1; position: relative; z-index: 1;">
            <span class="stat-label">Monthly Hourglass</span>
            <div style="display: flex; gap: 0.75rem; align-items: baseline; margin-bottom: 0.25rem;">
                <span class="stat-value"><?= esc($totalHoursMonth) ?>h</span>
                <span class="text-muted" style="font-size: 0.875rem;">logged</span>
            </div>
            
            <div style="margin-top: 0.75rem; height: 8px; background: var(--color-surface-muted); border-radius: 4px; overflow: hidden;">
                <?php $monthProgress = min(($totalHoursMonth / $requiredHoursMonth) * 100, 100); ?>
                <div style="height: 100%; width: <?= $monthProgress ?>%; background: linear-gradient(90deg, var(--color-accent) 0%, #6366f1 100%); border-radius: 4px;"></div>
            </div>
            
            <div style="margin-top: 0.5rem; display: flex; justify-content: space-between; align-items: center;">
                <span style="font-size: 0.7rem; font-weight: 700; color: var(--color-accent); text-transform: uppercase;"><?= round($monthProgress) ?>% Completed</span>
                <span style="font-size: 0.7rem; font-weight: 600; color: var(--color-text-dim);">Goal: <?= esc($requiredHoursMonth) ?>h</span>
            </div>
        </div>

        <div style="width: 1px; height: 70%; background: var(--color-border); position: relative; z-index: 1;"></div>

        <div style="flex: 0 0 auto; text-align: center; position: relative; z-index: 1; padding-right: 0.5rem;">
            <?php $remainingHours = max(0, $requiredHoursMonth - $totalHoursMonth); ?>
            <span class="stat-label" style="color: var(--color-error);">Remaining</span>
            <div style="font-size: 2.25rem; font-weight: 800; color: var(--color-error); line-height: 1; margin: 0.25rem 0;">
                <?= round($remainingHours, 1) ?>h
            </div>
            <span style="font-size: 0.65rem; font-weight: 600; color: var(--color-text-dim); text-transform: uppercase;">To reach goal</span>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3>Recent Activity Log</h3>
        <a href="<?= site_url('attendance') ?>" class="btn btn-outline"
            style="padding: 0.375rem 0.75rem; font-size: 0.75rem;">View Archive</a>
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
                        <td><span
                                class="badge badge--<?= esc($st) ?>"><?= esc(ucfirst(str_replace('_', ' ', $st))) ?></span>
                        </td>
                        <td><?= $row['first_in'] ? date('H:i', strtotime($row['first_in'])) : '—' ?></td>
                        <td><?= $row['last_out'] ? date('H:i', strtotime($row['last_out'])) : '—' ?></td>
                        <td style="font-weight: 600; color: var(--color-primary);">
                            <?= esc($row['total_hours'] ?? '0') ?>h
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($recent)): ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 3rem; color: var(--color-text-dim);">No
                            recent
                            activity discovered in the registry.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
    @keyframes pulse {
        0% {
            transform: scale(0.95);
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
        }

        70% {
            transform: scale(1);
            box-shadow: 0 0 0 6px rgba(16, 185, 129, 0);
        }

        100% {
            transform: scale(0.95);
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0);
        }
    }
</style>

<?= $this->endSection() ?>
