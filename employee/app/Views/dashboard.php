<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<?php
$name = $employee['name'] ?? 'Associate';
$st = $todayRow['attendance_status'] ?? $todayRow['status'] ?? 'absent';
$dayType = $todayRow['day_type'] ?? 'working_day';
if ($dayType === 'weekend' && $st === 'absent' && empty($todayRow['first_in'])) {
    $st = 'weekend';
} elseif ($dayType === 'holiday' && $st === 'absent' && empty($todayRow['first_in'])) {
    $st = 'holiday';
}
?>

<div class="page-header">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h2 class="font-display">Welcome, <?= esc($name) ?></h2>
        <div
            style="display: flex; align-items: center; gap: 0.75rem; background: var(--color-surface); padding: 0.5rem 1.25rem; border-radius: 999px; border: 1px solid var(--color-border); box-shadow: var(--shadow-sm);">
            <?php 
                $dotColor = 'var(--color-danger)';
                if ($st === 'present') $dotColor = 'var(--color-success)';
                elseif ($st === 'work_from_home' || !empty($todayRow['work_mode'])) $dotColor = '#6366f1';
                elseif ($st === 'half_day') $dotColor = 'var(--color-warning)';
                elseif ($st === 'weekend') $dotColor = '#3b82f6';
                elseif ($st === 'holiday') $dotColor = '#8b5cf6';
            ?>
            <div id="stat-status-dot"
                style="width: 10px; height: 10px; border-radius: 50%; background: <?= $dotColor ?>; animation: pulse 2s infinite;">
            </div>
            <div style="display: flex; flex-direction: column;">
                <span style="font-size: 0.8125rem; font-weight: 800; color: var(--color-primary); line-height: 1.2;"><?= date('l, d M') ?></span>
                <span id="stat-status-text" style="font-size: 0.65rem; font-weight: 700; color: <?= $dotColor ?>; text-transform: uppercase; letter-spacing: 0.05em;">
                    <?= str_replace('_', ' ', $st) ?>
                    <?= !empty($todayRow['work_mode']) ? ' (' . strtoupper($todayRow['work_mode']) . ')' : '' ?>
                </span>
            </div>
            <button id="manual-sync-btn" onclick="triggerManualSync()" title="Refresh Attendance Data"
                style="margin-left: 0.5rem; background: none; border: none; padding: 0.25rem; cursor: pointer; color: var(--color-text-dim); display: flex; align-items: center; justify-content: center; transition: all 0.2s;">
                <span class="material-symbols-outlined" id="sync-icon" style="font-size: 1.25rem;">sync</span>
            </button>
            <span id="sync-success-msg" style="display: none; color: var(--color-success); font-size: 0.75rem; font-weight: 700; margin-left: 0.5rem; animation: fadeIn 0.3s ease;">Sync Successful!</span>
        </div>
    </div>
</div>

