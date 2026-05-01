<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<div class="page-header animate-in">
    <div style="display: flex; justify-content: space-between; align-items: flex-end;">
        <div>
            <h2 class="page-title">Event Calendar Management</h2>
            <p class="page-subtitle">Manage company-wide public holidays, special events, and observances.</p>
        </div>
        <div style="display: flex; gap: 1rem; align-items: center;">
            <div style="text-align: right;">
                <div style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; color: var(--color-text-dim);">Total Events</div>
                <div style="font-size: 1.5rem; font-weight: 800; color: var(--color-primary);"><?= count($holidays ?? []) ?></div>
            </div>
            <div style="width: 48px; height: 48px; border-radius: var(--radius-md); background: linear-gradient(135deg, var(--color-accent) 0%, #6366f1 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: 1.25rem;">
                <i class="fa-solid fa-calendar-days"></i>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Add Holiday Form -->
    <div class="lg:col-span-1 animate-in" style="animation-delay: 0.1s;">
        <div class="card h-full" style="background: linear-gradient(135deg, white 0%, var(--color-surface-muted) 100%);">
            <div class="card-header">
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <div style="width: 40px; height: 40px; border-radius: var(--radius-md); background: linear-gradient(135deg, var(--color-success) 0%, #34D399 100%); display: flex; align-items: center; justify-content: center; color: white;">
                        <i class="fa-solid fa-plus"></i>
                    </div>
                    <div>
                        <h3 style="margin: 0;">Add New Event</h3>
                        <div style="font-size: 0.75rem; color: var(--color-text-dim);">Schedule holidays and events</div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <form action="<?= site_url('holidays/add') ?>" method="POST">
                    <div class="form-group mb-6">
                        <label for="title" class="form-label">Event Title</label>
                        <input type="text" name="title" id="title" class="form-input" required placeholder="e.g. Independence Day">
                    </div>
                    <div class="form-group mb-6">
                        <label for="date" class="form-label">Event Date</label>
                        <input type="date" name="date" id="date" class="form-input" required>
                    </div>
                    <div class="form-group mb-6">
                        <label for="type" class="form-label">Event Type</label>
                        <select name="type" id="type" class="form-input" required>
                            <option value="public">Public Holiday</option>
                            <option value="optional">Optional Holiday</option>
                            <option value="company_event">Company Event (Full Day Off)</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block" style="padding: 0.875rem 1.5rem; gap: 0.5rem;">
                        <i class="fa-solid fa-calendar-plus"></i>
                        Save Event
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Holiday List -->
    <div class="lg:col-span-2 animate-in" style="animation-delay: 0.2s;">
        <div class="card h-full">
            <div class="card-header">
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <div style="width: 40px; height: 40px; border-radius: var(--radius-md); background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-light) 100%); display: flex; align-items: center; justify-content: center; color: white;">
                        <i class="fa-solid fa-calendar-days"></i>
                    </div>
                    <div>
                        <h3 style="margin: 0;">Company Calendar 2026</h3>
                        <div style="font-size: 0.75rem; color: var(--color-text-dim);"><?= count($holidays ?? []) ?> scheduled events</div>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-wrapper" style="border-radius: var(--radius-lg); overflow: hidden;">
                    <table style="border: none;">
                        <thead>
                            <tr style="background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-light) 100%);">
                                <th style="color: white; border: none; padding: 1rem 1.5rem;">Event Date</th>
                                <th style="color: white; border: none; padding: 1rem 1.5rem;">Event Title</th>
                                <th style="color: white; border: none; padding: 1rem 1.5rem;">Type</th>
                                <th style="color: white; border: none; padding: 1rem 1.5rem; text-align: right;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($holidays)): ?>
                                <?php foreach ($holidays as $h): ?>
                                    <tr style="border-bottom: 1px solid var(--color-border); transition: all 0.2s;">
                                        <td style="padding: 1.25rem 1.5rem;">
                                            <div style="display: flex; align-items: center; gap: 1rem;">
                                                <div style="width: 48px; height: 48px; border-radius: var(--radius-md); background: linear-gradient(135deg, var(--color-accent) 0%, #6366f1 100%); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 0.875rem;">
                                                    <?= date('d', strtotime($h['date'])) ?>
                                                </div>
                                                <div>
                                                    <div style="font-weight: 700; color: var(--color-primary); font-size: 0.9375rem;"><?= date('d M Y (D)', strtotime($h['date'])) ?></div>
                                                    <div style="font-size: 0.75rem; color: var(--color-text-dim); margin-top: 0.25rem;">
                                                        <?php
                                                        $eventDate = new DateTime($h['date']);
                                                        $today = new DateTime();
                                                        $diff = $today->diff($eventDate);
                                                        if ($diff->days > 0) {
                                                            echo $diff->days . ' day' . ($diff->days > 1 ? 's' : '') . ' away';
                                                        } elseif ($diff->days == 0) {
                                                            echo 'Today';
                                                        } else {
                                                            echo abs($diff->days) . ' day' . (abs($diff->days) > 1 ? 's' : '') . ' ago';
                                                        }
                                                        ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td style="padding: 1.25rem 1.5rem;">
                                            <div style="font-weight: 700; color: var(--color-primary); font-size: 0.9375rem;"><?= esc($h['title']) ?></div>
                                        </td>
                                        <td style="padding: 1.25rem 1.5rem;">
                                            <?php
                                            $typeClass = 'info';
                                            $typeIcon = 'fa-calendar';
                                            if ($h['type'] === 'public') {
                                                $typeClass = 'success';
                                                $typeIcon = 'fa-globe';
                                            } elseif ($h['type'] === 'optional') {
                                                $typeClass = 'warning';
                                                $typeIcon = 'fa-star';
                                            } elseif ($h['type'] === 'company_event') {
                                                $typeClass = 'primary';
                                                $typeIcon = 'fa-building';
                                            }
                                            ?>
                                            <span class="tag tag--<?= $typeClass ?>" style="font-size: 0.7rem; padding: 0.375rem 0.875rem;">
                                                <i class="fa-solid <?= $typeIcon ?>" style="margin-right: 0.25rem;"></i>
                                                <?= esc(ucfirst(str_replace('_', ' ', $h['type']))) ?>
                                            </span>
                                        </td>
                                        <td style="padding: 1.25rem 1.5rem; text-align: right;">
                                            <form action="<?= site_url('holidays/delete') ?>" method="POST" onsubmit="return confirm('Are you sure you want to delete this event?')" class="inline">
                                                <input type="hidden" name="id" value="<?= $h['id'] ?>">
                                                <button type="submit" class="btn btn-outline" style="padding: 0.5rem 1rem; font-size: 0.8rem; gap: 0.5rem; color: var(--color-danger); border-color: var(--color-border);">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                    Delete
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" style="text-align: center; padding: 4rem;">
                                        <div class="empty-state" style="display: flex; flex-direction: column; align-items: center; gap: 1.5rem;">
                                            <div style="width: 80px; height: 80px; border-radius: 50%; background: var(--color-surface-muted); display: flex; align-items: center; justify-content: center;">
                                                <i class="fa-solid fa-calendar-xmark" style="font-size: 2rem; color: var(--color-text-dim);"></i>
                                            </div>
                                            <div>
                                                <div style="font-weight: 700; color: var(--color-text-dim); font-size: 1.125rem; margin-bottom: 0.5rem;">No events scheduled</div>
                                                <p style="font-size: 0.875rem; color: var(--color-text-dim);">Add your first holiday or company event using the form on the left.</p>
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
    </div>
</div>

<style>
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
    .btn-block {
        width: 100%;
    }
    .tag {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        padding: 0.375rem 0.875rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .tag--primary {
        background: linear-gradient(135deg, #E0E7FF 0%, #C7D2FE 100%);
        color: #4338CA;
        border: 1px solid #A5B4FC;
    }
    .tag--success {
        background: linear-gradient(135deg, #DCFCE7 0%, #D1FAE5 100%);
        color: #059669;
        border: 1px solid #86EFAC;
    }
    .tag--warning {
        background: linear-gradient(135deg, #FEF3C7 0%, #FDE68A 100%);
        color: #D97706;
        border: 1px solid #FCD34D;
    }
    .tag--info {
        background: linear-gradient(135deg, #E0F2FE 0%, #BAE6FD 100%);
        color: #0284C7;
        border: 1px solid #7DD3FC;
    }
</style>

<?= $this->endSection() ?>
