<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<div class="page-header">
    <div>
        <h2>Leave Requests</h2>
        <p>Review and approve employee leave applications.</p>
    </div>
</div>

<!-- Pending Requests -->
<div class="card mb-6">
    <div class="card-header">
        <h3>⏳ Pending Approval</h3>
    </div>
    <div class="card-body p-0">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Leave Type</th>
                        <th>Dates</th>
                        <th>Reason</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($pending)): ?>
                        <?php foreach ($pending as $p): ?>
                            <tr>
                                <td>
                                    <strong><?= esc($p['employee_name']) ?></strong><br>
                                    <small class="text-muted"><?= esc($p['emp_code']) ?></small>
                                </td>
                                <td><?= esc(ucwords(str_replace('_', ' ', $p['leave_type']))) ?></td>
                                <td>
                                    <?= date('d M', strtotime($p['from_date'])) ?> - <?= date('d M Y', strtotime($p['to_date'])) ?>
                                </td>
                                <td>
                                    <div class="max-w-xs truncate" title="<?= esc($p['reason']) ?>"><?= esc($p['reason']) ?></div>
                                </td>
                                <td>
                                    <button class="btn btn--success btn--sm" onclick="handleAction('approve', <?= $p['id'] ?>)">Approve</button>
                                    <button class="btn btn--danger btn--sm" onclick="handleAction('reject', <?= $p['id'] ?>)">Reject</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center py-8">No pending requests.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- History -->
<div class="card">
    <div class="card-header">
        <h3>📋 Request History</h3>
    </div>
    <div class="card-body p-0">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Dates</th>
                        <th>Status</th>
                        <th>Admin Comment</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($history)): ?>
                        <?php foreach ($history as $h): ?>
                            <tr>
                                <td>
                                    <strong><?= esc($h['employee_name']) ?></strong><br>
                                    <small class="text-muted"><?= esc($h['emp_code']) ?></small>
                                </td>
                                <td><?= date('d M', strtotime($h['from_date'])) ?> - <?= date('d M Y', strtotime($h['to_date'])) ?></td>
                                <td><span class="badge badge--<?= esc($h['status']) ?>"><?= esc(ucfirst($h['status'])) ?></span></td>
                                <td><?= esc($h['admin_comment'] ?? '—') ?></td>
                            </tr>
                        <?php endforeach; ?>
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
    const comment = prompt("Add a comment (optional):");
    if (comment === null) return;
    
    const form = document.getElementById('action-form');
    form.action = '<?= site_url('leave/') ?>' + type;
    document.getElementById('request-id').value = id;
    document.getElementById('admin-comment').value = comment;
    form.submit();
}
</script>

<?= $this->endSection() ?>
