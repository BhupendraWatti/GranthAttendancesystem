<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<div class="page-header">
    <p class="text-muted" style="text-transform: uppercase; letter-spacing: 0.1em; font-size: 0.75rem; font-weight: 600; margin-bottom: 0.5rem;">Payroll Registry</p>
    <h2 class="font-display">Salary Statement</h2>
</div>

<div style="display: grid; grid-template-columns: repeat(12, 1fr); gap: 1.5rem;">
    <!-- Main Salary Breakdown -->
    <div style="grid-column: span 8; display: flex; flex-direction: column; gap: 1.5rem;">
        <div class="card" style="padding: 3rem; background: var(--color-primary); color: white; border: none; overflow: hidden; position: relative;">
            <div style="position: absolute; top: -10%; right: -5%; width: 240px; height: 240px; background: var(--color-accent); filter: blur(100px); opacity: 0.2;"></div>
            
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 3rem; position: relative; z-index: 1;">
                <div>
                    <span style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; color: rgba(255,255,255,0.5); letter-spacing: 0.1em; display: block; margin-bottom: 0.5rem;">Net Remuneration</span>
                    <h3 style="font-family: var(--font-display); font-size: 3.5rem; line-height: 1;">₹<?= number_format($salary['net_salary'] ?? 0, 2) ?></h3>
                </div>
                <div style="text-align: right;">
                    <div style="font-size: 0.8125rem; font-weight: 600; color: rgba(255,255,255,0.7);"><?= date('F Y', mktime(0,0,0,$month,1,$year)) ?></div>
                    <span class="badge" style="background: rgba(16, 185, 129, 0.2); color: #4ADE80; margin-top: 0.5rem;">Finalized</span>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 2rem; position: relative; z-index: 1; padding-top: 2rem; border-top: 1px solid rgba(255,255,255,0.1);">
                <div>
                    <label style="font-size: 0.65rem; font-weight: 700; text-transform: uppercase; color: rgba(255,255,255,0.4); display: block; margin-bottom: 0.5rem;">Base Salary</label>
                    <div style="font-size: 1.25rem; font-weight: 600;">₹<?= number_format($salary['base_salary'] ?? 0, 2) ?></div>
                </div>
                <div>
                    <label style="font-size: 0.65rem; font-weight: 700; text-transform: uppercase; color: rgba(255,255,255,0.4); display: block; margin-bottom: 0.5rem;">Active Ratio</label>
                    <div style="font-size: 1.25rem; font-weight: 600;"><?= esc($salary['present_days'] ?? 0) ?> / <?= esc($salary['working_days'] ?? 0) ?> <small style="font-size: 0.75rem; color: rgba(255,255,255,0.5);">DAYS</small></div>
                </div>
                <div>
                    <label style="font-size: 0.65rem; font-weight: 700; text-transform: uppercase; color: rgba(255,255,255,0.4); display: block; margin-bottom: 0.5rem;">Deductions</label>
                    <div style="font-size: 1.25rem; font-weight: 600; color: #F87171;">-₹<?= number_format(($salary['base_salary'] ?? 0) - ($salary['net_salary'] ?? 0), 2) ?></div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3>Earnings Breakdown</h3>
                <a href="<?= site_url("salary/payslip?month={$month}&year={$year}") ?>" class="btn btn-primary" style="padding: 0.5rem 1.25rem; font-size: 0.75rem;">Generate Full Payslip</a>
            </div>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Entitlement</th>
                            <th>Calculation Logic</th>
                            <th style="text-align: right;">Effective Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="font-weight: 600;">Contractual Base</td>
                            <td class="text-muted" style="font-size: 0.8125rem;">Monthly fixed remuneration</td>
                            <td style="text-align: right; font-weight: 600;">₹<?= number_format($salary['base_salary'] ?? 0, 2) ?></td>
                        </tr>
                        <tr>
                            <td style="font-weight: 600; color: var(--color-error);">Attendance Adjustment</td>
                            <td class="text-muted" style="font-size: 0.8125rem;">Prorated based on <?= $salary['present_days'] ?> active days</td>
                            <td style="text-align: right; font-weight: 600; color: var(--color-error);">-₹<?= number_format(($salary['base_salary'] ?? 0) - ($salary['net_salary'] ?? 0), 2) ?></td>
                        </tr>
                        <tr style="background: var(--color-surface-muted);">
                            <td colspan="2" style="font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; font-size: 0.75rem;">Total Net Remuneration</td>
                            <td style="text-align: right; font-weight: 700; font-size: 1rem; color: var(--color-accent);">₹<?= number_format($salary['net_salary'] ?? 0, 2) ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Period & Assistance -->
    <div style="grid-column: span 4; display: flex; flex-direction: column; gap: 1.5rem;">
        <div class="card">
            <div class="card-header">
                <h3>Select Period</h3>
            </div>
            <div class="card-body">
                <form method="get" action="<?= site_url('salary') ?>" style="display: flex; flex-direction: column; gap: 1.25rem;">
                    <div>
                        <label class="form-label">Calendar Year</label>
                        <input type="number" name="year" value="<?= esc((string)$year) ?>" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Statement Month</label>
                        <select name="month" class="form-input">
                            <?php for ($m = 1; $m <= 12; $m++): ?>
                                <option value="<?= $m ?>" <?= $m == $month ? 'selected' : '' ?>><?= date('F', mktime(0,0,0,$m,1)) ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-outline" style="margin-top: 0.5rem;">Update View</button>
                </form>
            </div>
        </div>

        <div class="card" style="background: #EFF6FF; border-color: #BFDBFE;">
            <div class="card-header" style="border-bottom-color: #BFDBFE;">
                <h3 style="color: #1E40AF;">Support Directive</h3>
            </div>
            <div class="card-body">
                <p style="font-size: 0.8125rem; line-height: 1.6; color: #1E40AF;">For enquiries regarding your statement of earnings, please submit a formal ticket to the <span style="font-weight: 700;">Financial Registry</span> within 72 hours of publication.</p>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
