<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<div class="page-header">
    <p class="text-muted"
        style="text-transform: uppercase; letter-spacing: 0.1em; font-size: 0.75rem; font-weight: 600; margin-bottom: 0.5rem;">
        Time Off Registry</p>
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
                            <?php
                            $label = 'Balance';
                            $color = 'var(--color-text-dim)';
                            if ($bal['leave_type'] === 'paid_leave') {
                                $label = 'Monthly Paid Leave';
                                $color = 'var(--color-success)';
                            } elseif ($bal['leave_type'] === 'unpaid_leave') {
                                $label = 'Unpaid Leave Balance';
                                $color = 'var(--color-warning)';
                            } elseif ($bal['leave_type'] === 'comp_off') {
                                $label = 'Comp-off Balance';
                                $color = '#6366f1';
                            }
                            ?>
                            <div>
                                <div
                                    style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 0.5rem;">
                                    <span
                                        style="font-size: 0.8125rem; font-weight: 600; color: var(--color-text-main);"><?= $label ?></span>
                                    <span
                                        style="font-size: 0.875rem; font-weight: 700; color: <?= $color ?>;"><?= esc($bal['remaining']) ?>
                                        days left</span>
                                </div>
                                <div
                                    style="height: 6px; background: var(--color-surface-muted); border-radius: 3px; overflow: hidden;">
                                    <?php $percent = ($bal['total'] > 0) ? round(($bal['used'] / $bal['total']) * 100) : 0; ?>
                                    <div
                                        style="height: 100%; width: <?= $percent ?>%; background: <?= $color ?>; border-radius: 3px;">
                                    </div>
                                </div>
                                <div style="display: flex; justify-content: space-between; margin-top: 0.375rem;">
                                    <span class="text-muted" style="font-size: 0.7rem; font-weight: 500;">Used:
                                        <?= esc($bal['used']) ?></span>
                                    <span class="text-muted" style="font-size: 0.7rem; font-weight: 500;">Remaining:
                                        <?= esc($bal['remaining']) ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted" style="font-size: 0.875rem; font-style: italic;">No active leave balances
                            detected.</p>
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
                            <div
                                style="padding: 1rem 1.5rem; border-bottom: 1px solid var(--color-border); display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <div style="font-size: 0.875rem; font-weight: 600;"><?= esc($h['title']) ?></div>
                                    <div class="text-muted" style="font-size: 0.75rem;">
                                        <?php
                                        $hTime = strtotime($h['date'] ?? '');
                                        echo $hTime ? date('D, d M Y', $hTime) : '—';
                                        ?>
                                    </div>
                                </div>
                                <span class="badge"
                                    style="background: var(--color-surface-muted); color: var(--color-text-dim);"><?= esc(ucfirst($h['type'])) ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted" style="padding: 2rem; text-align: center; font-size: 0.875rem;">No upcoming
                            public holidays.</p>
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
                <form action="<?= site_url('leave/apply') ?>" method="POST"
                    style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.25rem;">
                    <div style="grid-column: span 2;">
                        <?php
                        $paidLeaveRemaining = 0;
                        $unpaidLeaveRemaining = 0;
                        $compOffRemaining = 0;
                        if (!empty($balances)) {
                            foreach ($balances as $bal) {
                                if ($bal['leave_type'] === 'paid_leave')
                                    $paidLeaveRemaining = (float) $bal['remaining'];
                                if ($bal['leave_type'] === 'unpaid_leave')
                                    $unpaidLeaveRemaining = (float) $bal['remaining'];
                                if ($bal['leave_type'] === 'comp_off')
                                    $compOffRemaining = (float) $bal['remaining'];
                            }
                        }
                        ?>
                        <label class="form-label">Absence Category</label>
                        <select name="leave_type" class="form-input" required>
                            <option value="" disabled selected>Select Category</option>
                            <option style="front-color: gray;" value="paid_leave" <?= $paidLeaveRemaining <= 0 ? 'disabled' : '' ?>>Paid Leave (PL)
                                <?= $paidLeaveRemaining <= 0 ? '' : '' ?>
                            </option>
                            <option style="front-color: gray;" value="unpaid_leave" <?= $unpaidLeaveRemaining <= 0 ? 'disabled' : '' ?>>Unpaid Leave
                                <?= $unpaidLeaveRemaining <= 0 ? '' : '' ?>
                            </option>
                            <option style="front-color: gray;" value="comp_off" <?= $compOffRemaining <= 0 ? 'disabled' : '' ?>>Comp-off
                                <?= $compOffRemaining <= 0 ? '' : '' ?>
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="form-label">Starting Date</label>
                        <div style="display: flex; gap: 0.5rem;">
                            <input type="date" name="from_date" class="form-input" style="flex: 1;" required
                                min="<?= date('Y-m-01') ?>">
                            <select name="from_session" class="form-input" style="width: auto;">
                                <option value="full">Full Day</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="form-label">End Date</label>
                        <div style="display: flex; gap: 0.5rem;">
                            <input type="date" name="to_date" class="form-input" style="flex: 1;" required
                                min="<?= date('Y-m-01') ?>">
                            <select name="to_session" class="form-input" style="width: auto;">
                                <option value="full">Full Day</option>
                            </select>
                        </div>
                    </div>

                    <div style="grid-column: span 2;">
                        <label class="form-label">Professional Justification</label>
                        <textarea name="reason" rows="3" class="form-input" required
                            placeholder="State the rationale for your absence request..."></textarea>
                    </div>

                    <div style="grid-column: span 2; display: flex; justify-content: flex-end; margin-top: 0.5rem;">
                        <button type="submit" class="btn btn-primary" style="padding: 0.75rem 2.5rem;">Submit
                            Request</button>
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
                                        <?php
                                        $fTime = strtotime($req['from_date'] ?? '');
                                        $tTime = strtotime($req['to_date'] ?? '');
                                        echo ($fTime ? date('d M', $fTime) : '—') . ' — ' . ($tTime ? date('d M Y', $tTime) : '—');
                                        ?>
                                    </td>
                                    <td style="font-size: 0.8125rem; color: var(--color-text-dim);">
                                        <?= esc(ucwords(str_replace('_', ' ', $req['leave_type']))) ?>
                                    </td>
                                    <td><span class="badge badge--<?= $stClass ?>"><?= esc(ucfirst($st)) ?></span></td>
                                    <td class="text-muted"
                                        style="font-size: 0.8125rem; max-width: 240px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"
                                        title="<?= esc($req['reason']) ?>">
                                        <?= esc($req['reason']) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" style="text-align: center; padding: 3rem; color: var(--color-text-dim);">No
                                    historical absence records found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>