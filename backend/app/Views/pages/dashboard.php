<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<!-- Dashboard Data Context for JS -->
<div id="dashboard-context" data-dashboard-date="<?= date('Y-m-d') ?>" style="display: none;"></div>

<div class="page-header animate-in">
    <div style="display: flex; justify-content: space-between; align-items: flex-end;">
        <div>
            <h2 class="page-title">Enterprise Dashboard</h2>
            <p class="page-subtitle">Real-time operational metrics and system status.</p>
        </div>
        <div style="text-align: right;">
            <div
                style="font-family: var(--font-mono); font-size: 1.125rem; font-weight: 700; color: var(--color-primary);">
                <?= date('H:i') ?></div>
            <div
                style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.1em; color: var(--color-text-dim); font-weight: 700;">
                System Time</div>
        </div>
    </div>
</div>

<!-- Key Performance Indicators -->
<div class="stats-grid animate-in" style="animation-delay: 0.1s;" id="dashboard-stats">
    <div class="stat-card">
        <div class="stat-info">
            <span class="label">Total Workforce</span>
            <span class="value" id="stat-total-employees"><?= esc((string) ($total_employees ?? 0)) ?></span>
        </div>
        <div class="stat-icon"><i class="fa-solid fa-users"></i></div>
    </div>

    <div class="stat-card">
        <div class="stat-info">
            <span class="label">Active Present</span>
            <span class="value" id="stat-present-today"><?= esc((string) ($present_today ?? 0)) ?></span>
            <div style="font-size: 0.7rem; margin-top: 0.5rem; color: var(--color-success); font-weight: 700;"
                id="stat-attendance-rate"></div>
        </div>
        <div class="stat-icon" style="color: var(--color-success);"><i class="fa-solid fa-user-check"></i></div>
    </div>

    <div class="stat-card">
        <div class="stat-info">
            <span class="label">Late Arrivals</span>
            <span class="value" id="stat-late-today"><?= esc((string) ($late_today ?? 0)) ?></span>
        </div>
        <div class="stat-icon" style="color: var(--color-warning);"><i class="fa-solid fa-clock"></i></div>
    </div>

    <div class="stat-card">
        <div class="stat-info">
            <span class="label">Absent / Leave</span>
            <span class="value" id="stat-absent-today"><?= esc((string) ($absent_today ?? 0)) ?></span>
            <div style="font-size: 0.7rem; margin-top: 0.5rem; color: var(--color-text-dim);" id="stat-halfday-note">
            </div>
        </div>
        <div class="stat-icon" style="color: var(--color-danger);"><i class="fa-solid fa-user-minus"></i></div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2.5rem;" class="animate-in"
    style="animation-delay: 0.2s;">

    <!-- Primary Activity Log -->
    <div class="card">
        <div class="card-header">
            <h3>Recent personal Activity</h3>
            <a href="<?= site_url('employees') ?>" class="btn btn-outline"
                style="padding: 0.375rem 0.75rem; font-size: 0.75rem;">Full Logs</a>
        </div>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Identity</th>
                        <th>Handshake</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="attendance-table-body">
                    <?php if (!empty($recent_punches)): ?>
                        <?php foreach ($recent_punches as $punch): ?>
                            <tr>
                                <td style="font-weight: 700;"><?= esc($punch['name']) ?></td>
                                <td><code
                                        style="font-family: var(--font-mono); font-size: 0.8rem;"><?= esc($punch['emp_code']) ?></code>
                                </td>
                                <td style="font-family: var(--font-mono);"><?= date('h:i A', strtotime($punch['punch_time'])) ?>
                                </td>
                                <td>
                                    <?php if (($punch['status'] ?? '') === 'late'): ?>
                                        <span class="badge badge--warning">Late Arrival</span>
                                    <?php else: ?>
                                        <span class="badge badge--success">On Time</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" style="text-align: center; padding: 4rem; color: var(--color-text-dim);">No
                                active sessions detected for current period.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Secondary Context -->
    <div style="display: flex; flex-direction: column; gap: 1.5rem;">
        <div class="card">
            <div class="card-header">
                <h3>Live Handshake Feed</h3>
            </div>
            <div class="card-body" style="padding: 0;">
                <ul id="live-feed" style="list-style: none;">
                    <li style="padding: 2rem; text-align: center; color: var(--color-text-dim); font-size: 0.8125rem;">
                        Initialising real-time telemetry...</li>
                </ul>
            </div>
        </div>

        <div class="card"
            style="background: var(--color-primary); color: white; border: none; overflow: hidden; position: relative;">
            <div
                style="position: absolute; top: -10%; right: -5%; width: 120px; height: 120px; background: var(--color-accent); filter: blur(60px); opacity: 0.2;">
            </div>
            <div class="card-body" style="padding: 2rem; position: relative; z-index: 1;">
                <h4 style="color: white; margin-bottom: 1rem; font-family: var(--font-display);">Admin Directive</h4>
                <p style="font-size: 0.875rem; color: rgba(255,255,255,0.6); line-height: 1.6;">
                    System audits are scheduled for every Sunday at 00:00 UTC. Ensure all records are finalized by then.
                </p>
            </div>
        </div>
    </div>

</div>

<style>
    /* Styles for live feed items added by JS */
    #live-feed .feed-item {
        padding: 1rem 1.5rem;
        border-bottom: 1px solid var(--color-border);
        display: flex;
        align-items: center;
        gap: 1rem;
        transition: background 0.2s;
    }

    #live-feed .feed-item:last-child {
        border-bottom: none;
    }

    #live-feed .feed-item:hover {
        background: var(--color-surface-muted);
    }

    #live-feed .feed-avatar {
        width: 32px;
        height: 32px;
        border-radius: 6px;
        background: var(--color-surface-muted);
        color: var(--color-text-dim);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.7rem;
        font-weight: 800;
    }

    #live-feed .feed-info {
        flex: 1;
    }

    #live-feed .feed-name {
        font-size: 0.875rem;
        font-weight: 700;
        color: var(--color-primary);
    }

    #live-feed .feed-code {
        font-size: 0.7rem;
        color: var(--color-text-dim);
        text-transform: uppercase;
    }

    #live-feed .feed-time {
        text-align: right;
        font-size: 0.8125rem;
        font-weight: 600;
        font-family: var(--font-mono);
    }

    #live-feed .feed-time small {
        display: block;
        font-size: 0.65rem;
        color: var(--color-text-dim);
        font-family: var(--font-body);
        font-weight: 400;
    }
</style>

<?= $this->endSection() ?>