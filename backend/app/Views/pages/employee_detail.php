<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<?php
$emp = $employee ?? [];
$empName = $emp['name'] ?? 'personal';
$empCode = $emp['emp_code'] ?? 'N/A';
$names = explode(' ', trim($empName));
$initials = strtoupper(substr($names[0], 0, 1));
$empType = $emp['employee_type'] ?? 'full_time';
$empStatus = strtolower($emp['status'] ?? 'active');
$currentMonthName = date('F Y', mktime(0, 0, 0, $month ?? date('n'), 1, $year ?? date('Y')));

// Calculate attendance stats
$presentDays = 0;
$absentDays = 0;
$halfDays = 0;
$lateDays = 0;
$totalWorkMin = 0;
$totalDays = count($attendanceRecords ?? []);
foreach (($attendanceRecords ?? []) as $r) {
    if ($r['status'] === 'present')
        $presentDays++;
    elseif ($r['status'] === 'absent')
        $absentDays++;
    elseif ($r['status'] === 'half_day')
        $halfDays++;
    if (($r['late_minutes'] ?? 0) > 0)
        $lateDays++;
    $totalWorkMin += (int) ($r['work_minutes'] ?? 0);
}
$avgWorkHours = $presentDays > 0 ? round($totalWorkMin / $presentDays / 60, 1) : 0;
?>

<div class="page-header animate-in">
    <div
        style="display: flex; align-items: center; gap: 0.5rem; color: var(--color-text-dim); font-size: 0.8125rem; font-weight: 600; margin-bottom: 1rem;">
        <a href="<?= site_url('employees') ?>" style="color: inherit; text-decoration: none;">Workforce Registry</a>
        <i class="fa-solid fa-chevron-right" style="font-size: 0.7rem; opacity: 0.5;"></i>
        <span style="color: var(--color-accent);"><?= esc($empCode) ?></span>
    </div>
</div>

