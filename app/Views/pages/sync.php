<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<?php
$fullSyncToDefault   = date('Y-m-d', strtotime('-1 day'));
$fullSyncFromDefault = date('Y-m-01', strtotime($fullSyncToDefault));
?>

<!-- Page Header -->
<div class="page-header">
    <div>
        <h2>Sync Data</h2>
        <p>Synchronize attendance data from eTimeOffice cloud</p>
    </div>
</div>

<!-- Sync Actions -->
<div class="grid-2 mb-3 grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="card">
        <div class="card-header">
            <h3>🔄 Incremental Sync</h3>
        </div>
        <div class="card-body">
            <p style="font-size: 0.875rem; color: var(--text-secondary); margin-bottom: 16px;">
                Fetches the latest punch data since the last successful sync. Use this for quick updates.
            </p>
            <form method="POST" action="/sync/run">
                <?= csrf_field() ?>
                <input type="hidden" name="type" value="incremental">
                <button type="submit" class="btn btn--primary" onclick="this.innerHTML='<span class=spinner></span> Syncing...'; this.disabled=true; this.form.submit();">
                    ⚡ Run Incremental Sync
                </button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3>📦 Full Sync</h3>
        </div>
        <div class="card-body">
            <p style="font-size: 0.875rem; color: var(--text-secondary); margin-bottom: 16px;">
                Re-fetches all punch data for a date range and reprocesses attendance. Use for corrections. Defaults use <strong>yesterday</strong> as “To” because the vendor API often errors on the current calendar day.
            </p>
            <form method="POST" action="/sync/run">
                <?= csrf_field() ?>
                <input type="hidden" name="type" value="full_range">
                <div class="form-inline mb-2">
                    <div class="form-group">
                        <label for="from_date">From Date</label>
                        <input type="date" name="from_date" id="from_date" class="form-control" value="<?= esc($fullSyncFromDefault) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="to_date">To Date</label>
                        <input type="date" name="to_date" id="to_date" class="form-control" value="<?= esc($fullSyncToDefault) ?>" required>
                    </div>
                </div>
                <button type="submit" class="btn btn--success" onclick="this.innerHTML='<span class=spinner></span> Syncing...'; this.disabled=true; this.form.submit();">
                    📥 Run Full Sync
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Sync History -->
<div class="card">
    <div class="card-header">
        <h3>📜 Sync History</h3>
        <?php if ($isRunning ?? false): ?>
            <span class="badge badge--late"><span class="pulse-dot"></span> Sync in progress</span>
        <?php endif; ?>
    </div>
    <div class="card-body p-0">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Records Fetched</th>
                        <th>Records Saved</th>
                        <th>Started</th>
                        <th>Completed</th>
                        <th>Error</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($history)): ?>
                        <?php foreach ($history as $log): ?>
                        <tr>
                            <td>
                                <span class="badge badge--info"><?= esc($log['sync_type'] ?? '') ?></span>
                            </td>
                            <td>
                                <?php
                                    $statusClass = match($log['status'] ?? '') {
                                        'success'  => 'badge--present',
                                        'failed'   => 'badge--absent',
                                        'running'  => 'badge--late',
                                        default    => 'badge--info'
                                    };
                                ?>
                                <span class="badge <?= $statusClass ?>"><?= esc($log['status'] ?? '') ?></span>
                            </td>
                            <td class="font-mono"><?= esc($log['records_fetched'] ?? 0) ?></td>
                            <td class="font-mono"><?= esc($log['records_saved'] ?? 0) ?></td>
                            <td><?= $log['started_at'] ? date('d M h:i A', strtotime($log['started_at'])) : '—' ?></td>
                            <td><?= $log['completed_at'] ? date('d M h:i A', strtotime($log['completed_at'])) : '—' ?></td>
                            <td>
                                <?php if (!empty($log['error_message'])): ?>
                                    <small class="text-danger"><?= esc(mb_substr($log['error_message'], 0, 80)) ?>...</small>
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
                                    <div class="empty-icon">📜</div>
                                    <h4>No sync history</h4>
                                    <p>Run your first sync to see history here.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
