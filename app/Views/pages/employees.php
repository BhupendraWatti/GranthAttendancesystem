<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<!-- Page Header -->
<div class="page-header">
    <div>
        <h2>Employees</h2>
        <p>Manage and view all employees in the system</p>
    </div>
    <div>
        <span class="badge badge--info" style="font-size:0.85rem; padding: 6px 14px;">
            <?= esc($total ?? 0) ?> total
        </span>
    </div>
</div>

<!-- Filters -->
<div class="card mb-3">
    <div class="card-body" style="padding: 14px 20px;">
        <form method="GET" action="<?= site_url('employees') ?>" class="form-inline">
            <div class="form-group">
                <label for="filter-status">Status</label>
                <select name="status" id="filter-status" class="form-control">
                    <option value="">All Status</option>
                    <option value="active" <?= ($filters['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= ($filters['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive
                    </option>
                </select>
            </div>
            <div class="form-group">
                <label for="filter-type">Type</label>
                <select name="type" id="filter-type" class="form-control">
                    <option value="">All Types</option>
                    <option value="full_time" <?= ($filters['type'] ?? '') === 'full_time' ? 'selected' : '' ?>>Full Time
                    </option>
                    <option value="intern" <?= ($filters['type'] ?? '') === 'intern' ? 'selected' : '' ?>>Intern</option>
                </select>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn--primary btn--sm">Filter</button>
                <a href="<?= site_url('employees') ?>" class="btn btn--outline btn--sm">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Employee Table -->
<div class="card">
    <div class="card-header">
        <h3>👥 Employee Directory</h3>
        <input type="text" id="table-search" class="form-control" placeholder="Search..."
            style="max-width:200px; padding: 6px 12px; font-size: 0.82rem;">
    </div>
    <div class="card-body p-0">
        <div class="table-wrapper">
            <table id="data-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Emp Code</th>
                        <th>Department</th>
                        <th>Designation</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($employees)): ?>
                        <?php foreach ($employees as $emp): ?>
                            <tr>
                                <td>
                                    <a href="<?= site_url('employees/' . esc($emp['emp_code'])) ?>" style="font-weight:600;">
                                        <?= esc($emp['name']) ?>
                                    </a>
                                </td>
                                <td class="font-mono"><?= esc($emp['emp_code']) ?></td>
                                <td><?= esc($emp['department'] ?? '—') ?></td>
                                <td><?= esc($emp['designation'] ?? '—') ?></td>
                                <td>
                                    <span class="badge badge--info">
                                        <?= esc(str_replace('_', ' ', $emp['employee_type'] ?? 'full_time')) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge--<?= esc($emp['status'] ?? 'active') ?>">
                                        <?= esc($emp['status'] ?? 'active') ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="<?= site_url('employees/' . esc($emp['emp_code'])) ?>" class="btn btn--outline btn--sm">View</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <div class="empty-icon">👥</div>
                                    <h4>No employees found</h4>
                                    <p>Run a sync to import employees from eTimeOffice.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?= $this->endSection() ?>