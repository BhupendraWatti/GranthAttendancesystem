<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<?php
$fullSyncToDefault   = date('Y-m-d', strtotime('-1 day'));
$fullSyncFromDefault = date('Y-m-01', strtotime($fullSyncToDefault));
?>

<div class="page-header animate-in">
    <h2 class="page-title">Data Integration</h2>
    <p class="page-subtitle">Synchronize attendance registries with eTimeOffice cloud infrastructure.</p>
</div>

<div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem; margin-bottom: 2.5rem;" class="animate-in" style="animation-delay: 0.1s;">
    
    <!-- Incremental Sync -->
    <div class="card">
        <div class="card-header">
            <h3>Standard Delta Sync</h3>
            <span class="badge badge--info">Recommended</span>
        </div>
        <div class="card-body">
            <p style="font-size: 0.875rem; color: var(--color-text-dim); margin-bottom: 2rem; line-height: 1.6;">
                Efficiently retrieves new punch records generated since the last successful handshake. Ideal for periodic daytime updates.
            </p>
            <form method="POST" action="<?= site_url('sync/run') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="type" value="incremental">
                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1rem;" onclick="this.innerHTML='<i class=\'fa-solid fa-spinner fa-spin\'></i> Initiating Sync...';">
                    Execute Delta Synchronization
                </button>
            </form>
        </div>
    </div>

    <!-- Full Sync -->
    <div class="card">
        <div class="card-header">
            <h3>Global Range Re-sync</h3>
            <span class="badge badge--warning">Heavy Operation</span>
        </div>
        <div class="card-body">
            <p style="font-size: 0.8125rem; color: var(--color-text-dim); margin-bottom: 1.5rem; line-height: 1.6;">
                Force-reprocesses all data within a specific temporal window. Use only for manual corrections or registry audits.
            </p>
            <form method="POST" action="<?= site_url('sync/run') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="type" value="full_range">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
                    <div>
                        <label class="form-label">Inclusive Start</label>
                        <input type="date" name="from_date" class="form-input" value="<?= esc($fullSyncFromDefault) ?>" required>
                    </div>
                    <div>
                        <label class="form-label">Inclusive End</label>
                        <input type="date" name="to_date" class="form-input" value="<?= esc($fullSyncToDefault) ?>" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-outline" style="width: 100%; padding: 1rem;" onclick="this.innerHTML='<i class=\'fa-solid fa-spinner fa-spin\'></i> Processing Range...';">
                    Execute Range Synchronization
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Sync History Ledger -->
<div class="card animate-in" style="animation-delay: 0.2s;">
    <div class="card-header">
        <h3>Sync Execution Ledger</h3>
        <?php if ($isRunning ?? false): ?>
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <div style="width: 8px; height: 8px; border-radius: 50%; background: var(--color-warning); animation: pulse 1.5s infinite;"></div>
                <span style="font-size: 0.75rem; font-weight: 700; color: var(--color-warning); text-transform: uppercase;">Operation in progress</span>
            </div>
        <?php endif; ?>
    </div>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Status</th>
                    <th style="text-align: center;">Fetched</th>
                    <th style="text-align: center;">Committed</th>
                    <th>Execution Window</th>
                    <th>System Notes</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($history)): ?>
                    <?php foreach ($history as $log): ?>
                    <tr>
                        <td>
                            <span style="font-weight: 700; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.02em;"><?= esc($log['sync_type'] ?? '') ?></span>
                        </td>
                        <td>
                            <?php
                                $st = $log['status'] ?? '';
                                $badgeClass = match($st) {
                                    'success'  => 'badge--success',
                                    'failed'   => 'badge--danger',
                                    'running'  => 'badge--warning',
                                    default    => 'badge--info'
                                };
                            ?>
                            <span class="badge <?= $badgeClass ?>"><?= esc($st) ?></span>
                        </td>
                        <td style="text-align: center; font-family: var(--font-mono); font-weight: 600;"><?= esc($log['records_fetched'] ?? 0) ?></td>
                        <td style="text-align: center; font-family: var(--font-mono); font-weight: 600; color: var(--color-success);"><?= esc($log['records_saved'] ?? 0) ?></td>
                        <td style="font-size: 0.8125rem;">
                            <div style="font-weight: 600;"><?= $log['started_at'] ? date('d M, H:i', strtotime($log['started_at'])) : '—' ?></div>
                            <div class="text-muted" style="font-size: 0.7rem;"><?= $log['completed_at'] ? 'Finished in ' . round(strtotime($log['completed_at']) - strtotime($log['started_at'])) . 's' : 'Pending completion' ?></div>
                        </td>
                        <td>
                            <?php if (!empty($log['error_message'])): ?>
                                <div style="color: var(--color-danger); font-size: 0.75rem; max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?= esc($log['error_message']) ?>">
                                    <i class="fa-solid fa-circle-exclamation"></i> <?= esc($log['error_message']) ?>
                                </div>
                            <?php else: ?>
                                <span class="text-muted" style="font-size: 0.75rem;">Normal completion.</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 5rem;">
                            <div style="display: flex; flex-direction: column; align-items: center; gap: 1rem;">
                                <i class="fa-solid fa-clock-rotate-left" style="font-size: 3rem; color: var(--color-border);"></i>
                                <div style="font-weight: 700; color: var(--color-text-dim);">No synchronization records discovered.</div>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
    @keyframes pulse {
        0% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.4; transform: scale(1.2); }
        100% { opacity: 1; transform: scale(1); }
    }
</style>

<?= $this->endSection() ?>
