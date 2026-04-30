<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<?php
    $emp = $employee ?? [];
    $empName = $emp['name'] ?? 'Unknown';
    $empCode = $emp['emp_code'] ?? '';
    $initials = strtoupper(substr($empName, 0, 1) . (strpos($empName, ' ') !== false ? substr($empName, strpos($empName, ' ') + 1, 1) : substr($empName, 1, 1)));
    $empType = $emp['employee_type'] ?? 'full_time';
    $empStatus = $emp['status'] ?? 'active';
    $currentMonthName = date('F Y', mktime(0, 0, 0, $month ?? date('n'), 1, $year ?? date('Y')));

    // Calculate attendance stats
    $presentDays = 0; $absentDays = 0; $halfDays = 0; $lateDays = 0;
    $totalWorkMin = 0; $totalDays = count($attendanceRecords ?? []);
    foreach (($attendanceRecords ?? []) as $r) {
        if ($r['status'] === 'present') $presentDays++;
        elseif ($r['status'] === 'absent') $absentDays++;
        elseif ($r['status'] === 'half_day') $halfDays++;
        if (($r['late_minutes'] ?? 0) > 0) $lateDays++;
        $totalWorkMin += (int)($r['work_minutes'] ?? 0);
    }
    $avgWorkHours = $presentDays > 0 ? round($totalWorkMin / $presentDays / 60, 1) : 0;
?>

<!-- Breadcrumb -->
<div class="emp-breadcrumb">
    <a href="<?= site_url('employees') ?>">Employees</a>
    <span class="sep">›</span>
    <span><?= esc($empCode) ?></span>
</div>

<!-- Profile Header Card -->
<div class="card emp-profile-card mb-3">
    <div class="emp-profile-header">
        <div class="emp-profile-left">
            <div class="emp-avatar"><?= esc($initials) ?></div>
            <div class="emp-profile-info">
                <div class="emp-name-row">
                    <h2><?= esc($empName) ?></h2>
                    <span class="badge badge--<?= esc($empStatus) ?>"><?= ucfirst(esc($empStatus)) ?></span>
                </div>
                <p class="emp-role"><?= esc($emp['designation'] ?? ucwords(str_replace('_', ' ', $empType))) ?> · <?= esc($empCode) ?></p>
                <div class="emp-meta-row">
                    <div class="emp-meta-item">
                        <span class="emp-meta-label">Department</span>
                        <span class="emp-meta-value"><?= esc($emp['department'] ?? 'General') ?></span>
                    </div>
                    <div class="emp-meta-item">
                        <span class="emp-meta-label">Email</span>
                        <span class="emp-meta-value"><?= esc($emp['email'] ?? '—') ?></span>
                    </div>
                    <div class="emp-meta-item">
                        <span class="emp-meta-label">Type</span>
                        <span class="emp-meta-value"><?= ucwords(str_replace('_', ' ', esc($empType))) ?></span>
                    </div>
                    <div class="emp-meta-item">
                        <span class="emp-meta-label">Joined</span>
                        <span class="emp-meta-value"><?= $emp['created_at'] ? date('M d, Y', strtotime($emp['created_at'])) : 'N/A' ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabs Navigation -->