<div class="stats-grid" style="display: grid; grid-template-columns: repeat(12, 1fr); gap: 1.5rem; margin-bottom: 2.5rem;">
    <!-- Monthly Presence -->
    <div class="stat-card" style="grid-column: span 3;">
        <span class="stat-label">Monthly Presence</span>
        <div style="display: flex; align-items: baseline; gap: 0.5rem;">
            <span class="stat-value" id="stat-present-count"><?= (int) (($counts['present'] ?? 0) + ($counts['work_from_home'] ?? 0)) ?></span>
            <span class="text-muted" style="font-size: 0.875rem;">/
                <span id="stat-total-days"><?= (int) (($counts['present'] ?? 0) + ($counts['absent'] ?? 0) + ($counts['half_day'] ?? 0) + ($counts['work_from_home'] ?? 0)) ?></span> days
            </span>
        </div>
        <div style="display: flex; flex-direction: column; gap: 0.125rem;">
            <span class="stat-sub" style="color: var(--color-warning);"><span id="stat-halfday-count"><?= (int) ($counts['half_day'] ?? 0) ?></span> half-days recorded</span>
            <?php if (($counts['work_from_home'] ?? 0) > 0): ?>
                <span class="stat-sub" style="color: #6366f1; font-weight: 700;"><span id="stat-wfh-count"><?= (int) ($counts['work_from_home']) ?></span> remote sessions</span>
            <?php endif; ?>
        </div>
    </div>

    <!-- Registry Profile -->
    <div class="stat-card" style="grid-column: span 3; display: flex; flex-direction: column; justify-content: space-between;">
        <div>
            <span class="stat-label">Registry Profile</span>
            <span class="stat-value"
                style="font-size: 1.125rem; letter-spacing: 0.02em;"><?= esc($employee['emp_code'] ?? 'N/A') ?></span>
            <span class="text-muted"
                style="font-size: 0.8125rem; margin-top: 0.25rem; display: block; font-weight: 700; color: var(--color-primary);">
                <?= esc($employee['desig_name'] ?? $employee['designation'] ?? 'Associate') ?>
            </span>
            <span class="stat-sub"
                style="color: var(--color-accent); font-weight: 600;"><?= esc($employee['dept_name'] ?? $employee['department'] ?? 'General Ops') ?></span>
        </div>
        
        <div style="margin-top: 1rem; padding-top: 0.75rem; border-top: 1px solid var(--color-border);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                <span style="font-size: 0.65rem; font-weight: 700; color: var(--color-text-dim); text-transform: uppercase;">Joining Date</span>
                <span style="font-size: 0.75rem; font-weight: 600;">
                    <?= !empty($employee['date_of_joining']) ? date('d M Y', strtotime($employee['date_of_joining'])) : ($employee['created_at'] ? date('d M Y', strtotime($employee['created_at'])) : 'N/A') ?>
                </span>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span style="font-size: 0.65rem; font-weight: 700; color: var(--color-text-dim); text-transform: uppercase;">Assigned Shift</span>
                <span style="font-size: 0.75rem; font-weight: 700; color: var(--color-accent);">
                    <?= !empty($employee['shift_name']) ? esc($employee['shift_name']) : 'General' ?>
                    <small style="font-size: 0.6rem; display: block; text-align: right; color: var(--color-text-dim); font-weight: 400;">
                        <?= !empty($employee['start_time']) ? date('H:i', strtotime($employee['start_time'])) . ' - ' . date('H:i', strtotime($employee['end_time'])) : '10:00 - 18:30' ?>
                    </small>
                </span>
            </div>
        </div>
    </div>

    <!-- Monthly Hours (Redesigned for Urgency) -->
    <div class="stat-card" style="grid-column: span 6; border: 1px solid var(--color-border); position: relative; overflow: hidden; display: flex; flex-direction: row; gap: 2rem; align-items: center; background: var(--color-surface);">
        <div style="position: absolute; top: -10px; right: -10px; width: 80px; height: 80px; background: var(--color-surface-muted); border-radius: 50%; z-index: 0;"></div>
        
        <div style="flex: 1; position: relative; z-index: 1;">
            <span class="stat-label">Total Monthly Work Done</span>
            <div style="display: flex; gap: 0.75rem; align-items: baseline; margin-bottom: 0.25rem;">
                <span class="stat-value" style="color: var(--color-text);"><span id="stat-logged-hours"><?= esc($totalHoursMonth) ?></span>h</span>
                <span class="text-muted" style="font-size: 0.875rem;">logged</span>
            </div>
            
            <div style="margin-top: 0.75rem; height: 8px; background: var(--color-surface-muted); border-radius: 4px; overflow: hidden;">
                <?php $monthProgress = min(($totalHoursMonth / $requiredHoursMonth) * 100, 100); ?>
                <div id="stat-progress-bar" style="height: 100%; width: <?= $monthProgress ?>%; background: var(--color-primary); border-radius: 4px; transition: width 0.5s ease;"></div>
            </div>
            
            <div style="margin-top: 0.5rem; display: flex; justify-content: space-between; align-items: center;">
                <span style="font-size: 0.7rem; font-weight: 700; color: var(--color-text-dim); text-transform: uppercase;"><span id="stat-progress-percent"><?= round($monthProgress) ?></span>% Completed</span>
                <span style="font-size: 0.7rem; font-weight: 600; color: var(--color-text-dim);">Monthly Goal: <span id="stat-goal-hours"><?= esc($requiredHoursMonth) ?></span>h</span>
            </div>
        </div>

        <div style="width: 1px; height: 70%; background: var(--color-border); position: relative; z-index: 1;"></div>

        <div style="flex: 0 0 auto; text-align: center; position: relative; z-index: 1; padding-right: 2rem;">
            <?php $remainingHours = max(0, $requiredHoursMonth - $totalHoursMonth); ?>
            <span class="stat-label" style="color: var(--color-error);">Remaining</span>
            <div style="font-size: 2.25rem; font-weight: 800; color: var(--color-error); line-height: 1; margin: 0.25rem 0;">
                <span id="stat-remaining-hours"><?= round($remainingHours, 1) ?></span>h
            </div>
            <span style="font-size: 0.65rem; font-weight: 600; color: var(--color-text-dim); text-transform: uppercase;">To complete monthly goal</span>
        </div>
    </div>
</div>

