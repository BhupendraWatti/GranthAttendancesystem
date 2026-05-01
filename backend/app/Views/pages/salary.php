<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<?php
$monthName = date('F', mktime(0, 0, 0, $month ?? date('n'), 1));
$yearVal = $year ?? date('Y');
$t = $totals ?? [];
$currency = '₹';
?>

<div class="page-header animate-in">
    <div style="display: flex; justify-content: space-between; align-items: flex-end;">
        <div>
            <h2 class="page-title">Payroll Governance</h2>
            <p class="page-subtitle">Consolidated financial records based on verified service duration for
                <?= esc($monthName) ?> <?= esc($yearVal) ?>.</p>
        </div>
        <div style="display: flex; gap: 0.75rem;">
            <form method="GET" action="<?= site_url('salary') ?>" style="display: flex; gap: 0.5rem;">
                <select name="month" class="form-input" style="padding: 0.5rem; width: auto;"
                    onchange="this.form.submit()">
                    <?php for ($m = 1; $m <= 12; $m++): ?>
                        <option value="<?= $m ?>" <?= ($month ?? date('n')) == $m ? 'selected' : '' ?>>
                            <?= date('F', mktime(0, 0, 0, $m, 1)) ?>
                        </option>
                    <?php endfor; ?>
                </select>
                <select name="year" class="form-input" style="padding: 0.5rem; width: auto;"
                    onchange="this.form.submit()">
                    <?php for ($y = date('Y'); $y >= date('Y') - 2; $y--): ?>
                        <option value="<?= $y ?>" <?= ($year ?? date('Y')) == $y ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
            </form>
        </div>
    </div>
</div>

<!-- Financial Summary -->
<div class="stats-grid animate-in" style="animation-delay: 0.1s;">
    <div class="stat-card">
        <div class="stat-info">
            <span class="label">Gross Disbursement</span>
            <span class="value"><?= $currency ?><?= number_format($t['total_salary_paid'] ?? 0, 2) ?></span>
        </div>
        <div class="stat-icon" style="color: var(--color-success);"><i class="fa-solid fa-receipt"></i></div>
    </div>

    <div class="stat-card">
        <div class="stat-info">
            <span class="label">Registry Deductions</span>
            <span class="value"
                style="color: var(--color-danger);"><?= $currency ?><?= number_format($t['total_deduction'] ?? 0, 2) ?></span>
        </div>
        <div class="stat-icon" style="color: var(--color-danger);"><i class="fa-solid fa-chart-line-down"></i></div>
    </div>

    <div class="stat-card">
        <div class="stat-info">
            <span class="label">Workload Average</span>
            <span class="value"><?= $t['avg_work_hours'] ?? 0 ?>h <small
                    style="font-size: 0.875rem; color: var(--color-text-dim); font-weight: 400;">per
                    personal</small></span>
        </div>
        <div class="stat-icon"><i class="fa-solid fa-stopwatch"></i></div>
    </div>
</div>

<!-- Payroll Ledger -->
<div class="card animate-in" style="animation-delay: 0.2s;">
    <div class="card-header">
        <h3>Earnings Ledger</h3>
        <input type="text" id="table-search" class="form-input" placeholder="Quick search..."
            style="max-width: 240px; padding: 0.5rem;">
    </div>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>personal</th>
                    <th style="text-align: center;">Service Hours</th>
                    <th style="text-align: center;">Registry Ratio</th>
                    <th style="text-align: right;">Adj. Deductions</th>
                    <th style="text-align: right;">Net Remuneration</th>
                    <th style="text-align: right;">Operation</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($salaryData)): ?>
                    <?php foreach ($salaryData as $row): ?>
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center; gap: 0.75rem;">
                                    <div
                                        style="width: 32px; height: 32px; border-radius: 6px; background: var(--color-surface-muted); display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 800; color: var(--color-text-dim);">
                                        <?= strtoupper(substr($row['name'] ?? '', 0, 1)) ?>
                                    </div>
                                    <div>
                                        <div style="font-weight: 700; font-size: 0.875rem;">
                                            <?= esc($row['name'] ?? $row['emp_code']) ?></div>
                                        <div
                                            style="font-size: 0.7rem; color: var(--color-text-dim); text-transform: uppercase; letter-spacing: 0.02em;">
                                            <?= esc($row['emp_code']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td style="text-align: center; font-family: var(--font-mono); font-weight: 600;">
                                <?= esc($row['work_hours'] ?? 0) ?>h <span
                                    style="color: var(--color-text-dim); font-weight: 400;">/
                                    <?= esc($row['expected_hours'] ?? 0) ?>h</span>
                            </td>
                            <td style="text-align: center;">
                                <div style="display: inline-flex; flex-direction: column; align-items: center;">
                                    <span style="font-weight: 700; font-size: 0.875rem;"><?= esc($row['present_days'] ?? 0) ?>
                                        <small style="color: var(--color-text-dim); font-weight: 400;">active</small></span>
                                    <div
                                        style="width: 40px; height: 3px; background: var(--color-surface-muted); border-radius: 2px; margin-top: 4px;">
                                        <?php $ratio = ($row['working_days'] ?? 1) > 0 ? min(($row['present_days'] / $row['working_days']) * 100, 100) : 0; ?>
                                        <div style="height: 100%; width: <?= $ratio ?>%; background: var(--color-success);">
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td
                                style="text-align: right; color: var(--color-danger); font-family: var(--font-mono); font-weight: 600;">
                                -<?= $currency ?><?= number_format($row['deduction'] ?? 0, 2) ?>
                            </td>
                            <td
                                style="text-align: right; font-weight: 800; font-family: var(--font-mono); color: var(--color-primary);">
                                <?= $currency ?>        <?= number_format($row['net_salary'] ?? 0, 2) ?>
                            </td>
                            <td style="text-align: right;">
                                <a href="<?= site_url('payslip/' . esc($row['emp_code'])) ?>?month=<?= $month ?>&year=<?= $year ?>"
                                    class="btn btn-outline" style="padding: 0.25rem 0.75rem; font-size: 0.75rem;">Release
                                    Slip</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 6rem;">
                            <div style="display: flex; flex-direction: column; align-items: center; gap: 1rem;">
                                <i class="fa-solid fa-money-bill-transfer"
                                    style="font-size: 3rem; color: var(--color-border);"></i>
                                <div style="font-weight: 700; color: var(--color-text-dim);">No financial data available for
                                    this cycle.</div>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div
        style="padding: 1rem 1.5rem; background: var(--color-surface-muted); border-top: 1px solid var(--color-border);">
        <p style="font-size: 0.75rem; color: var(--color-text-dim); font-weight: 500;">
            Ledger calculation logic: <code>Net = (Logged Minutes / Expected Minutes) * Base Salary</code>. Verified
            against registry snapshots.
        </p>
    </div>
</div>

<?= $this->endSection() ?>