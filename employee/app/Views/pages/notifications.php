<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<div class="page-header">
    <div>
        <h2>Notifications</h2>
        <p>Stay updated with your leave requests and documents.</p>
    </div>
    <button id="mark-all-read" class="btn btn--outline btn--sm">Mark all as read</button>
</div>

<div class="card">
    <div class="card-body p-0">
        <ul class="list-none m-0 p-0">
            <?php if (!empty($notifications)): ?>
                <?php foreach ($notifications as $n): ?>
                    <li class="p-4 border-b last:border-0 <?= $n['is_read'] ? 'opacity-60' : 'bg-blue-50' ?>">
                        <div class="flex justify-between items-start mb-1">
                            <h4 class="m-0 text-lg"><?= esc($n['title']) ?></h4>
                            <small class="text-muted"><?= date('d M Y, h:i A', strtotime($n['created_at'])) ?></small>
                        </div>
                        <p class="m-0 text-gray-700"><?= esc($n['message']) ?></p>
                        <div class="mt-2">
                            <span class="badge badge--<?= esc($n['type']) ?>"><?= esc(ucfirst($n['type'])) ?></span>
                        </div>
                    </li>
                <?php endforeach; ?>
            <?php else: ?>
                <li class="p-8 text-center text-muted">
                    <div class="text-4xl mb-2">🔔</div>
                    <p>No notifications yet.</p>
                </li>
            <?php endif; ?>
        </ul>
    </div>
</div>

<script>
document.getElementById('mark-all-read').addEventListener('click', function() {
    fetch('<?= site_url('notifications/mark-read') ?>', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    }).then(() => window.location.reload());
});
</script>

<?= $this->endSection() ?>