<div class="card animate-in" style="padding: 2.5rem; margin-bottom: 2rem;">
    <div style="display: flex; gap: 3rem; align-items: center;">
        <div
            style="width: 96px; height: 96px; border-radius: 12px; background: var(--color-primary); color: white; display: flex; align-items: center; justify-content: center; font-family: var(--font-display); font-size: 2.5rem; font-weight: 800; box-shadow: var(--shadow-md);">
            <?= esc($initials) ?>
        </div>

        <div style="flex: 1;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <h2 class="font-display" style="font-size: 2rem; margin-bottom: 0.25rem;"><?= esc($empName) ?></h2>
                    <p
                        style="color: var(--color-accent); font-weight: 700; font-size: 0.875rem; text-transform: uppercase; letter-spacing: 0.05em;">
                        <?= esc($emp['designation'] ?? 'personal Associate') ?>
                    </p>
                </div>
                <span class="badge badge--<?= $empStatus === 'active' ? 'success' : 'danger' ?>"
                    style="padding: 0.5rem 1rem;">
                    <?= strtoupper($empStatus) ?> SERVICE
                </span>
            </div>

            <div
                style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 2rem; margin-top: 2rem; border-top: 1px solid var(--color-border); padding-top: 1.5rem;">
                <div>
                    <div
                        style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; color: var(--color-text-dim); margin-bottom: 0.25rem;">
                        Department</div>
                    <div style="font-weight: 600; font-size: 0.9375rem;"><?= esc($emp['department'] ?? 'General Ops') ?>
                    </div>
                </div>
                <div>
                    <div
                        style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; color: var(--color-text-dim); margin-bottom: 0.25rem;">
                        Corporate Email</div>
                    <div style="font-weight: 600; font-size: 0.9375rem;"><?= esc($emp['email'] ?? '—') ?></div>
                </div>
                <div>
                    <div
                        style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; color: var(--color-text-dim); margin-bottom: 0.25rem;">
                        Contract Type</div>
                    <div style="font-weight: 600; font-size: 0.9375rem;">
                        <?= ucwords(str_replace('_', ' ', esc($empType))) ?></div>
                </div>
                <div>
                    <div
                        style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; color: var(--color-text-dim); margin-bottom: 0.25rem;">
                        Handover Date</div>
                    <div style="font-weight: 600; font-size: 0.9375rem;">
                        <?= $emp['created_at'] ? date('d M Y', strtotime($emp['created_at'])) : 'N/A' ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="animate-in" style="animation-delay: 0.1s;">
    <div style="display: flex; gap: 0.5rem; margin-bottom: -1px; position: relative; z-index: 10;">
        <button class="tab-trigger active" data-target="overview">Insights</button>
        <button class="tab-trigger" data-target="attendance">Attendance</button>
        <button class="tab-trigger" data-target="salary">Payroll</button>
        <button class="tab-trigger" data-target="documents">Registry Files</button>
    </div>

    <div class="card" style="border-top-left-radius: 0;">
        <!-- Tab: Insights -->
        <div class="tab-pane active" id="overview">
            <div class="card-body">
                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2.5rem;">
                    <div>
                        <div
                            style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                            <h4 class="font-display">Monthly Performance Snapshot</h4>
                            <span class="badge badge--info"><?= esc($currentMonthName) ?></span>
                        </div>

                        <div class="stats-grid">
                            <div class="stat-card" style="background: var(--color-surface-muted); border: none;">
                                <div class="stat-info">
                                    <span class="label">Presence Ratio</span>
                                    <span class="value"><?= $presentDays ?> <small
                                            style="font-size: 1rem; color: var(--color-text-dim);">/ <?= $totalDays ?>
                                            DAYS</small></span>
                                </div>
                            </div>
                            <div class="stat-card" style="background: var(--color-surface-muted); border: none;">
                                <div class="stat-info">
                                    <span class="label">Workload Avg.</span>
                                    <span class="value"><?= $avgWorkHours ?>h</span>
                                </div>
                            </div>
                            <div class="stat-card" style="background: var(--color-surface-muted); border: none;">
                                <div class="stat-info">
                                    <span class="label">Late Markers</span>
                                    <span class="value" style="color: var(--color-danger);"><?= $lateDays ?></span>
                                </div>
                            </div>
                        </div>

                        <div style="margin-top: 2rem;">
                            <h5
                                style="font-size: 0.875rem; font-weight: 700; color: var(--color-text-dim); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 1rem;">
                                Service Distribution</h5>
                            <?php $perc = $totalDays > 0 ? round($presentDays / $totalDays * 100) : 0; ?>
                            <div
                                style="height: 12px; background: var(--color-surface-muted); border-radius: 6px; overflow: hidden; display: flex;">
                                <div style="width: <?= $perc ?>%; background: var(--color-success);" title="Present">
                                </div>
                                <div style="width: <?= $totalDays > 0 ? round($halfDays / $totalDays * 100) : 0 ?>%; background: var(--color-warning);"
                                    title="Half Day"></div>
                                <div style="width: <?= $totalDays > 0 ? round($absentDays / $totalDays * 100) : 0 ?>%; background: var(--color-danger);"
                                    title="Absent"></div>
                            </div>
                            <div
                                style="display: flex; gap: 1.5rem; margin-top: 1rem; font-size: 0.75rem; font-weight: 600;">
                                <span style="display: flex; align-items: center; gap: 0.5rem;">
                                    <div
                                        style="width: 8px; height: 8px; border-radius: 2px; background: var(--color-success);">
                                    </div> PRESENT
                                </span>
                                <span style="display: flex; align-items: center; gap: 0.5rem;">
                                    <div
                                        style="width: 8px; height: 8px; border-radius: 2px; background: var(--color-warning);">
                                    </div> HALF DAY
                                </span>
                                <span style="display: flex; align-items: center; gap: 0.5rem;">
                                    <div
                                        style="width: 8px; height: 8px; border-radius: 2px; background: var(--color-danger);">
                                    </div> ABSENT
                                </span>
                            </div>
                        </div>
                    </div>

                    <div style="background: var(--color-surface-muted); border-radius: 12px; padding: 2rem;">
                        <h4 class="font-display" style="font-size: 1rem; margin-bottom: 1.5rem;">Accumulated Metrics
                        </h4>
                        <div style="display: flex; flex-direction: column; gap: 1.25rem;">
                            <div style="display: flex; justify-content: space-between;">
                                <span class="text-muted" style="font-size: 0.8125rem;">Total Logged Duration</span>
                                <span style="font-weight: 700;"><?= floor($totalWorkMin / 60) ?>h
                                    <?= $totalWorkMin % 60 ?>m</span>
                            </div>
                            <div style="display: flex; justify-content: space-between;">
                                <span class="text-muted" style="font-size: 0.8125rem;">Presence Markers</span>
                                <span style="font-weight: 700;"><?= $presentDays ?></span>
                            </div>
                            <div style="display: flex; justify-content: space-between;">
                                <span class="text-muted" style="font-size: 0.8125rem;">Partial Markers</span>
                                <span style="font-weight: 700;"><?= $halfDays ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab: Attendance -->
        <div class="tab-pane" id="attendance">
            <div class="card-body">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                    <h4 class="font-display">Service Logs</h4>
                    <form method="GET" action="<?= site_url('employees/' . esc($empCode)) ?>"
                        style="display: flex; gap: 0.5rem;">
                        <select name="month" class="form-input" style="padding: 0.4rem; width: auto;">
                            <?php for ($m = 1; $m <= 12; $m++): ?>
                                <option value="<?= $m ?>" <?= ($month ?? date('n')) == $m ? 'selected' : '' ?>>
                                    <?= date('F', mktime(0, 0, 0, $m, 1)) ?></option>
                            <?php endfor; ?>
                        </select>
                        <select name="year" class="form-input" style="padding: 0.4rem; width: auto;">
                            <?php for ($y = date('Y'); $y >= date('Y') - 2; $y--): ?>
                                <option value="<?= $y ?>" <?= ($year ?? date('Y')) == $y ? 'selected' : '' ?>><?= $y ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                        <button type="submit" class="btn btn-outline" style="padding: 0.4rem 1rem;">Filter</button>
                    </form>
                </div>

                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Service Date</th>
                                <th>First Handshake</th>
                                <th>Final Handshake</th>
                                <th>Logged Hours</th>
                                <th>Registry Status</th>
                                <th>Tardiness</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (($attendanceRecords ?? []) as $rec): ?>
                                <tr>
                                    <td style="font-weight: 600;"><?= date('d M, Y (D)', strtotime($rec['date'])) ?></td>
                                    <td style="font-family: var(--font-mono);">
                                        <?= $rec['first_in'] ? date('H:i', strtotime($rec['first_in'])) : '—' ?></td>
                                    <td style="font-family: var(--font-mono);">
                                        <?= $rec['last_out'] ? date('H:i', strtotime($rec['last_out'])) : '—' ?></td>
                                    <td style="font-weight: 700; color: var(--color-primary);">
                                        <?php $h = floor(($rec['work_minutes'] ?? 0) / 60);
                                        $mn = ($rec['work_minutes'] ?? 0) % 60;
                                        echo "{$h}h {$mn}m"; ?>
                                    </td>
                                    <td><span
                                            class="badge badge--<?= esc($rec['status']) ?>"><?= ucfirst(str_replace('_', ' ', esc($rec['status']))) ?></span>
                                    </td>
                                    <td>
                                        <?php if (($rec['late_minutes'] ?? 0) > 0): ?>
                                            <span class="badge badge--warning"
                                                style="font-size: 0.65rem;"><?= esc($rec['late_minutes']) ?> MIN DELAY</span>
                                        <?php else: ?>
                                            <span class="text-muted" style="font-size: 0.8125rem;">Handled.</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Tab: Payroll -->
        <div class="tab-pane" id="salary">
            <div class="card-body">
                <?php if (!empty($salarySummary)): ?>
                    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.5rem; margin-bottom: 2.5rem;">
                        <div class="stat-card" style="background: var(--color-surface-muted); border: none;">
                            <div class="stat-info">
                                <span class="label">Net Payable</span>
                                <span class="value"
                                    style="color: var(--color-accent);">₹<?= number_format($salarySummary['net_salary'] ?? 0, 2) ?></span>
                            </div>
                        </div>
                        <div class="stat-card" style="background: var(--color-surface-muted); border: none;">
                            <div class="stat-info">
                                <span class="label">Logged Work</span>
                                <span class="value"><?= floor(($salarySummary['total_work_minutes'] ?? 0) / 60) ?>h</span>
                            </div>
                        </div>
                        <div class="stat-card" style="background: var(--color-surface-muted); border: none;">
                            <div class="stat-info">
                                <span class="label">Handled Days</span>
                                <span
                                    class="value"><?= ($salarySummary['present_days'] ?? 0) + (($salarySummary['half_days'] ?? 0) * 0.5) ?></span>
                            </div>
                        </div>
                        <div class="stat-card" style="background: var(--color-surface-muted); border: none;">
                            <div class="stat-info">
                                <span class="label">Late Events</span>
                                <span class="value"><?= $salarySummary['late_count'] ?? 0 ?></span>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2.5rem;">
                    <div class="card">
                        <div class="card-header">
                            <h3>Contractual Configuration</h3>
                        </div>
                        <div class="card-body">
                            <form action="<?= site_url('employees/salary') ?>" method="POST">
                                <input type="hidden" name="emp_code" value="<?= esc($empCode) ?>">
                                <div class="form-group">
                                    <label class="form-label">Base Monthly Compensation (INR)</label>
                                    <input type="number" step="0.01" name="salary" class="form-input"
                                        value="<?= esc($emp['salary'] ?? '') ?>" placeholder="Enter amount">
                                </div>
                                <button type="submit" class="btn btn-primary" style="width: 100%;">Commit Financial
                                    Update</button>
                            </form>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h3>Registry Identity Mapping</h3>
                        </div>
                        <div class="card-body">
                            <form action="<?= site_url('employees/email') ?>" method="POST">
                                <input type="hidden" name="emp_code" value="<?= esc($empCode) ?>">
                                <div class="form-group">
                                    <label class="form-label">Authorized Communication Email</label>
                                    <input type="email" name="email" class="form-input"
                                        value="<?= esc($emp['email'] ?? '') ?>" placeholder="name@company.com">
                                </div>
                                <button type="submit" class="btn btn-outline" style="width: 100%;">Update Corporate
                                    Identity</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab: Documents -->
        <div class="tab-pane" id="documents">
            <div class="card-body">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                    <div>
                        <h4 class="font-display" style="font-size: 1.25rem; margin-bottom: 0.5rem;">Document Registry
                        </h4>
                        <p style="color: var(--color-text-dim); font-size: 0.875rem;">Centralized repository for
                            personal records and compliance files</p>
                    </div>
                    <button onclick="document.getElementById('upload-modal').classList.add('active')"
                        class="btn btn-primary" style="padding: 0.75rem 1.5rem; gap: 0.5rem;">
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                        Upload Document
                    </button>
                </div>

                <div class="table-container"
                    style="border-radius: var(--radius-lg); overflow: hidden; border: 1px solid var(--color-border);">
                    <table style="border: none;">
                        <thead>
                            <tr
                                style="background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-light) 100%);">
                                <th style="color: white; border: none; padding: 1rem 1.5rem;">Registry File</th>
                                <th style="color: white; border: none; padding: 1rem 1.5rem;">Category</th>
                                <th style="color: white; border: none; padding: 1rem 1.5rem;">Release</th>
                                <th style="color: white; border: none; padding: 1rem 1.5rem;">Handover Date</th>
                                <th style="color: white; border: none; padding: 1rem 1.5rem; text-align: right;">Action
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($documents)): ?>
                                <?php foreach ($documents as $doc): ?>
                                    <tr style="border-bottom: 1px solid var(--color-border); transition: all 0.2s;">
                                        <td style="padding: 1.25rem 1.5rem;">
                                            <div style="display: flex; align-items: center; gap: 1rem;">
                                                <div
                                                    style="width: 48px; height: 48px; border-radius: var(--radius-md); background: linear-gradient(135deg, var(--color-accent) 0%, #6366f1 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: 1.25rem;">
                                                    <i class="fa-solid fa-file-pdf"></i>
                                                </div>
                                                <div>
                                                    <div
                                                        style="font-weight: 700; color: var(--color-primary); font-size: 0.9375rem;">
                                                        <?= esc($doc['title']) ?></div>
                                                    <div
                                                        style="font-size: 0.75rem; color: var(--color-text-dim); margin-top: 0.25rem;">
                                                        <i class="fa-solid fa-file-lines" style="margin-right: 0.25rem;"></i>
                                                        <?= strtoupper(pathinfo($doc['title'], PATHINFO_EXTENSION)) ?> FILE
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td style="padding: 1.25rem 1.5rem;">
                                            <span class="badge badge--info"
                                                style="font-size: 0.7rem; padding: 0.375rem 0.875rem; font-weight: 700; letter-spacing: 0.05em;">
                                                <?= strtoupper(esc($doc['document_type'])) ?>
                                            </span>
                                        </td>
                                        <td style="padding: 1.25rem 1.5rem;">
                                            <div
                                                style="display: inline-flex; align-items: center; gap: 0.5rem; background: var(--color-surface-muted); padding: 0.375rem 0.75rem; border-radius: var(--radius-sm); font-family: var(--font-mono); font-size: 0.8rem; font-weight: 600; color: var(--color-primary);">
                                                <i class="fa-solid fa-code-branch" style="font-size: 0.7rem;"></i>
                                                v<?= esc($doc['version']) ?>
                                            </div>
                                        </td>
                                        <td style="padding: 1.25rem 1.5rem; font-size: 0.875rem; color: var(--color-text-dim);">
                                            <i class="fa-regular fa-calendar" style="margin-right: 0.5rem;"></i>
                                            <?= date('d M Y', strtotime($doc['created_at'])) ?>
                                        </td>
                                        <td style="padding: 1.25rem 1.5rem; text-align: right;">
                                            <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                                                <a href="<?= site_url('documents/download/employee/' . $doc['id']) ?>"
                                                    class="btn btn-outline"
                                                    style="padding: 0.5rem 1rem; font-size: 0.8rem; gap: 0.5rem;"
                                                    title="Download from Archive">
                                                    <i class="fa-solid fa-download"></i>
                                                    Retrieve
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" style="text-align: center; padding: 4rem 2rem;">
                                        <div
                                            style="display: flex; flex-direction: column; align-items: center; gap: 1.5rem;">
                                            <div
                                                style="width: 80px; height: 80px; border-radius: 50%; background: var(--color-surface-muted); display: flex; align-items: center; justify-content: center;">
                                                <i class="fa-solid fa-folder-open"
                                                    style="font-size: 2rem; color: var(--color-text-dim);"></i>
                                            </div>
                                            <div>
                                                <div
                                                    style="font-weight: 700; color: var(--color-text-dim); font-size: 1.125rem; margin-bottom: 0.5rem;">
                                                    No Registry Files</div>
                                                <p style="font-size: 0.875rem; color: var(--color-text-dim);">This personal
                                                    profile has no associated documents in the archive.</p>
                                            </div>
                                            <button
                                                onclick="document.getElementById('upload-modal').classList.add('active')"
                                                class="btn btn-primary" style="padding: 0.75rem 1.5rem;">
                                                <i class="fa-solid fa-plus" style="margin-right: 0.5rem;"></i>
                                                Upload First Document
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Upload Modal -->
<div id="upload-modal" class="modal-overlay">
    <div class="modal-container">
        <div class="modal-header">
            <div>
                <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--color-primary);">Upload Document</h3>
                <p style="font-size: 0.875rem; color: var(--color-text-dim); margin-top: 0.25rem;">Add new files to
                    <?= esc($empName) ?>'s registry</p>
            </div>
            <button onclick="document.getElementById('upload-modal').classList.remove('active')" class="modal-close">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div class="modal-body">
            <form action="<?= site_url('documents/upload/employee') ?>" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="emp_codes[]" value="<?= esc($empCode) ?>">

                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label class="form-label">Document Title</label>
                    <input type="text" name="title" class="form-input" placeholder="e.g. Performance Review 2024"
                        required>
                </div>

                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label class="form-label">Document Category</label>
                    <select name="document_type" class="form-input" required>
                        <option value="joining">Joining Letter</option>
                        <option value="offer">Offer Letter</option>
                        <option value="incentive">Incentive Letter</option>
                        <option value="id_proof">ID Proof</option>
                        <option value="performance">Performance Review</option>
                        <option value="contract">Contract Agreement</option>
                        <option value="other">Other</option>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 2rem;">
                    <label class="form-label">File Upload</label>
                    <div class="premium-upload-area" id="drop-area">
                        <div class="upload-icon">
                            <i class="fa-solid fa-cloud-arrow-up"></i>
                        </div>
                        <div class="upload-text">
                            <div style="font-weight: 600; color: var(--color-primary); margin-bottom: 0.5rem;">Drag &
                                drop files here</div>
                            <div style="font-size: 0.875rem; color: var(--color-text-dim);">or click to browse from your
                                device</div>
                        </div>
                        <div class="upload-hint">
                            <span>PDF, DOCX, JPG, PNG</span>
                            <span>Max 5MB</span>
                        </div>
                        <input type="file" name="document" id="document" class="file-input" required>
                    </div>
                    <div id="file-name" class="file-name-display"></div>
                </div>

                <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                    <button type="button" onclick="document.getElementById('upload-modal').classList.remove('active')"
                        class="btn btn-outline" style="padding: 0.75rem 1.5rem;">Cancel</button>
                    <button type="submit" class="btn btn-primary" style="padding: 0.75rem 1.5rem; gap: 0.5rem;">
                        <i class="fa-solid fa-check-circle"></i>
                        Upload to Registry
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .tab-trigger {
        padding: 0.875rem 2rem;
        font-weight: 700;
        font-size: 0.875rem;
        color: var(--color-text-dim);
        border: 1px solid transparent;
        background: none;
        cursor: pointer;
        transition: all 0.2s ease;
        border-radius: 8px 8px 0 0;
        font-family: var(--font-display);
    }

    .tab-trigger:hover {
        color: var(--color-accent);
    }

    .tab-trigger.active {
        background: white;
        border: 1px solid var(--color-border);
        border-bottom-color: white;
        color: var(--color-accent);
    }

    .tab-pane {
        display: none;
    }

    .tab-pane.active {
        display: block;
    }

    /* Modal Styles */
    .modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.6);
        backdrop-filter: blur(8px);
        z-index: 2000;
        display: none;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .modal-overlay.active {
        display: flex;
        opacity: 1;
    }

    .modal-container {
        background: white;
        border-radius: var(--radius-xl);
        width: 100%;
        max-width: 500px;
        box-shadow: var(--shadow-xl);
        transform: scale(0.95);
        transition: transform 0.3s ease;
    }

    .modal-overlay.active .modal-container {
        transform: scale(1);
    }

    .modal-header {
        padding: 1.5rem 2rem;
        border-bottom: 1px solid var(--color-border);
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
    }

    .modal-close {
        width: 32px;
        height: 32px;
        border-radius: var(--radius-sm);
        border: none;
        background: var(--color-surface-muted);
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--color-text-dim);
        transition: all 0.2s;
    }

    .modal-close:hover {
        background: var(--color-border);
        color: var(--color-primary);
    }

    .modal-body {
        padding: 2rem;
    }

    /* Premium Upload Area */
    .premium-upload-area {
        position: relative;
        border: 2px dashed var(--color-border);
        border-radius: var(--radius-lg);
        padding: 2.5rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        background: linear-gradient(135deg, var(--color-surface-muted) 0%, white 100%);
    }

    .premium-upload-area:hover {
        border-color: var(--color-accent);
        background: linear-gradient(135deg, #e0e7ff 0%, white 100%);
        transform: translateY(-2px);
    }

    .premium-upload-area.dragover {
        border-color: var(--color-accent);
        background: linear-gradient(135deg, #c7d2fe 0%, #e0e7ff 100%);
    }

    .upload-icon {
        width: 64px;
        height: 64px;
        margin: 0 auto 1.5rem;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--color-accent) 0%, #6366f1 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.5rem;
    }

    .upload-hint {
        display: flex;
        gap: 1rem;
        justify-content: center;
        margin-top: 1rem;
        font-size: 0.75rem;
        color: var(--color-text-dim);
    }

    .upload-hint span {
        background: var(--color-surface-muted);
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-weight: 600;
    }

    .file-input {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        cursor: pointer;
    }

    .file-name-display {
        margin-top: 1rem;
        padding: 0.75rem 1rem;
        background: linear-gradient(135deg, #dcfce7 0%, #d1fae5 100%);
        border: 1px solid #86efac;
        border-radius: var(--radius-md);
        font-size: 0.875rem;
        font-weight: 600;
        color: #059669;
        display: none;
        align-items: center;
        gap: 0.5rem;
    }

    .file-name-display.active {
        display: flex;
    }

    .file-name-display i {
        font-size: 1rem;
    }

    /* Form Styles */
    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-label {
        display: block;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--color-text-dim);
        margin-bottom: 0.5rem;
    }

    .form-input {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 1px solid var(--color-border);
        border-radius: var(--radius-md);
        font-size: 0.875rem;
        font-family: var(--font-body);
        transition: all 0.2s;
        background: white;
    }

    .form-input:focus {
        outline: none;
        border-color: var(--color-accent);
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
    }

    .form-input::placeholder {
        color: var(--color-text-dim);
    }
</style>

<script>
    document.querySelectorAll('.tab-trigger').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.tab-trigger, .tab-pane').forEach(el => el.classList.remove('active'));
            btn.classList.add('active');
            document.getElementById(btn.dataset.target).classList.add('active');
        });
    });

    // File Upload UX
    const fileInput = document.getElementById('document');
    const fileName = document.getElementById('file-name');
    const dropArea = document.getElementById('drop-area');

    fileInput.addEventListener('change', function () {
        if (this.files && this.files.length > 0) {
            fileName.innerHTML = '<i class="fa-solid fa-check-circle"></i> ' + this.files[0].name;
            fileName.classList.add('active');
            dropArea.style.borderColor = 'var(--color-success)';
        }
    });

    ['dragenter', 'dragover'].forEach(eventName => {
        dropArea.addEventListener(eventName, e => {
            e.preventDefault();
            dropArea.classList.add('dragover');
        });
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropArea.addEventListener(eventName, e => {
            e.preventDefault();
            dropArea.classList.remove('dragover');
        });
    });
</script>

<?= $this->endSection() ?>