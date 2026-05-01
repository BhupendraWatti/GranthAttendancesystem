<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<div style="max-width: 900px; margin: 0 auto;">
    <div class="card" style="padding: 5rem; background: #fff; color: #000; border-radius: 0; box-shadow: 0 40px 100px rgba(0,0,0,0.2);">
        
        <div style="display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 4px solid #000; padding-bottom: 3rem; margin-bottom: 4rem;">
            <div>
                <h2 style="font-family: var(--font-display); font-size: 3rem; margin: 0;">Granth.</h2>
                <p style="text-transform: uppercase; letter-spacing: 0.3em; font-size: 0.7rem; font-weight: 800; margin-top: 0.5rem;">Corporate Payroll</p>
            </div>
            <div style="text-align: right;">
                <h3 style="font-family: var(--font-display); font-size: 1.5rem; margin: 0;">Earnings Statement</h3>
                <p style="font-size: 0.9rem; color: #666; margin-top: 0.5rem;"><?= date('F Y', mktime(0,0,0,$month,1,$year)) ?></p>
            </div>
        </div>

        <div style="display: grid; grid-cols-1 md:grid-cols-2 gap-12 mb-12;">
            <div>
                <label style="display: block; font-size: 0.65rem; font-weight: 800; text-transform: uppercase; color: #999; margin-bottom: 0.5rem;">Employee</label>
                <div style="font-size: 1.2rem; font-weight: 700;"><?= esc($employee['name']) ?></div>
                <div style="font-size: 0.9rem; color: #666;"><?= esc($employee['designation']) ?></div>
            </div>
            <div style="text-align: right;">
                <label style="display: block; font-size: 0.65rem; font-weight: 800; text-transform: uppercase; color: #999; margin-bottom: 0.5rem;">Identity</label>
                <div style="font-size: 1.1rem; font-weight: 700;"><?= esc($employee['emp_code']) ?></div>
                <div style="font-size: 0.9rem; color: #666;"><?= esc($employee['department']) ?></div>
            </div>
        </div>

        <div style="margin: 4rem 0;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 2px solid #000;">
                        <th style="padding: 1.5rem 0; color: #000; font-weight: 800;">Description</th>
                        <th style="padding: 1.5rem 0; text-align: right; color: #000; font-weight: 800;">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="padding: 2rem 0; background: transparent; border: 0; border-bottom: 1px solid #eee; color: #000;">Base Remuneration</td>
                        <td style="padding: 2rem 0; text-align: right; background: transparent; border: 0; border-bottom: 1px solid #eee; font-family: var(--font-display); font-size: 1.25rem; color: #000;">₹<?= number_format($salary['base_salary'] ?? 0, 2) ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 2rem 0; background: transparent; border: 0; border-bottom: 1px solid #eee; color: #000;">Attendance Adjustment <small style="color: #999; display: block; font-size: 0.7rem; margin-top: 0.25rem;">Based on <?= $salary['present_days'] ?>/<?= $salary['working_days'] ?> active days</small></td>
                        <td style="padding: 2rem 0; text-align: right; background: transparent; border: 0; border-bottom: 1px solid #eee; font-family: var(--font-display); font-size: 1.25rem; color: #EF4444;">-₹<?= number_format(($salary['base_salary'] ?? 0) - ($salary['net_salary'] ?? 0), 2) ?></td>
                    </tr>
                    <tr style="border-top: 4px solid #000;">
                        <td style="padding: 3rem 0; background: transparent; border: 0; font-weight: 800; font-size: 1.25rem; color: #000; text-transform: uppercase; letter-spacing: 0.1em;">Net Payable</td>
                        <td style="padding: 3rem 0; text-align: right; background: transparent; border: 0; font-family: var(--font-display); font-size: 3.5rem; color: #000;">₹<?= number_format($salary['net_salary'] ?? 0, 2) ?></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div style="margin-top: 6rem; padding-top: 3rem; border-top: 1px solid #eee; display: flex; justify-content: space-between; align-items: flex-end;">
            <div style="font-size: 0.8rem; color: #999; max-width: 400px;">
                This is a digitally generated document. No physical signature is required. All calculations are subject to statutory compliance and audit.
            </div>
            <div>
                <button onclick="window.print()" style="background: #000; color: #fff; padding: 1rem 2.5rem; border: none; font-weight: 800; text-transform: uppercase; letter-spacing: 0.1em; cursor: pointer;">Print Record</button>
            </div>
        </div>

    </div>
</div>

<style>
    @media print {
        .sidebar, footer, .page-header { display: none !important; }
        .main-wrapper { margin-left: 0 !important; padding: 0 !important; background: #fff !important; }
        body { background: #fff !important; }
        .card { box-shadow: none !important; margin: 0 !important; width: 100% !important; max-width: 100% !important; }
        button { display: none !important; }
    }
</style>

<?= $this->endSection() ?>
