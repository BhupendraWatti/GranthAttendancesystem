<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<div class="page-header">
    <div>
        <h2>Holiday Management</h2>
        <p>Manage company-wide public holidays and special events.</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Add Holiday Form -->
    <div class="lg:col-span-1">
        <div class="card">
            <div class="card-header">
                <h3>➕ Add New Holiday</h3>
            </div>
            <div class="card-body">
                <form action="<?= site_url('holidays/add') ?>" method="POST">
                    <div class="form-group mb-4">
                        <label for="title">Holiday Title</label>
                        <input type="text" name="title" id="title" class="form-control" required placeholder="e.g. Independence Day">
                    </div>
                    <div class="form-group mb-4">
                        <label for="date">Date</label>
                        <input type="date" name="date" id="date" class="form-control" required>
                    </div>
                    <div class="form-group mb-4">
                        <label for="type">Holiday Type</label>
                        <select name="type" id="type" class="form-control" required>
                            <option value="public">Public Holiday</option>
                            <option value="optional">Optional Holiday</option>
                            <option value="company_event">Company Event (Full Day Off)</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn--primary w-full">Save Holiday</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Holiday List -->
    <div class="lg:col-span-2">
        <div class="card">
            <div class="card-header">
                <h3>📅 Company Calendar 2026</h3>
            </div>
            <div class="card-body p-0">
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Holiday Title</th>
                                <th>Type</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($holidays)): ?>
                                <?php foreach ($holidays as $h): ?>
                                    <tr>
                                        <td><strong><?= date('d M Y (D)', strtotime($h['date'])) ?></strong></td>
                                        <td><?= esc($h['title']) ?></td>
                                        <td><span class="badge badge--info"><?= esc(ucfirst($h['type'])) ?></span></td>
                                        <td>
                                            <form action="<?= site_url('holidays/delete') ?>" method="POST" onsubmit="return confirm('Delete this holiday?')">
                                                <input type="hidden" name="id" value="<?= $h['id'] ?>">
                                                <button type="submit" class="btn btn--danger btn--sm">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="4" class="text-center py-8">No holidays configured yet.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
