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
            <form id="full-sync-form" onsubmit="event.preventDefault(); triggerAdminManualSync();">
                <?= csrf_field() ?>
                <input type="hidden" name="type" value="full_range">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
                    <div>
                        <label class="form-label">Inclusive Start</label>
                        <input type="date" id="sync_from_date" name="from_date" class="form-input" value="<?= esc($fullSyncFromDefault) ?>" required>
                    </div>
                    <div>
                        <label class="form-label">Inclusive End</label>
                        <input type="date" id="sync_to_date" name="to_date" class="form-input" value="<?= esc($fullSyncToDefault) ?>" required>
                    </div>
                </div>
                <button type="submit" id="full-sync-btn" class="btn btn-outline" style="width: 100%; padding: 1rem;">
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

<script>
    async function triggerAdminManualSync() {
        const btn = document.getElementById('full-sync-btn');
        const fromDateInput = document.getElementById('sync_from_date');
        const toDateInput = document.getElementById('sync_to_date');
        const base = '<?= site_url() ?>'.replace(/\/$/, '');
        
        if (btn.disabled) return;
        
        let startDate = new Date(fromDateInput.value);
        let endDate = new Date(toDateInput.value);
        
        if (startDate > endDate) {
            alert("Start date cannot be after end date.");
            return;
        }

        // Start loading state
        btn.disabled = true;
        btn.style.opacity = '0.5';
        const originalText = btn.innerHTML;
        
        // Calculate total days
        let diffTime = Math.abs(endDate - startDate);
        let totalDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
        let currentDay = 1;
        let successfulSyncs = 0;
        let finalError = '';

        try {
            // Chunking loop (3 days at a time) to bypass eTimeOffice single-day API bugs
            let currentStart = new Date(startDate);
            let chunks = [];
            
            while (currentStart <= endDate) {
                let currentEnd = new Date(currentStart);
                currentEnd.setDate(currentEnd.getDate() + 2); // 3-day chunk
                if (currentEnd > endDate) currentEnd = new Date(endDate);
                
                chunks.push({
                    start: currentStart.toISOString().split('T')[0],
                    end: currentEnd.toISOString().split('T')[0]
                });
                
                currentStart.setDate(currentStart.getDate() + 3);
            }
            
            let currentChunk = 1;
            let totalChunks = chunks.length;

            for (let chunk of chunks) {
                btn.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i> Syncing Chunk (${currentChunk}/${totalChunks})`;
                
                try {
                    let res = await fetch(base + '/api/sync/run', {
                        method: 'POST',
                        headers: { 
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ 
                            type: 'full_range', 
                            from_date: chunk.start,
                            to_date: chunk.end
                        })
                    });
                    
                    if (!res.ok) {
                        let text = await res.text();
                        throw new Error(text);
                    }
                    
                    let response = await res.json();
                    if (response.status === 'success' || response.status === 'skipped') {
                        successfulSyncs++;
                    } else {
                        throw new Error(response.message || 'Unknown server error');
                    }
                } catch (err) {
                    console.warn(`[Sync] Failed for chunk ${chunk.start} - ${chunk.end}:`, err);
                    finalError = err.message || 'Unknown error';
                }
                
                currentChunk++;
            }
            
            if (successfulSyncs > 0) {
                if (successfulSyncs < totalChunks) {
                    alert(`Partial Sync Complete: ${successfulSyncs}/${totalChunks} chunks succeeded. Some chunks failed.`);
                }
                location.reload();
            } else {
                throw new Error(finalError || 'All chunks failed to sync.');
            }

        } catch (err) {
            console.error('[Sync] Final Error:', err);
            let errMsg = err.message || 'Unknown error';
            if (errMsg.includes('<title>')) {
                const match = errMsg.match(/<title>(.*?)<\/title>/);
                if (match) errMsg = match[1];
            } else {
                errMsg = errMsg.substring(0, 150);
            }
            alert('Sync Error: ' + errMsg);
            
            btn.disabled = false;
            btn.style.opacity = '1';
            btn.innerHTML = originalText;
        }
    }
</script>

<?= $this->endSection() ?>