<div class="card">
    <div class="card-body" style="padding-bottom:0;">
        <div class="tabs">
            <button class="tab-btn active" data-tab="tab-overview">📊 Overview</button>
            <button class="tab-btn" data-tab="tab-attendance">📋 Attendance</button>
            <button class="tab-btn" data-tab="tab-salary">💰 Salary</button>
            <button class="tab-btn" data-tab="tab-documents">📄 Documents</button>
        </div>
    </div>

    <!-- Tab: Overview -->
    <div class="tab-content active" id="tab-overview">
        <div class="card-body">
            <div class="emp-overview-grid grid grid-cols-1 md:grid-cols-[2fr_1fr] gap-6">
                <!-- Current Month Statistics -->
                <div class="emp-stats-card">
                    <div class="emp-stats-header">
                        <h4>Current Month Statistics</h4>
                        <span class="emp-month-badge"><?= esc($currentMonthName) ?></span>
                    </div>
                    <div class="emp-stats-grid">
                        <div class="emp-stat-box emp-stat-box--present">
                            <span class="emp-stat-dot" style="background:var(--success);"></span>
                            <span class="emp-stat-label">Present Days</span>
                            <div class="emp-stat-big"><?= $presentDays ?><span class="emp-stat-total">/<?= $totalDays ?></span></div>
                            <div class="emp-stat-bar"><div class="emp-stat-bar-fill" style="width:<?= $totalDays > 0 ? round($presentDays/$totalDays*100) : 0 ?>%;background:var(--success);"></div></div>
                        </div>
                        <div class="emp-stat-box emp-stat-box--hours">
                            <span class="emp-stat-dot" style="background:var(--primary);"></span>
                            <span class="emp-stat-label">Avg. Work Hours</span>
                            <div class="emp-stat-big"><?= $avgWorkHours ?><span class="emp-stat-unit">hrs</span></div>
                        </div>
                        <div class="emp-stat-box emp-stat-box--late">
                            <span class="emp-stat-dot" style="background:var(--danger);"></span>
                            <span class="emp-stat-label">Late Marks</span>
                            <div class="emp-stat-big"><?= $lateDays ?></div>
                        </div>
                    </div>
                </div>

                <!-- Quick Summary -->
                <div class="emp-quick-summary">
                    <h4>Attendance Breakdown</h4>
                    <div class="emp-summary-items">
                        <div class="emp-summary-row">
                            <span class="emp-summary-icon" style="background:var(--success-light);color:var(--success-dark);">✓</span>
                            <span class="emp-summary-label">Present</span>
                            <span class="emp-summary-value font-bold"><?= $presentDays ?> days</span>
                        </div>
                        <div class="emp-summary-row">
                            <span class="emp-summary-icon" style="background:var(--warning-light);color:var(--warning-dark);">½</span>
                            <span class="emp-summary-label">Half Day</span>
                            <span class="emp-summary-value font-bold"><?= $halfDays ?> days</span>
                        </div>
                        <div class="emp-summary-row">
                            <span class="emp-summary-icon" style="background:var(--danger-light);color:var(--danger);">✕</span>
                            <span class="emp-summary-label">Absent</span>
                            <span class="emp-summary-value font-bold"><?= $absentDays ?> days</span>
                        </div>
                        <div class="emp-summary-row">
                            <span class="emp-summary-icon" style="background:var(--info-light);color:var(--info);">⏱</span>
                            <span class="emp-summary-label">Total Work</span>
                            <span class="emp-summary-value font-bold font-mono"><?= floor($totalWorkMin/60) ?>h <?= $totalWorkMin%60 ?>m</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab: Attendance History -->
    <div class="tab-content" id="tab-attendance">
        <div class="card-body">
            <!-- Month/Year Filter -->
            <form method="GET" action="<?= site_url('employees/' . esc($empCode)) ?>" class="form-inline mb-3">
                <div class="form-group">
                    <label for="month">Month</label>
                    <select name="month" id="month" class="form-control">
                        <?php for ($m = 1; $m <= 12; $m++): ?>
                            <option value="<?= $m ?>" <?= ($month ?? date('n')) == $m ? 'selected' : '' ?>>
                                <?= date('F', mktime(0, 0, 0, $m, 1)) ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="year">Year</label>
                    <select name="year" id="year" class="form-control">
                        <?php for ($y = date('Y'); $y >= date('Y') - 2; $y--): ?>
                            <option value="<?= $y ?>" <?= ($year ?? date('Y')) == $y ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn--primary btn--sm">View</button>
                </div>
            </form>

            <!-- Attendance Table -->
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>First In</th>
                            <th>Last Out</th>
                            <th>Work Hours</th>
                            <th>Status</th>
                            <th>Late</th>
                            <th>Punches</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($attendanceRecords)): ?>
                            <?php foreach ($attendanceRecords as $rec): ?>
                            <tr>
                                <td class="font-mono"><?= date('d M (D)', strtotime($rec['date'])) ?></td>
                                <td><?= $rec['first_in'] ? date('h:i A', strtotime($rec['first_in'])) : '<span class="text-muted">—</span>' ?></td>
                                <td><?= $rec['last_out'] ? date('h:i A', strtotime($rec['last_out'])) : '<span class="text-muted">—</span>' ?></td>
                                <td class="font-mono"><?php $h=floor(($rec['work_minutes']??0)/60);$mn=($rec['work_minutes']??0)%60;echo "{$h}h {$mn}m"; ?></td>
                                <td><span class="badge badge--<?= esc($rec['status']) ?>"><?= ucwords(str_replace('_',' ',esc($rec['status']))) ?></span></td>
                                <td>
                                    <?php if (($rec['late_minutes'] ?? 0) > 0): ?>
                                        <span class="badge badge--late"><?= esc($rec['late_minutes']) ?> min</span>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center"><?= esc($rec['punch_count'] ?? 0) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7">
                                    <div class="empty-state">
                                        <div class="empty-icon">📋</div>
                                        <h4>No attendance records</h4>
                                        <p>No data for the selected month. Try running a sync first.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Tab: Salary -->
    <div class="tab-content" id="tab-salary">
        <div class="card-body">
            <?php if (!empty($salarySummary)): ?>
                <?php $sal = $salarySummary; ?>
                <div class="stats-grid grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="stat-card stat-card--success">
                        <div class="stat-info">
                            <div class="stat-label">Present Days</div>
                            <div class="stat-value"><?= esc($sal['present_days'] ?? 0) ?></div>
                            <div class="stat-sub">of <?= $totalDays ?> total days</div>
                        </div>
                        <div class="stat-icon">✅</div>
                    </div>
                    <div class="stat-card stat-card--warning">
                        <div class="stat-info">
                            <div class="stat-label">Half Days</div>
                            <div class="stat-value"><?= esc($sal['half_days'] ?? 0) ?></div>
                        </div>
                        <div class="stat-icon">⚡</div>
                    </div>
                    <div class="stat-card stat-card--danger">
                        <div class="stat-info">
                            <div class="stat-label">Absent Days</div>
                            <div class="stat-value"><?= esc($sal['absent_days'] ?? 0) ?></div>
                        </div>
                        <div class="stat-icon">❌</div>
                    </div>
                    <div class="stat-card stat-card--info">
                        <div class="stat-info">
                            <div class="stat-label">Late Count</div>
                            <div class="stat-value"><?= esc($sal['late_count'] ?? 0) ?></div>
                        </div>
                        <div class="stat-icon">⏰</div>
                    </div>
                </div>

                <div class="card mt-2">
                    <div class="card-body">
                        <table>
                            <tr><td><strong>Total Work Hours</strong></td><td class="font-mono"><?php $m=$sal['total_work_minutes']??0;echo floor($m/60).'h '.($m%60).'m'; ?></td></tr>
                            <tr><td><strong>Total Late Minutes</strong></td><td class="font-mono"><?= esc($sal['total_late_minutes'] ?? 0) ?> min</td></tr>
                            <tr><td><strong>Expected Working Days</strong></td><td class="font-mono"><?= esc(env('WORKING_DAYS_PER_MONTH', 23)) ?></td></tr>
                            <tr><td><strong>Effective Days</strong></td><td class="font-mono"><?= ($sal['present_days'] ?? 0) + (($sal['half_days'] ?? 0) * 0.5) ?> days</td></tr>
                        </table>
                    </div>
                </div>

                <div class="mt-3">
                    <a href="<?= site_url('payslip/' . esc($empCode) . '?month=' . $month . '&year=' . $year) ?>" class="btn btn--primary">
                        📄 View Payslip
                    </a>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-icon">💰</div>
                    <h4>No salary data</h4>
                    <p>Attendance records are needed to compute salary summary. Run a sync first.</p>
                </div>
            <?php endif; ?>

            <!-- Salary Configuration Form -->
            <div class="card mt-3">
                <div class="card-header">
                    <h4>Configure Employee Salary</h4>
                </div>
                <div class="card-body">
                    <?php if (empty($employee['salary'])): ?>
                        <div class="alert alert--warning mb-2">
                            ⚠️ This employee does not have a base monthly salary configured. It is falling back to the system default of <?= esc(env('DEFAULT_MONTHLY_SALARY', 25000)) ?>.
                        </div>
                    <?php endif; ?>
                    
                    <form action="<?= site_url('employees/salary') ?>" method="POST" class="form-inline">
                        <input type="hidden" name="emp_code" value="<?= esc($empCode) ?>">
                        
                        <div class="form-group mb-0">
                            <label for="salary">Base Monthly Salary (₹)</label>
                            <input 
                                type="number" 
                                step="0.01" 
                                name="salary" 
                                id="salary" 
                                class="form-control" 
                                value="<?= esc($employee['salary'] ?? '') ?>" 
                                placeholder="Enter base monthly salary"
                                required
                            >
                        </div>
                        <div class="form-group mb-0" style="align-self: flex-end;">
                            <button type="submit" class="btn btn--primary">Update Salary</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Email Mapping Form -->
            <div class="card mt-3">
                <div class="card-header">
                    <h4>Configure Employee Email (Login Mapping)</h4>
                </div>
                <div class="card-body">
                    <div class="alert alert--info mb-2">
                        Email is not provided by eTimeOffice and is stored internally for employee OTP login.
                    </div>
                    <form action="<?= site_url('employees/email') ?>" method="POST" class="form-inline">
                        <input type="hidden" name="emp_code" value="<?= esc($empCode) ?>">
                        <div class="form-group mb-0">
                            <label for="email">Email</label>
                            <input
                                type="email"
                                name="email"
                                id="email"
                                class="form-control"
                                value="<?= esc($employee['email'] ?? '') ?>"
                                placeholder="employee@company.com"
                            >
                        </div>
                        <div class="form-group mb-0" style="align-self: flex-end;">
                            <button type="submit" class="btn btn--primary">Update Email</button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

    <!-- Tab: Documents -->
    <div class="tab-content" id="tab-documents">
        <div class="card-body">
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Type</th>
                            <th>Version</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($documents)): ?>
                            <?php foreach ($documents as $doc): ?>
                                <tr>
                                    <td><strong><?= esc($doc['title']) ?></strong></td>
                                    <td><span class="badge badge--info"><?= esc(ucfirst($doc['document_type'])) ?></span></td>
                                    <td>v<?= esc($doc['version']) ?></td>
                                    <td><?= date('d M Y', strtotime($doc['created_at'])) ?></td>
                                    <td>
                                        <a href="<?= site_url('documents/download/employee/' . $doc['id']) ?>" class="btn btn--sm btn--outline">Download</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-8">
                                    No documents found for this employee.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
