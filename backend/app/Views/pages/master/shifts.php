<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<div class="page-header animate-in">
    <div>
        <h2 class="font-display" style="font-size: 1.875rem; margin-bottom: 0.25rem;">Shift Master</h2>
        <p style="color: var(--color-text-dim); font-size: 0.875rem;">Configure work schedules and grace periods.</p>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 350px; gap: 2rem;" class="animate-in">
    <!-- List Section -->
    <div class="card">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Shift Name</th>
                        <th>Timing</th>
                        <th>Hours</th>
                        <th>Grace</th>
                        <th>Status</th>
                        <th style="text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($shifts as $s): ?>
                        <tr>
                            <td style="font-weight: 700; color: var(--color-primary);"><?= esc($s['name']) ?></td>
                            <td style="font-family: var(--font-mono); font-size: 0.8125rem;">
                                <?= date('H:i', strtotime($s['start_time'])) ?> - <?= date('H:i', strtotime($s['end_time'])) ?>
                            </td>
                            <td><?= esc($s['expected_hours']) ?>h</td>
                            <td><?= esc($s['grace_minutes']) ?>m</td>
                            <td>
                                <span class="badge badge--<?= $s['status'] === 'active' ? 'success' : 'danger' ?>">
                                    <?= strtoupper($s['status']) ?>
                                </span>
                            </td>
                            <td style="text-align: right; display: flex; gap: 0.5rem; justify-content: flex-end;">
                                <button class="btn btn-outline edit-shift-btn" 
                                    data-id="<?= $s['id'] ?>"
                                    data-name="<?= esc($s['name']) ?>"
                                    data-start="<?= substr($s['start_time'], 0, 5) ?>"
                                    data-end="<?= substr($s['end_time'], 0, 5) ?>"
                                    data-hours="<?= $s['expected_hours'] ?>"
                                    data-grace="<?= $s['grace_minutes'] ?>"
                                    data-intern="<?= $s['is_intern_shift'] ?>"
                                    data-status="<?= $s['status'] ?>"
                                    style="padding: 0.25rem 0.5rem; font-size: 0.75rem;">Edit</button>
                                <form action="<?= site_url('master/shifts/delete') ?>" method="POST" onsubmit="return confirm('Are you sure you want to delete this shift?');">
                                    <input type="hidden" name="id" value="<?= $s['id'] ?>">
                                    <button type="submit" class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.75rem; border-color: red; color: red; background: white;">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Form Section -->
    <div class="card" style="height: fit-content;">
        <div class="card-header">
            <h3 id="form-title">Add New Shift</h3>
        </div>
        <div class="card-body">
            <form action="<?= site_url('master/shifts/save') ?>" method="POST">
                <input type="hidden" name="id" id="shift-id">
                
                <div class="form-group">
                    <label class="form-label">Shift Label</label>
                    <input type="text" name="name" id="shift-name" class="form-input" placeholder="e.g. General Shift A" required>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="form-group">
                        <label class="form-label">Start Time</label>
                        <input type="time" name="start_time" id="shift-start" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">End Time</label>
                        <input type="time" name="end_time" id="shift-end" class="form-input" required>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="form-group">
                        <label class="form-label">Work Hours</label>
                        <input type="number" step="0.1" name="expected_hours" id="shift-hours" class="form-input" value="8.5">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Grace (Mins)</label>
                        <input type="number" name="grace_minutes" id="shift-grace" class="form-input" value="30">
                    </div>
                </div>

                <div class="form-group">
                    <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.8125rem; font-weight: 600; cursor: pointer;">
                        <input type="checkbox" name="is_intern_shift" id="shift-intern" value="1">
                        Is Intern/Short Shift?
                    </label>
                </div>

                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="status" id="shift-status" class="form-input">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

                <div style="display: flex; gap: 1rem; margin-top: 1rem;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">Save Shift</button>
                    <button type="button" onclick="resetShiftForm()" class="btn btn-outline">Reset</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function resetShiftForm() {
        document.getElementById('form-title').textContent = 'Add New Shift';
        document.getElementById('shift-id').value = '';
        document.getElementById('shift-name').value = '';
        document.getElementById('shift-start').value = '';
        document.getElementById('shift-end').value = '';
        document.getElementById('shift-hours').value = '8.5';
        document.getElementById('shift-grace').value = '30';
        document.getElementById('shift-intern').checked = false;
        document.getElementById('shift-status').value = 'active';
    }

    document.querySelectorAll('.edit-shift-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById('form-title').textContent = 'Update Shift';
            document.getElementById('shift-id').value = btn.dataset.id;
            document.getElementById('shift-name').value = btn.dataset.name;
            document.getElementById('shift-start').value = btn.dataset.start;
            document.getElementById('shift-end').value = btn.dataset.end;
            document.getElementById('shift-hours').value = btn.dataset.hours;
            document.getElementById('shift-grace').value = btn.dataset.grace;
            document.getElementById('shift-intern').checked = btn.dataset.intern == 1;
            document.getElementById('shift-status').value = btn.dataset.status;
        });
    });

    function calculateHours() {
        const start = document.getElementById('shift-start').value;
        const end = document.getElementById('shift-end').value;
        if (start && end) {
            const startTime = new Date(`1970-01-01T${start}:00Z`);
            let endTime = new Date(`1970-01-01T${end}:00Z`);
            if (endTime < startTime) {
                endTime.setDate(endTime.getDate() + 1); // Crosses midnight
            }
            const diffHrs = (endTime - startTime) / (1000 * 60 * 60);
            document.getElementById('shift-hours').value = diffHrs.toFixed(1);
        }
    }

    document.getElementById('shift-start').addEventListener('change', calculateHours);
    document.getElementById('shift-end').addEventListener('change', calculateHours);
</script>

<?= $this->endSection() ?>
