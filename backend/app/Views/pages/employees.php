<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<div class="page-header animate-in">
    <div style="display: flex; justify-content: space-between; align-items: flex-end;">
        <div>
            <h2 class="page-title">Workforce Registry</h2>
            <p class="page-subtitle">Centralized directory of all active and inactive personal.</p>
        </div>
        <div style="display: flex; gap: 1rem; align-items: center;">
            <div style="text-align: right;">
                <div
                    style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; color: var(--color-text-dim);">
                    Total Entries</div>
                <div style="font-size: 1.5rem; font-weight: 800; color: var(--color-primary);">
                    <?= esc((string) ($total ?? 0)) ?></div>
            </div>
            <div
                style="width: 48px; height: 48px; border-radius: var(--radius-md); background: linear-gradient(135deg, var(--color-accent) 0%, #6366f1 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: 1.25rem;">
                <i class="fa-solid fa-users"></i>
            </div>
        </div>
    </div>
</div>

<!-- Filters & Search -->
<div class="card animate-in"
    style="animation-delay: 0.1s; background: linear-gradient(135deg, white 0%, var(--color-surface-muted) 100%);">
    <div class="card-body" style="padding: 1.5rem 2rem;">
        <form method="GET" action="<?= site_url('employees') ?>"
            style="display: flex; gap: 1.5rem; align-items: flex-end; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 180px;">
                <label
                    style="display: block; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; color: var(--color-text-dim); margin-bottom: 0.5rem;">Service
                    Status</label>
                <select name="status" class="form-input" style="padding: 0.75rem 1rem;">
                    <option value="">All Statuses</option>
                    <option value="active" <?= ($filters['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= ($filters['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive
                    </option>
                </select>
            </div>

            <div style="flex: 1; min-width: 180px;">
                <label
                    style="display: block; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; color: var(--color-text-dim); margin-bottom: 0.5rem;">Contract
                    Type</label>
                <select name="type" class="form-input" style="padding: 0.75rem 1rem;">
                    <option value="">All Types</option>
                    <option value="full_time" <?= ($filters['type'] ?? '') === 'full_time' ? 'selected' : '' ?>>Full Time
                    </option>
                    <option value="intern" <?= ($filters['type'] ?? '') === 'intern' ? 'selected' : '' ?>>Intern</option>
                </select>
            </div>

            <div style="flex: 2; min-width: 250px;">
                <label
                    style="display: block; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; color: var(--color-text-dim); margin-bottom: 0.5rem;">Identity
                    Search</label>
                <div style="position: relative;">
                    <input type="text" id="table-search" class="form-input" placeholder="Search by name or code..."
                        style="padding: 0.75rem 1rem 0.75rem 2.5rem;">
                    <i class="fa-solid fa-search"
                        style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--color-text-dim);"></i>
                </div>
            </div>

            <div style="display: flex; gap: 0.75rem;">
                <button type="submit" class="btn btn-primary" style="padding: 0.75rem 1.5rem; gap: 0.5rem;">
                    <i class="fa-solid fa-filter"></i>
                    Apply
                </button>
                <a href="<?= site_url('employees') ?>" class="btn btn-outline" style="padding: 0.75rem 1.5rem;">
                    <i class="fa-solid fa-rotate-left"></i>
                    Reset
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Employee Directory Table -->
<div class="card animate-in" style="animation-delay: 0.2s;">
    <div class="table-container" style="border-radius: var(--radius-lg); overflow: hidden;">
        <table id="data-table" style="border: none;">
            <thead>
                <tr
                    style="background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-light) 100%);">
                    <th style="color: white; border: none; padding: 1rem 1.5rem;">personal Name</th>
                    <th style="color: white; border: none; padding: 1rem 1.5rem;">Identity Code</th>
                    <th style="color: white; border: none; padding: 1rem 1.5rem;">Department</th>
                    <th style="color: white; border: none; padding: 1rem 1.5rem;">Designation</th>
                    <th style="color: white; border: none; padding: 1rem 1.5rem;">Category</th>
                    <th style="color: white; border: none; padding: 1rem 1.5rem;">Status</th>
                    <th style="color: white; border: none; padding: 1rem 1.5rem; text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($employees)): ?>
                    <?php foreach ($employees as $emp): ?>
                        <tr style="border-bottom: 1px solid var(--color-border); transition: all 0.2s;">
                            <td style="padding: 1.25rem 1.5rem;">
                                <div style="display: flex; align-items: center; gap: 1rem;">
                                    <div
                                        style="width: 40px; height: 40px; border-radius: var(--radius-md); background: linear-gradient(135deg, var(--color-accent) 0%, #6366f1 100%); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 0.875rem;">
                                        <?= strtoupper(substr($emp['name'], 0, 1)) ?>
                                    </div>
                                    <a href="<?= site_url('employees/' . esc($emp['emp_code'])) ?>"
                                        style="font-weight: 700; color: var(--color-primary); text-decoration: none; font-size: 0.9375rem;">
                                        <?= esc($emp['name']) ?>
                                    </a>
                                </div>
                            </td>
                            <td style="padding: 1.25rem 1.5rem;">
                                <code
                                    style="font-family: var(--font-mono); font-size: 0.8rem; background: var(--color-surface-muted); padding: 0.375rem 0.75rem; border-radius: var(--radius-sm); font-weight: 600; color: var(--color-primary);"><?= esc($emp['emp_code']) ?></code>
                            </td>
                            <td style="padding: 1.25rem 1.5rem; font-size: 0.875rem;"><?= esc($emp['department'] ?? '—') ?></td>
                            <td style="padding: 1.25rem 1.5rem; font-size: 0.875rem;"><?= esc($emp['designation'] ?? '—') ?>
                            </td>
                            <td style="padding: 1.25rem 1.5rem;">
                                <span class="badge badge--info"
                                    style="font-size: 0.7rem; padding: 0.375rem 0.875rem; font-weight: 700; letter-spacing: 0.05em;">
                                    <?= esc(str_replace('_', ' ', $emp['employee_type'] ?? 'full_time')) ?>
                                </span>
                            </td>
                            <td style="padding: 1.25rem 1.5rem;">
                                <span class="badge badge--<?= esc($emp['status'] === 'active' ? 'success' : 'danger') ?>"
                                    style="font-size: 0.7rem; padding: 0.375rem 0.875rem; font-weight: 700; letter-spacing: 0.05em;">
                                    <?= esc($emp['status'] ?? 'active') ?>
                                </span>
                            </td>
                            <td style="padding: 1.25rem 1.5rem; text-align: right;">
                                <a href="<?= site_url('employees/' . esc($emp['emp_code'])) ?>" class="btn btn-primary"
                                    style="padding: 0.5rem 1rem; font-size: 0.8rem; gap: 0.5rem;">
                                    <i class="fa-solid fa-folder-open"></i>
                                    Manage
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 6rem;">
                            <div style="display: flex; flex-direction: column; align-items: center; gap: 1.5rem;">
                                <div
                                    style="width: 96px; height: 96px; border-radius: 50%; background: var(--color-surface-muted); display: flex; align-items: center; justify-content: center;">
                                    <i class="fa-solid fa-user-slash"
                                        style="font-size: 2.5rem; color: var(--color-text-dim);"></i>
                                </div>
                                <div style="font-weight: 700; color: var(--color-text-dim); font-size: 1.125rem;">No
                                    matching records found</div>
                                <p style="font-size: 0.875rem; color: var(--color-text-dim);">Consider synchronizing with
                                    the primary identity provider or adjusting your filters.</p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>