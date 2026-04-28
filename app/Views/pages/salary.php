<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<?php
$monthName = date('F', mktime(0, 0, 0, $month ?? date('n'), 1));
$yearVal = $year ?? date('Y');
$t = $totals ?? [];
$currency = '₹';
?>

<!-- Page Header -->
<div class="page-header">
    <div>
        <h2>Salary Management</h2>
        <p>Review and manage employee compensation records. Totals use <strong>processed daily attendance</strong> for
            the selected month (not the live punch feed). After <a href="<?= site_url('sync') ?>">Sync</a> imports punches, attendance is
            updated for affected dates—refresh this page to see new figures.</p>
    </div>
    <div class="page-header-actions">
        <form method="GET" action="<?= site_url('salary') ?>" class="form-inline" style="gap:8px;">
            <select name="month" class="form-control" style="width:140px;" onchange="this.form.submit()">
                <?php for ($m = 1; $m <= 12; $m++): ?>
                    <option value="<?= $m ?>" <?= ($month ?? date('n')) == $m ? 'selected' : '' ?>>
                        📅 <?= date('F', mktime(0, 0, 0, $m, 1)) ?>
                    </option>
                <?php endfor; ?>
            </select>
            <select name="year" class="form-control" style="width:100px;" onchange="this.form.submit()">
                <?php for ($y = date('Y'); $y >= date('Y') - 2; $y--): ?>
                    <option value="<?= $y ?>" <?= ($year ?? date('Y')) == $y ? 'selected' : '' ?>><?= $y ?></option>
                <?php endfor; ?>
            </select>
        </form>
    </div>
</div>

<!-- Summary Stat Cards (Stitch design: 3 metric cards) -->
<div class="stats-grid mb-3 grid grid-cols-1 sm:grid-cols-3 gap-6">
    <div class="stat-card stat-card--success">
        <div class="stat-info">
            <div class="stat-label">Total Salary Paid</div>
            <div class="stat-value"><?= $currency ?><?= number_format($t['total_salary_paid'] ?? 0, 2) ?></div>
            <div class="stat-sub"><?= esc($monthName) ?> <?= esc($yearVal) ?></div>
        </div>
        <div class="stat-icon">💰</div>
    </div>
    <div class="stat-card stat-card--danger">
        <div class="stat-info">
            <div class="stat-label">Total Deduction</div>
            <div class="stat-value"><?= $currency ?><?= number_format($t['total_deduction'] ?? 0, 2) ?></div>
            <div class="stat-sub">Attendance-based</div>
        </div>
        <div class="stat-icon">📉</div>
    </div>
    <div class="stat-card stat-card--primary">
        <div class="stat-info">
            <div class="stat-label">Avg Work Hours</div>
            <div class="stat-value"><?= $t['avg_work_hours'] ?? 0 ?>h <span style="font-size:0.6em;font-weight:400;">/
                    emp</span></div>
            <div class="stat-sub"><?= $t['employee_count'] ?? 0 ?> employees</div>
        </div>
        <div class="stat-icon">⏱</div>
    </div>
</div>

<!-- Employee Salary Table -->
<div class="card">
    <div class="card-header">
        <h3>Employee Salary Details</h3>
        <input type="text" id="table-search" class="form-control" placeholder="Search employee..."
            style="max-width:200px; padding: 6px 12px; font-size: 0.82rem;">
    </div>
    <div class="card-body p-0">
        <div class="table-wrapper">
            <table id="data-table">
                <thead>
                    <tr>
                        <th>Employee Name</th>
                        <th class="text-center">Work Hours</th>
                        <th class="text-center">Expected Hours</th>
                        <th class="text-center">Present</th>
                        <th class="text-center">Absent</th>
                        <th class="text-right">Deduction</th>
                        <th class="text-right">Net Salary</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($salaryData)): ?>
                        <?php foreach ($salaryData as $row): ?>
                            <tr>
                                <td>
                                    <div style="display:flex;align-items:center;gap:10px;">
                                        <div class="feed-avatar feed-avatar--in"
                                            style="width:32px;height:32px;font-size:0.65rem;">
                                            <?= strtoupper(substr($row['name'] ?? '', 0, 2)) ?>
                                        </div>
                                        <div>
                                            <a href="<?= site_url('employees/' . esc($row['emp_code'])) ?>"
                                                style="font-weight:600;"><?= esc($row['name'] ?? $row['emp_code']) ?></a>
                                            <br><small
                                                class="text-muted"><?= ucwords(str_replace('_', ' ', esc($row['employee_type'] ?? 'full time'))) ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center font-mono"><?= esc($row['work_hours'] ?? 0) ?>h</td>
                                <td class="text-center font-mono"><?= esc($row['expected_hours'] ?? 0) ?>h</td>
                                <td class="text-center">
                                    <span class="font-bold text-success"><?= esc($row['present_days'] ?? 0) ?></span>
                                </td>
                                <td class="text-center">
                                    <span class="text-danger"><?= esc($row['absent_days'] ?? 0) ?></span>
                                </td>
                                <td class="text-right">
                                    <?php if (($row['deduction'] ?? 0) > 0): ?>
                                        <span
                                            class="text-danger font-mono">-<?= $currency ?><?= number_format($row['deduction'], 2) ?></span>
                                    <?php else: ?>
                                        <span class="text-success font-mono"><?= $currency ?>0.00</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-right font-bold font-mono" style="color:var(--success-dark);">
                                    <?= $currency ?>        <?= number_format($row['net_salary'] ?? 0, 2) ?>
                                </td>
                                <td class="text-center">
                                    <a href="<?= site_url('payslip/' . esc($row['emp_code'])) ?>?month=<?= $month ?>&year=<?= $year ?>"
                                        class="btn btn--outline btn--sm">
                                        📄 Payslip
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8">
                                <div class="empty-state">
                                    <div class="empty-icon">💰</div>
                                    <h4>No salary data available</h4>
                                    <p>No attendance records found for <?= esc($monthName) ?>     <?= esc($yearVal) ?>. Run a
                                        sync first.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php if (!empty($salaryData)): ?>
        <div class="card-footer">
            <small class="text-muted">
                Showing <?= count($salaryData) ?> employees ·
                Formula: salary = (actual_minutes ÷ expected_minutes) × monthly_salary ·
                Expected: <?= esc(env('WORKING_DAYS_PER_MONTH', 23)) ?> working days/month
            </small>
        </div>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>