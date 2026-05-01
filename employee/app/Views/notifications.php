<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<div class="page-header">
    <p style="text-transform: uppercase; letter-spacing: 0.2em; font-size: 0.7rem; font-weight: 800; color: var(--color-accent); margin-bottom: 0.5rem;">Updates</p>
    <h2 class="font-display">Notifications.</h2>
</div>

<div class="grid grid-cols-1 gap-12">
    <div class="card" style="padding: 0; overflow: hidden;">
        <div class="card-header" style="padding: 2rem 3rem; border-bottom: 1px solid var(--color-border); margin-bottom: 0;">
            <h3 class="font-display">Recent Alerts.</h3>
            <button id="mark-all-read" style="background: transparent; border: 1px solid var(--color-accent); color: var(--color-accent); padding: 0.75rem 1.5rem; border-radius: 4px; font-size: 0.7rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.1em; cursor: pointer; transition: all 0.3s ease;">Dismiss All</button>
        </div>

        <div style="display: flex; flex-direction: column;">
            <?php if (!empty($notifications)): ?>
                <?php foreach ($notifications as $n): ?>
                    <div style="padding: 2.5rem 3rem; border-bottom: 1px solid var(--color-border); display: flex; gap: 2.5rem; transition: background 0.3s ease; <?= $n['is_read'] ? 'opacity: 0.4;' : '' ?>" class="hover-trigger">
                        <div style="width: 50px; height: 50px; border-radius: 50%; background: var(--color-surface-soft); border: 1px solid var(--color-border); display: flex; align-items: center; justify-content: center; shrink-0;">
                            <span class="material-symbols-outlined" style="color: var(--color-accent); font-size: 1.25rem;">
                                <?= $n['type'] === 'leave' ? 'calendar_month' : 'notifications_active' ?>
                            </span>
                        </div>
                        
                        <div style="flex: 1;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                <h4 style="font-family: var(--font-display); font-size: 1.25rem;"><?= esc($n['title']) ?>.</h4>
                                <span style="font-size: 0.65rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.1em; color: var(--color-text-dim);"><?= date('D, d M, H:i', strtotime($n['created_at'])) ?></span>
                            </div>
                            <p style="color: var(--color-text-dim); font-size: 0.95rem; line-height: 1.8; max-width: 800px;"><?= esc($n['message']) ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="padding: 8rem; text-align: center;">
                    <span class="material-symbols-outlined" style="font-size: 4rem; opacity: 0.1; margin-bottom: 1.5rem;">done_all</span>
                    <p style="color: var(--color-text-dim); font-family: var(--font-display); font-size: 1.5rem;">Everything is up to date.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    document.getElementById('mark-all-read')?.addEventListener('click', async function() {
        if(!confirm('Archive all notifications?')) return;
        try {
            const response = await fetch('<?= site_url('notifications/mark-read') ?>', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (response.ok) location.reload();
        } catch (error) {
            console.error('Archive error:', error);
        }
    });
</script>

<?= $this->endSection() ?>
