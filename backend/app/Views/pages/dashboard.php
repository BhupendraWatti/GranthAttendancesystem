<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<?php $dashDate = $date ?? date('Y-m-d'); ?>
<!-- Server calendar day for dashboard refresh (must match PHP; do not derive from JS UTC). -->
<div id="dashboard-context" data-dashboard-date="<?= esc($dashDate, 'attr') ?>" hidden></div>

<!-- Premium Dashboard Container -->
<div class="premium-dashboard">
    <!-- Page Header -->
    <div class="dashboard-header animate-slide-in-down">
        <div class="dashboard-header-content">
            <h1 class="dashboard-title">Dashboard</h1>
            <p class="dashboard-subtitle">Real-time attendance overview for <?= esc(date('l, d M Y', strtotime($dashDate))) ?></p>
        </div>
        <div class="dashboard-date-badge">
            <span class="date-icon">📅</span>
            <span class="date-text"><?= esc(date('d M Y', strtotime($dashDate))) ?></span>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="stats-grid animate-slide-in-up stagger-1" id="dashboard-stats">
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
    <div class="main-content-grid animate-slide-in-up stagger-2">

        <!-- Daily Attendance Table -->
        <div class="card card--table">
            <div class="card-header">
                <div class="card-header-content">
                    <h3 class="card-title">📋 Today's Roster</h3>
                    <span class="card-subtitle">Live attendance tracking</span>
                </div>
                <div class="card-header-actions">
                    <div class="search-wrapper">
                        <input type="text" id="table-search" class="search-input" placeholder="Search employees...">
                        <span class="search-icon">🔍</span>
                    </div>
                </div>
            </div>
            <div class="card-body card-body--table">
                <div class="table-wrapper">
                    <table id="data-table" class="table">
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
                                <tr class="table-row">
                                    <td>
                                        <div class="employee-cell">
                                            <div class="employee-avatar">
                                                <?= strtoupper(substr($row['name'] ?? $row['emp_code'], 0, 1)) ?>
                                            </div>
                                            <div class="employee-info">
                                                <a href="<?= site_url('employees/' . esc($row['emp_code'])) ?>" class="employee-name">
                                                    <?= esc($row['name'] ?? $row['emp_code']) ?>
                                                </a>
                                                <?php if (!empty($row['department'])): ?>
                                                    <span class="employee-department"><?= esc($row['department']) ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="font-mono"><?= esc($row['emp_code']) ?></td>
                                    <td>
                                        <?php if ($row['first_in']): ?>
                                            <span class="time-badge time-badge--in">
                                                <?= date('h:i A', strtotime($row['first_in'])) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($row['last_out']): ?>
                                            <span class="time-badge time-badge--out">
                                                <?= date('h:i A', strtotime($row['last_out'])) ?>
                                            </span>
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
        <div class="card card--feed">
            <div class="card-header">
                <div class="card-header-content">
                    <h3 class="card-title">
                        <span class="pulse-dot"></span>
                        Live Punch Feed
                    </h3>
                    <span class="card-subtitle">Auto-refreshes every 30s</span>
                </div>
            </div>
            <div class="card-body card-body--feed">
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
                                <span class="feed-time-text"><?= date('h:i A', strtotime($punch['punch_time'])) ?></span>
                                <span class="feed-time-ago"><?= esc($punch['time_ago'] ?? '') ?></span>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li class="feed-item">
                            <div class="feed-empty">
                                <span class="feed-empty-icon">📭</span>
                                <span class="feed-empty-text">No recent punch data available.</span>
                            </div>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>

    </div>
</div>

<style>
/* Premium Dashboard Styles */
.premium-dashboard {
    padding: 2rem;
    max-width: 1600px;
    margin: 0 auto;
}

.dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    margin-bottom: 2rem;
    padding: 1.5rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 1rem;
    color: white;
    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
}

