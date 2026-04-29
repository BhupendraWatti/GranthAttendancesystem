<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<?php $dashDate = $date ?? date('Y-m-d'); ?>
<!-- Server calendar day for dashboard refresh (must match PHP; do not derive from JS UTC). -->
<div id="dashboard-context" data-dashboard-date="<?= esc($dashDate, 'attr') ?>" hidden></div>

<!-- Page Header -->
<div class="page-header">
    <div>
        <h2>Dashboard</h2>
        <p>Real-time attendance overview for <?= esc(date('l, d M Y', strtotime($dashDate))) ?></p>
    </div>
    <div class="date-display">
        📅 <?= esc(date('d M Y', strtotime($dashDate))) ?>
    </div>
</div>

<!-- Stats Grid -->
<div class="stats-grid grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6" id="dashboard-stats">
    <div class="stat-card stat-card--primary">
        <div class="stat-info">
            <div class="stat-label">Total Employees</div>
            <div class="stat-value" id="stat-total-employees"><?= esc($summary['total_employees'] ?? 0) ?></div>
            <div class="stat-sub">Active workforce</div>
        </div>
        <div class="stat-icon">👥</div>
    </div>

    <div class="stat-card stat-card--success">
        <div class="stat-info">
            <div class="stat-label">Present Today</div>
            <div class="stat-value" id="stat-present-today"><?= esc($summary['present_today'] ?? 0) ?></div>
            <div class="stat-sub" id="stat-attendance-rate"><?= esc($summary['attendance_rate'] ?? 0) ?>% attendance rate</div>
        </div>
        <div class="stat-icon">✅</div>
    </div>

    <div class="stat-card stat-card--danger">
        <div class="stat-info">
            <div class="stat-label">Absent Today</div>
            <div class="stat-value" id="stat-absent-today"><?= esc($summary['absent_today'] ?? 0) ?></div>
            <div class="stat-sub" id="stat-halfday-note">
                <?php
                    $halfDay = $summary['half_day_today'] ?? 0;
                    echo $halfDay > 0 ? "{$halfDay} half-day" : 'Full absences';
                ?>
            </div>
        </div>
        <div class="stat-icon">❌</div>
    </div>

    <div class="stat-card stat-card--warning">
        <div class="stat-info">
            <div class="stat-label">Late Arrivals</div>
            <div class="stat-value" id="stat-late-today"><?= esc($summary['late_today'] ?? 0) ?></div>
            <div class="stat-sub">After <?= esc(env('OFFICE_START_TIME', '10:00')) ?> AM</div>
        </div>
        <div class="stat-icon">⏰</div>
    </div>
</div>

<!-- Main Content Grid -->
<div class="grid-3 grid grid-cols-1 lg:grid-cols-[2fr_1fr] gap-6">

    <!-- Daily Attendance Table -->
    <div class="card">
        <div class="card-header">
            <h3>📋 Today's Roster</h3>
            <div class="form-inline">
                <input type="text" id="table-search" class="form-control" placeholder="Search employees..." style="max-width:220px; padding: 6px 12px; font-size: 0.82rem;">
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-wrapper">
                <table id="data-table">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Code</th>
                            <th>First In</th>
                            <th>Last Out</th>
                            <th>Work Hrs</th>
                            <th>Status</th>
                            <th>Late</th>
                        </tr>
                    </thead>
                    <tbody id="attendance-table-body">
                        <?php if (!empty($attendance)): ?>
                            <?php foreach ($attendance as $row): ?>
                            <tr>
                                <td>
                                    <a href="<?= site_url('employees/' . esc($row['emp_code'])) ?>" style="font-weight:600;">
                                        <?= esc($row['name'] ?? $row['emp_code']) ?>
                                    </a>
                                    <?php if (!empty($row['department'])): ?>
                                        <br><small class="text-muted"><?= esc($row['department']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td class="font-mono"><?= esc($row['emp_code']) ?></td>
                                <td>
                                    <?php if ($row['first_in']): ?>
                                        <?= date('h:i A', strtotime($row['first_in'])) ?>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($row['last_out']): ?>
                                        <?= date('h:i A', strtotime($row['last_out'])) ?>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="font-mono">
                                    <?php
                                        $hrs = floor(($row['work_minutes'] ?? 0) / 60);
                                        $mins = ($row['work_minutes'] ?? 0) % 60;
                                        echo "{$hrs}h {$mins}m";
                                    ?>
                                </td>
                                <td>
                                    <span class="badge badge--<?= esc($row['status']) ?>">
                                        <?= esc(str_replace('_', ' ', $row['status'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (($row['late_minutes'] ?? 0) > 0): ?>
                                        <span class="badge badge--late"><?= esc($row['late_minutes']) ?> min</span>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7">
                                    <div class="empty-state">
                                        <div class="empty-icon">📊</div>
                                        <h4>No attendance data</h4>
                                        <p>Run a sync to populate attendance records.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Live Punch Feed -->
    <div class="card">
        <div class="card-header">
            <h3><span class="pulse-dot"></span> Live Punch Feed</h3>
            <small class="text-muted">Auto-refreshes every 30s</small>
        </div>
        <div class="card-body">
            <ul class="feed-list" id="live-feed">
                <?php if (!empty($livePunches)): ?>
                    <?php foreach ($livePunches as $punch): ?>
                    <li class="feed-item">
                        <div class="feed-avatar feed-avatar--in">
                            <?= strtoupper(substr($punch['name'] ?? $punch['emp_code'], 0, 2)) ?>
                        </div>
                        <div class="feed-info">
                            <div class="feed-name"><?= esc($punch['name'] ?? $punch['emp_code']) ?></div>
                            <div class="feed-code"><?= esc($punch['emp_code']) ?></div>
                        </div>
                        <div class="feed-time">
                            <?= date('h:i A', strtotime($punch['punch_time'])) ?>
                            <small><?= esc($punch['time_ago'] ?? '') ?></small>
                        </div>
                    </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li class="feed-item">
                        <div class="text-muted" style="padding:20px 0; text-align:center; width:100%;">
                            No recent punch data available.
                        </div>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>

</div>

<?= $this->endSection() ?>
