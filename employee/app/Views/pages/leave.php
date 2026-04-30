<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<div class="page-header">
    <div>
        <h2>Leave Management</h2>
        <p>Apply for leaves and check your balance.</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
    <!-- Leave Balance Cards -->
    <div class="lg:col-span-1 space-y-6">
        <div class="card">
            <div class="card-header">
                <h3>💰 Leave Balance</h3>
            </div>
            <div class="card-body">
                <?php if (!empty($balances)): ?>
                    <?php foreach ($balances as $bal): ?>
                        <div class="mb-4 last:mb-0">
                            <div class="flex justify-between items-center mb-1">
                                <span class="font-bold"><?= esc(ucwords(str_replace('_', ' ', $bal['leave_type']))) ?></span>
                                <span class="badge badge--info"><?= esc($bal['remaining']) ?> days left</span>
                            </div>
                            <div class="emp-stat-bar">
                                <div class="emp-stat-bar-fill" style="width: <?= ($bal['total'] > 0) ? round(($bal['used'] / $bal['total']) * 100) : 0 ?>%; background: var(--warning);"></div>
                            </div>
                            <small class="text-muted"><?= esc($bal['used']) ?> used of <?= esc($bal['total']) ?> total</small>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted">No leave balances found. They will be initialized when you first apply.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3>📅 Upcoming Holidays</h3>
            </div>
            <div class="card-body p-0">
                <ul class="list-none m-0 p-0">
                    <?php if (!empty($holidays)): ?>
                        <?php foreach (array_slice($holidays, 0, 5) as $h): ?>
                            <li class="p-3 border-b last:border-0 flex justify-between items-center">
                                <div>
                                    <div class="font-bold"><?= esc($h['title']) ?></div>
                                    <small class="text-muted"><?= date('D, d M Y', strtotime($h['date'])) ?></small>
                                </div>
                                <span class="badge badge--outline"><?= esc(ucfirst($h['type'])) ?></span>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li class="p-4 text-center text-muted">No upcoming holidays</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>

    <!-- Apply Leave Form -->
    <div class="lg:col-span-1">
        <div class="card">
            <div class="card-header">
                <h3>📝 Apply for Leave</h3>
            </div>
            <div class="card-body">
                <form action="<?= site_url('leave/apply') ?>" method="POST">
                    <div class="form-group mb-4">
                        <label for="leave_type">Leave Type</label>
                        <select name="leave_type" id="leave_type" class="form-control" required>
                            <option value="casual_leave">Casual Leave</option>
                            <option value="sick_leave">Sick Leave</option>
                            <option value="earned_leave">Earned Leave</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div class="form-group">
                            <label for="from_date">From Date</label>
                            <input type="date" name="from_date" id="from_date" class="form-control" required min="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="form-group">
                            <label for="from_session">Session</label>
                            <select name="from_session" id="from_session" class="form-control">
                                <option value="full">Full Day</option>
                                <option value="half_morning">Half Day (AM)</option>
                                <option value="half_afternoon">Half Day (PM)</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div class="form-group">
                            <label for="to_date">To Date</label>
                            <input type="date" name="to_date" id="to_date" class="form-control" required min="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="form-group">
                            <label for="to_session">Session</label>
                            <select name="to_session" id="to_session" class="form-control">
                                <option value="full">Full Day</option>
                                <option value="half_morning">Half Day (AM)</option>
                                <option value="half_afternoon">Half Day (PM)</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group mb-4">
                        <label for="reason">Reason</label>
                        <textarea name="reason" id="reason" class="form-control" rows="3" required placeholder="Why are you taking leave?"></textarea>
                    </div>

                    <button type="submit" class="btn btn--primary w-full">Submit Request</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Leave History -->
    <div class="lg:col-span-2">
        <div class="card">
            <div class="card-header">
                <h3>📋 Leave History</h3>
            </div>
            <div class="card-body p-0">
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Dates</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($history)): ?>
                                <?php foreach ($history as $req): ?>
                                    <tr>
                                        <td>
                                            <strong><?= date('d M', strtotime($req['from_date'])) ?> - <?= date('d M Y', strtotime($req['to_date'])) ?></strong><br>
                                            <small class="text-muted">
                                                <?= ($req['from_session'] !== 'full' || $req['to_session'] !== 'full') ? 'Half Day Included' : 'Full Days' ?>
                                            </small>
                                        </td>
                                        <td><?= esc(ucwords(str_replace('_', ' ', $req['leave_type']))) ?></td>
                                        <td>
                                            <span class="badge badge--<?= esc($req['status']) ?>">
                                                <?= esc(ucfirst($req['status'])) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="max-w-xs truncate" title="<?= esc($req['reason']) ?>">
                                                <?= esc($req['reason']) ?>
                                            </div>
                                            <?php if (!empty($req['admin_comment'])): ?>
                                                <small class="text-danger">Admin: <?= esc($req['admin_comment']) ?></small>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center py-8">
                                        No leave history found.
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

<?= $this->endSection() ?>