<script>
    function refreshPersonalStats() {
        const base = (window.siteUrl || '').replace(/\/$/, '');
        fetch(base + '/api/dashboard/personal-summary', {
            headers: { 'Accept': 'application/json' }
        })
        .then(res => res.json())
        .then(response => {
            if (response.status === 'success') {
                const data = response.data;
                const elPresent = document.getElementById('stat-present-count');
                if (elPresent) elPresent.textContent = data.counts.present + (data.counts.work_from_home || 0);
                
                const elWfh = document.getElementById('stat-wfh-count');
                if (elWfh) elWfh.textContent = data.counts.work_from_home || 0;

                const elTotalDays = document.getElementById('stat-total-days');
                if (elTotalDays) elTotalDays.textContent = data.counts.present + data.counts.absent + data.counts.half_day + (data.counts.work_from_home || 0);
                
                const elHalfDay = document.getElementById('stat-halfday-count');
                if (elHalfDay) elHalfDay.textContent = data.counts.half_day;
                
                const elLogged = document.getElementById('stat-logged-hours');
                if (elLogged) elLogged.textContent = data.totalHoursMonth;
                
                const elProgress = document.getElementById('stat-progress-bar');
                if (elProgress) elProgress.style.width = data.monthProgress + '%';
                
                const elPercent = document.getElementById('stat-progress-percent');
                if (elPercent) elPercent.textContent = Math.round(data.monthProgress);
                
                const elRemaining = document.getElementById('stat-remaining-hours');
                if (elRemaining) elRemaining.textContent = data.remainingHours.toFixed(1);

                const elGoal = document.getElementById('stat-goal-hours');
                if (elGoal) elGoal.textContent = data.requiredHoursMonth;

                // Update Status Header
                const elDot = document.getElementById('stat-status-dot');
                const elTxt = document.getElementById('stat-status-text');
                if (elDot && elTxt) {
                    const st = data.todayStatus;
                    let color = 'var(--color-danger)';
                    if (st === 'present') color = 'var(--color-success)';
                    else if (st === 'work_from_home') color = '#6366f1';
                    else if (st === 'half_day') color = 'var(--color-warning)';
                    
                    elDot.style.background = color;
                    elTxt.style.color = color;
                    elTxt.textContent = st.replace('_', ' ');
                }
            }
        })
        .catch(err => console.error('[Dashboard Refresh] Error:', err));
    }

    function triggerManualSync() {
        const btn = document.getElementById('manual-sync-btn');
        const icon = document.getElementById('sync-icon');
        const base = (window.siteUrl || '').replace(/\/$/, '');

        if (btn.disabled) return;

        // Start loading state
        btn.disabled = true;
        btn.style.opacity = '0.5';
        icon.classList.add('rotating');
        
        // Save original text to restore later
        const originalText = btn.innerHTML;

        async function syncMultipleDays() {
            try {
                // We sync the last 7 days sequentially
                let startDate = new Date(Date.now() - 7 * 24 * 60 * 60 * 1000);
                let endDate = new Date();
                
                let totalDays = 8; // Including today
                let successfulSyncs = 0;
                let finalError = '';

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
                    btn.innerHTML = `<span class="material-symbols-outlined rotating" style="font-size: 1.25rem;">sync</span> Syncing (${currentChunk}/${totalChunks})`;
                    
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
                
                // Final Evaluation
                const msg = document.getElementById('sync-success-msg');
                if (successfulSyncs > 0) {
                    if (successfulSyncs === totalChunks) {
                        if (msg) {
                            msg.textContent = 'Sync Complete!';
                            msg.style.display = 'inline';
                        }
                    } else {
                        alert(`Partial Sync Complete: ${successfulSyncs}/${totalChunks} chunks succeeded. Some chunks timed out or failed.`);
                    }
                    setTimeout(() => location.reload(), 1500);
                } else {
                    // All days failed
                    throw new Error(finalError || 'All days failed to sync. eTimeOffice API may be completely offline.');
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
                
                // Revert button
                btn.disabled = false;
                btn.style.opacity = '1';
                btn.innerHTML = originalText;
            }
        }
        
        syncMultipleDays();
    }

    // Refresh stats every 30 seconds
    setInterval(refreshPersonalStats, 30000);

    // Full page auto-refresh every 5 minutes to keep session and layout fresh
    setInterval(() => {
        location.reload();
    }, 300000);
</script>

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
                    <?php 
                    $st = $row['attendance_status'] ?? $row['status'] ?? 'absent';
                    $dayType = $row['day_type'] ?? 'working_day';
                    if ($dayType === 'weekend' && $st === 'absent' && empty($row['first_in'])) {
                        $st = 'weekend';
                    } elseif ($dayType === 'holiday' && $st === 'absent' && empty($row['first_in'])) {
                        $st = 'holiday';
                    }
                    ?>
                    <tr>
                        <td style="font-weight: 500;"><?= date('D, d M Y', strtotime($row['date'])) ?></td>
                        <td>
                            <span class="badge badge--<?= esc($st) ?>"><?= esc(ucfirst(str_replace('_', ' ', $st))) ?></span>
                            <?php if (!empty($row['work_mode'])): ?>
                                <span class="badge badge--info" style="font-size:0.6rem; padding: 2px 4px;"><?= strtoupper(esc($row['work_mode'])) ?></span>
                            <?php endif; ?>
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
    .rotating {
        animation: rotate 1.5s linear infinite;
    }

    @keyframes rotate {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

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
