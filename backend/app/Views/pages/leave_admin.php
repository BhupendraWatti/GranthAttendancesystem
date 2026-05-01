<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<div class="page-header animate-in">
    <div style="display: flex; justify-content: space-between; align-items: flex-end;">
        <div>
            <h2 class="page-title">Absence Request Management</h2>
            <p class="page-subtitle">Review and approve employee leave applications with detailed oversight.</p>
        </div>
        <div style="display: flex; gap: 1rem; align-items: center;">
            <div style="text-align: right;">
                <div style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; color: var(--color-text-dim);">Pending Requests</div>
                <div style="font-size: 1.5rem; font-weight: 800; color: var(--color-warning);"><?= count($pending ?? []) ?></div>
            </div>
            <div style="width: 48px; height: 48px; border-radius: var(--radius-md); background: linear-gradient(135deg, var(--color-warning) 0%, #FBBF24 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: 1.25rem;">
                <i class="fa-solid fa-clock"></i>
            </div>
        </div>
    </div>
</div>

<!-- Pending Requests -->
<div class="card animate-in" style="animation-delay: 0.1s; background: linear-gradient(135deg, white 0%, var(--color-surface-muted) 100%);">
    <div class="card-header">
        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <div style="width: 40px; height: 40px; border-radius: var(--radius-md); background: linear-gradient(135deg, var(--color-warning) 0%, #FBBF24 100%); display: flex; align-items: center; justify-content: center; color: white;">
                <i class="fa-solid fa-hourglass-half"></i>
            </div>
            <div>
                <h3 style="margin: 0;">Pending Approval</h3>
                <div style="font-size: 0.75rem; color: var(--color-text-dim);"><?= count($pending ?? []) ?> requests awaiting review</div>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-wrapper" style="border-radius: var(--radius-lg); overflow: hidden;">
            <table style="border: none;">
                <thead>
                    <tr style="background: linear-gradient(135deg, var(--color-warning) 0%, #FBBF24 100%);">
                        <th style="color: white; border: none; padding: 1rem 1.5rem;">Employee</th>
                        <th style="color: white; border: none; padding: 1rem 1.5rem;">Leave Type</th>
                        <th style="color: white; border: none; padding: 1rem 1.5rem;">Duration</th>
                        <th style="color: white; border: none; padding: 1rem 1.5rem;">Reason</th>
                        <th style="color: white; border: none; padding: 1rem 1.5rem; text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($pending)): ?>
                        <?php foreach ($pending as $p): ?>
                            <tr style="border-bottom: 1px solid var(--color-border); transition: all 0.2s;">
                                <td style="padding: 1.25rem 1.5rem;">
                                    <div style="display: flex; align-items: center; gap: 1rem;">
                                        <div style="width: 40px; height: 40px; border-radius: var(--radius-md); background: linear-gradient(135deg, var(--color-accent) 0%, #6366f1 100%); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 0.875rem;">
                                            <?= strtoupper(substr($p['employee_name'], 0, 1)) ?>
                                        </div>
                                        <div>
                                            <div style="font-weight: 700; color: var(--color-primary); font-size: 0.9375rem;"><?= esc($p['employee_name']) ?></div>
                                            <div style="font-size: 0.75rem; color: var(--color-text-dim);"><?= esc($p['emp_code']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td style="padding: 1.25rem 1.5rem;">
                                    <span class="badge badge--info" style="font-size: 0.7rem; padding: 0.375rem 0.875rem; font-weight: 700; letter-spacing: 0.05em;">
                                        <?= esc(ucwords(str_replace('_', ' ', $p['leave_type']))) ?>
                                    </span>
                                </td>
                                <td style="padding: 1.25rem 1.5rem;">
                                    <div style="font-weight: 600; color: var(--color-primary); font-size: 0.9375rem;">
                                        <?= date('d M', strtotime($p['from_date'])) ?> - <?= date('d M Y', strtotime($p['to_date'])) ?>
                                    </div>
                                    <div style="font-size: 0.75rem; color: var(--color-text-dim); margin-top: 0.25rem;">
                                        <?= (strtotime($p['to_date']) - strtotime($p['from_date'])) / 86400 + 1 ?> day(s)
                                    </div>
                                </td>
                                <td style="padding: 1.25rem 1.5rem;">
                                    <div style="max-width: 250px; font-size: 0.875rem; color: var(--color-text-main); line-height: 1.5;" title="<?= esc($p['reason']) ?>">
                                        <?= esc($p['reason']) ?>
                                    </div>
                                </td>
                                <td style="padding: 1.25rem 1.5rem; text-align: right;">
                                    <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                                        <button class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.8rem; gap: 0.5rem; background: linear-gradient(135deg, var(--color-success) 0%, #34D399 100%);" onclick="handleAction('approve', <?= $p['id'] ?>)">
                                            <i class="fa-solid fa-check"></i>
                                            Approve
                                        </button>
                                        <button class="btn btn-outline" style="padding: 0.5rem 1rem; font-size: 0.8rem; gap: 0.5rem; color: var(--color-danger); border-color: var(--color-border);" onclick="handleAction('reject', <?= $p['id'] ?>)">
                                            <i class="fa-solid fa-xmark"></i>
                                            Reject
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 4rem;">
                                <div class="empty-state" style="display: flex; flex-direction: column; align-items: center; gap: 1.5rem;">
                                    <div style="width: 80px; height: 80px; border-radius: 50%; background: var(--color-surface-muted); display: flex; align-items: center; justify-content: center;">
                                        <i class="fa-solid fa-check-circle" style="font-size: 2rem; color: var(--color-success);"></i>
                                    </div>
                                    <div>
                                        <div style="font-weight: 700; color: var(--color-text-dim); font-size: 1.125rem; margin-bottom: 0.5rem;">All caught up!</div>
                                        <p style="font-size: 0.875rem; color: var(--color-text-dim);">No pending leave requests to review at this time.</p>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- History -->
<div class="card animate-in" style="animation-delay: 0.2s;">
    <div class="card-header">
        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <div style="width: 40px; height: 40px; border-radius: var(--radius-md); background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-light) 100%); display: flex; align-items: center; justify-content: center; color: white;">
                <i class="fa-solid fa-clipboard-list"></i>
            </div>
            <div>
                <h3 style="margin: 0;">Request History</h3>
                <div style="font-size: 0.75rem; color: var(--color-text-dim);"><?= count($history ?? []) ?> processed requests</div>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-wrapper" style="border-radius: var(--radius-lg); overflow: hidden;">
            <table style="border: none;">
                <thead>
                    <tr style="background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-light) 100%);">
                        <th style="color: white; border: none; padding: 1rem 1.5rem;">Employee</th>
                        <th style="color: white; border: none; padding: 1rem 1.5rem;">Duration</th>
                        <th style="color: white; border: none; padding: 1rem 1.5rem;">Status</th>
                        <th style="color: white; border: none; padding: 1rem 1.5rem;">Admin Comment</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($history)): ?>
                        <?php foreach ($history as $h): ?>
                            <tr style="border-bottom: 1px solid var(--color-border); transition: all 0.2s;">
                                <td style="padding: 1.25rem 1.5rem;">
                                    <div style="display: flex; align-items: center; gap: 1rem;">
                                        <div style="width: 40px; height: 40px; border-radius: var(--radius-md); background: linear-gradient(135deg, var(--color-accent) 0%, #6366f1 100%); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 0.875rem;">
                                            <?= strtoupper(substr($h['employee_name'], 0, 1)) ?>
                                        </div>
                                        <div>
                                            <div style="font-weight: 700; color: var(--color-primary); font-size: 0.9375rem;"><?= esc($h['employee_name']) ?></div>
                                            <div style="font-size: 0.75rem; color: var(--color-text-dim);"><?= esc($h['emp_code']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td style="padding: 1.25rem 1.5rem;">
                                    <div style="font-weight: 600; color: var(--color-primary); font-size: 0.9375rem;">
                                        <?= date('d M', strtotime($h['from_date'])) ?> - <?= date('d M Y', strtotime($h['to_date'])) ?>
                                    </div>
                                    <div style="font-size: 0.75rem; color: var(--color-text-dim); margin-top: 0.25rem;">
                                        <?= (strtotime($h['to_date']) - strtotime($h['from_date'])) / 86400 + 1 ?> day(s)
                                    </div>
                                </td>
                                <td style="padding: 1.25rem 1.5rem;">
                                    <?php
                                    $statusClass = 'info';
                                    if ($h['status'] === 'approved') $statusClass = 'success';
                                    elseif ($h['status'] === 'rejected') $statusClass = 'danger';
                                    ?>
                                    <span class="badge badge--<?= $statusClass ?>" style="font-size: 0.7rem; padding: 0.375rem 0.875rem; font-weight: 700; letter-spacing: 0.05em;">
                                        <?= esc(ucfirst($h['status'])) ?>
                                    </span>
                                </td>
                                <td style="padding: 1.25rem 1.5rem;">
                                    <div style="font-size: 0.875rem; color: var(--color-text-main); max-width: 300px;">
                                        <?= esc($h['admin_comment'] ?? '—') ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" style="text-align: center; padding: 4rem;">
                                <div class="empty-state" style="display: flex; flex-direction: column; align-items: center; gap: 1.5rem;">
                                    <div style="width: 80px; height: 80px; border-radius: 50%; background: var(--color-surface-muted); display: flex; align-items: center; justify-content: center;">
                                        <i class="fa-solid fa-folder-open" style="font-size: 2rem; color: var(--color-text-dim);"></i>
                                    </div>
                                    <div>
                                        <div style="font-weight: 700; color: var(--color-text-dim); font-size: 1.125rem; margin-bottom: 0.5rem;">No history yet</div>
                                        <p style="font-size: 0.875rem; color: var(--color-text-dim);">Processed leave requests will appear here.</p>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Action Modal (Simple Prompt for Comment) -->
<form id="action-form" method="POST" style="display:none;">
    <input type="hidden" name="id" id="request-id">
    <input type="hidden" name="admin_comment" id="admin-comment">
</form>

<script>
function handleAction(type, id) {
    const actionText = type === 'approve' ? 'approve' : 'reject';
    const comment = prompt(`Add a comment for ${actionText}al (optional):`);
    if (comment === null) return;

    const form = document.getElementById('action-form');
    form.action = '<?= site_url('leave/') ?>' + type;
    document.getElementById('request-id').value = id;
    document.getElementById('admin-comment').value = comment;
    form.submit();
}
</script>

<?= $this->endSection() ?>