.dashboard-header-content h1 {
    font-family: 'Playfair Display', Georgia, serif;
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.dashboard-subtitle {
    opacity: 0.9;
    font-size: 1rem;
}

.dashboard-date-badge {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    background: rgba(255, 255, 255, 0.2);
    padding: 0.75rem 1.25rem;
    border-radius: 0.5rem;
    backdrop-filter: blur(10px);
}

.date-icon {
    font-size: 1.25rem;
}

.date-text {
    font-weight: 600;
    font-size: 0.875rem;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.main-content-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 1.5rem;
}

@media (max-width: 1024px) {
    .main-content-grid {
        grid-template-columns: 1fr;
    }
}

.card--table {
    overflow: hidden;
}

.card-header-content {
    flex: 1;
}

.card-title {
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.card-subtitle {
    font-size: 0.875rem;
    color: var(--color-text-secondary);
}

.card-header-actions {
    display: flex;
    gap: 1rem;
}

.search-wrapper {
    position: relative;
}

.search-input {
    padding: 0.5rem 1rem 0.5rem 2.5rem;
    border: 1px solid var(--color-border);
    border-radius: 0.5rem;
    font-size: 0.875rem;
    width: 220px;
    transition: all 0.3s ease;
}

.search-input:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.search-icon {
    position: absolute;
    left: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    font-size: 1rem;
    color: var(--color-text-muted);
}

.card-body--table {
    padding: 0;
}

.table-row {
    transition: all 0.2s ease;
}

.table-row:hover {
    background: var(--color-bg-tertiary);
}

.employee-cell {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.employee-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
    font-size: 0.875rem;
}

.employee-info {
    display: flex;
    flex-direction: column;
}

.employee-name {
    font-weight: 600;
    color: var(--color-text-primary);
    text-decoration: none;
    transition: color 0.2s ease;
}

.employee-name:hover {
    color: #667eea;
}

.employee-department {
    font-size: 0.75rem;
    color: var(--color-text-muted);
}

.time-badge {
    display: inline-block;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.75rem;
    font-weight: 600;
}

.time-badge--in {
    background: rgba(0, 184, 148, 0.1);
    color: #00b894;
}

.time-badge--out {
    background: rgba(9, 132, 227, 0.1);
    color: #0984e3;
}

.card--feed {
    height: fit-content;
}

.card-body--feed {
    padding: 0;
}

.feed-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.feed-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem;
    border-bottom: 1px solid var(--color-border-light);
    transition: background 0.2s ease;
}

.feed-item:last-child {
    border-bottom: none;
}

.feed-item:hover {
    background: var(--color-bg-tertiary);
}

.feed-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
    font-size: 0.875rem;
    flex-shrink: 0;
}

.feed-avatar--in {
    background: linear-gradient(135deg, #00b894 0%, #00cec9 100%);
}

.feed-info {
    flex: 1;
    min-width: 0;
}

.feed-name {
    font-weight: 600;
    font-size: 0.875rem;
    color: var(--color-text-primary);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.feed-code {
    font-size: 0.75rem;
    color: var(--color-text-muted);
}

.feed-time {
    text-align: right;
    flex-shrink: 0;
}

.feed-time-text {
    display: block;
    font-weight: 600;
    font-size: 0.875rem;
    color: var(--color-text-primary);
}

.feed-time-ago {
    display: block;
    font-size: 0.75rem;
    color: var(--color-text-muted);
}

.feed-empty {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 2rem;
    color: var(--color-text-muted);
    width: 100%;
}

.feed-empty-icon {
    font-size: 1.5rem;
}

.pulse-dot {
    display: inline-block;
    width: 8px;
    height: 8px;
    background: #00b894;
    border-radius: 50%;
    margin-right: 0.5rem;
    animation: pulse 2s ease-in-out infinite;
}

@keyframes pulse {
    0%, 100% {
        opacity: 1;
        transform: scale(1);
    }
    50% {
        opacity: 0.5;
        transform: scale(1.2);
    }
}

.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 4rem 2rem;
    text-align: center;
    color: var(--color-text-muted);
}

.empty-icon {
    font-size: 3rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}

.empty-state h4 {
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: var(--color-text-primary);
}

.empty-state p {
    font-size: 0.875rem;
}

/* Responsive */
@media (max-width: 768px) {
    .premium-dashboard {
        padding: 1rem;
    }

    .dashboard-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }

    .dashboard-header-content h1 {
        font-size: 1.5rem;
    }

    .stats-grid {
        grid-template-columns: 1fr;
    }

    .search-input {
        width: 100%;
    }
}
</style>

<?= $this->endSection() ?>