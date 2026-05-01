<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<div class="page-header">
    <p class="text-muted" style="text-transform: uppercase; letter-spacing: 0.1em; font-size: 0.75rem; font-weight: 600; margin-bottom: 0.5rem;">Time Off Registry</p>
    <h2 class="font-display">Leaves & Holidays</h2>
</div>

<div style="display: grid; grid-template-columns: repeat(12, 1fr); gap: 1.5rem;">
    <!-- Allocation Summary -->
    <div style="grid-column: span 4; display: flex; flex-direction: column; gap: 1.5rem;">
        <div class="card">
            <div class="card-header">
                <h3>Leave Allocation</h3>
            </div>
            <div class="card-body">
                <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                    <?php if (!empty($balances)): ?>
                        <?php foreach ($balances as $bal): ?>
                            <div>
                                <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 0.5rem;">
                                    <span style="font-size: 0.8125rem; font-weight: 600; color: var(--color-text-main);"><?= esc(ucwords(str_replace('_', ' ', $bal['leave_type']))) ?></span>
                                    <span style="font-size: 0.875rem; font-weight: 700; color: var(--color-accent);"><?= esc($bal['remaining']) ?> days left</span>
                                </div>
                                <div style="height: 6px; background: var(--color-surface-muted); border-radius: 3px; overflow: hidden;">
                                    <?php $percent = ($bal['total'] > 0) ? round(($bal['used'] / $bal['total']) * 100) : 0; ?>
                                    <div style="height: 100%; width: <?= $percent ?>%; background: var(--color-accent); border-radius: 3px;"></div>
                                </div>
                                <div style="display: flex; justify-content: space-between; margin-top: 0.375rem;">
                                    <span class="text-muted" style="font-size: 0.7rem; font-weight: 500;">Used: <?= esc($bal['used']) ?></span>
                                    <span class="text-muted" style="font-size: 0.7rem; font-weight: 500;">Total: <?= esc($bal['total']) ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted" style="font-size: 0.875rem; font-style: italic;">No active leave balances detected.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3>Holiday Calendar</h3>
            </div>
            <div class="card-body" style="padding: 0;">
                <div style="display: flex; flex-direction: column;">
                    <?php if (!empty($holidays)): ?>
                        <?php foreach (array_slice($holidays, 0, 5) as $h): ?>
                            <div style="padding: 1rem 1.5rem; border-bottom: 1px solid var(--color-border); display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <div style="font-size: 0.875rem; font-weight: 600;"><?= esc($h['title']) ?></div>
                                    <div class="text-muted" style="font-size: 0.75rem;"><?= date('D, d M Y', strtotime($h['date'])) ?></div>
                                </div>
                                <span class="badge" style="background: var(--color-surface-muted); color: var(--color-text-dim);"><?= esc(ucfirst($h['type'])) ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted" style="padding: 2rem; text-align: center; font-size: 0.875rem;">No upcoming public holidays.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Application Form & History -->
    <div style="grid-column: span 8; display: flex; flex-direction: column; gap: 1.5rem;">
        <div class="card">
            <div class="card-header">
                <h3>Submit Absence Request</h3>
            </div>
            <div class="card-body">
                <form action="<?= site_url('leave/apply') ?>" method="POST" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.25rem;">
                    <div style="grid-column: span 2;">
                        <label class="form-label">Absence Category</label>
                        <select name="leave_type" class="form-input" required>
                            <option value="casual_leave">Casual Leave</option>
                            <option value="sick_leave">Sick Leave</option>
                            <option value="earned_leave">Earned Leave</option>
                        </select>
                    </div>

                    <div>
                        <label class="form-label">Commencement Date</label>
                        <div style="display: flex; gap: 0.5rem;">
                            <input type="date" name="from_date" class="form-input" style="flex: 1;" required min="<?= date('Y-m-d') ?>">
                            <select name="from_session" class="form-input" style="width: auto;">
                                <option value="full">Full Day</option>
                                <option value="half_morning">Half (AM)</option>
                                <option value="half_afternoon">Half (PM)</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="form-label">Conclusion Date</label>
                        <div style="display: flex; gap: 0.5rem;">
                            <input type="date" name="to_date" class="form-input" style="flex: 1;" required min="<?= date('Y-m-d') ?>">
                            <select name="to_session" class="form-input" style="width: auto;">
                                <option value="full">Full Day</option>
                                <option value="half_morning">Half (AM)</option>
                                <option value="half_afternoon">Half (PM)</option>
                            </select>
                        </div>
                    </div>

                    <div style="grid-column: span 2;">
                        <label class="form-label">Professional Justification</label>
                        <textarea name="reason" rows="3" class="form-input" required placeholder="State the rationale for your absence request..."></textarea>
                    </div>

                    <div style="grid-column: span 2; display: flex; justify-content: flex-end; margin-top: 0.5rem;">
                        <button type="submit" class="btn btn-primary" style="padding: 0.75rem 2.5rem;">Submit Request</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3>Request History</h3>
            </div>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Inclusive Dates</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Justification</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($history)): ?>
                            <?php foreach ($history as $req): ?>
                                <?php 
                                    $st = $req['status'] ?? 'pending';
                                    $stClass = ($st === 'approved' ? 'success' : ($st === 'rejected' ? 'absent' : 'half_day'));
                                ?>
                                <tr>
                                    <td style="font-weight: 500;">
                                        <?= date('d M', strtotime($req['from_date'])) ?> — <?= date('d M Y', strtotime($req['to_date'])) ?>
                                    </td>
                                    <td style="font-size: 0.8125rem; color: var(--color-text-dim);"><?= esc(ucwords(str_replace('_', ' ', $req['leave_type']))) ?></td>
                                    <td><span class="badge badge--<?= $stClass ?>"><?= esc(ucfirst($st)) ?></span></td>
                                    <td class="text-muted" style="font-size: 0.8125rem; max-width: 240px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?= esc($req['reason']) ?>">
                                        <?= esc($req['reason']) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" style="text-align: center; padding: 3rem; color: var(--color-text-dim);">No historical absence records found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
