<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<div class="page-header animate-in">
    <div>
        <h2 class="font-display" style="font-size: 1.875rem; margin-bottom: 0.25rem;">Organization Structure</h2>
        <p style="color: var(--color-text-dim); font-size: 0.875rem;">Manage company departments and designations.</p>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2.5rem;" class="animate-in">
    
    <!-- Departments Section -->
    <div>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h3 class="font-display" style="font-size: 1.125rem;">Departments</h3>
            <button onclick="openDeptModal()" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.8125rem;">
                <i class="fa-solid fa-plus mr-1"></i> Add Dept
            </button>
        </div>

        <div class="card">
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Department Name</th>
                            <th>Status</th>
                            <th style="text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($departments as $d): ?>
                            <tr>
                                <td style="font-weight: 700;"><?= esc($d['name']) ?></td>
                                <td>
                                    <span class="badge badge--<?= $d['status'] === 'active' ? 'success' : 'danger' ?>">
                                        <?= strtoupper($d['status']) ?>
                                    </span>
                                </td>
                                <td style="text-align: right; display: flex; gap: 0.5rem; justify-content: flex-end;">
                                    <button class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.75rem;"
                                        onclick="openDeptModal(<?= $d['id'] ?>, '<?= esc($d['name']) ?>', '<?= $d['status'] ?>')">
                                        Edit
                                    </button>
                                    <form action="<?= site_url('master/departments/delete') ?>" method="POST" onsubmit="return confirm('Are you sure you want to delete this department?');">
                                        <input type="hidden" name="id" value="<?= $d['id'] ?>">
                                        <button type="submit" class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.75rem; border-color: red; color: red; background: white;">Delete</button>
                                    </form> 
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Designations Section -->
    <div>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h3 class="font-display" style="font-size: 1.125rem;">Designations</h3>
            <button onclick="openDesigModal()" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.8125rem;">
                <i class="fa-solid fa-plus mr-1"></i> Add Designation
            </button>
        </div>

        <div class="card">
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Designation</th>
                            <th>Department</th>
                            <th style="text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($designations as $ds): ?>
                            <tr>
                                <td style="font-weight: 700;"><?= esc($ds['name']) ?></td>
                                <td style="font-size: 0.8125rem; color: var(--color-text-dim);"><?= esc($ds['dept_name'] ?? '—') ?></td>
                                <td style="text-align: right; display: flex; gap: 0.5rem; justify-content: flex-end;">
                                    <button class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.75rem;"
                                        onclick="openDesigModal(<?= $ds['id'] ?>, '<?= esc($ds['name']) ?>', <?= $ds['department_id'] ?>, '<?= $ds['status'] ?>')">
                                        Edit
                                    </button>
                                    <form action="<?= site_url('master/designations/delete') ?>" method="POST" onsubmit="return confirm('Are you sure you want to delete this designation?');">
                                        <input type="hidden" name="id" value="<?= $ds['id'] ?>">
                                        <button type="submit" class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.75rem; border-color: var(--color-danger); color: var(--color-danger);">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Department Modal -->
<div id="dept-modal" class="modal-overlay">
    <div class="modal-container" style="max-width: 400px;">
        <div class="modal-header">
            <h3 id="dept-modal-title">Add Department</h3>
            <button onclick="closeModal('dept-modal')" class="modal-close"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal-body">
            <form action="<?= site_url('master/departments/save') ?>" method="POST">
                <input type="hidden" name="id" id="dept-id">
                <div class="form-group">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" id="dept-name" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="status" id="dept-status" class="form-input">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">Save Department</button>
            </form>
        </div>
    </div>
</div>

<!-- Designation Modal -->
<div id="desig-modal" class="modal-overlay">
    <div class="modal-container" style="max-width: 400px;">
        <div class="modal-header">
            <h3 id="desig-modal-title">Add Designation</h3>
            <button onclick="closeModal('desig-modal')" class="modal-close"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal-body">
            <form action="<?= site_url('master/designations/save') ?>" method="POST">
                <input type="hidden" name="id" id="desig-id">
                <div class="form-group">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" id="desig-name" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Department</label>
                    <select name="department_id" id="desig-dept-id" class="form-input" required>
                        <option value="">-- Select Dept --</option>
                        <?php foreach ($departments as $d): ?>
                            <option value="<?= $d['id'] ?>"><?= esc($d['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="status" id="desig-status" class="form-input">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">Save Designation</button>
            </form>
        </div>
    </div>
</div>

<script>
    function openDeptModal(id = '', name = '', status = 'active') {
        document.getElementById('dept-modal-title').textContent = id ? 'Update Department' : 'Add Department';
        document.getElementById('dept-id').value = id;
        document.getElementById('dept-name').value = name;
        document.getElementById('dept-status').value = status;
        document.getElementById('dept-modal').classList.add('active');
    }

    function openDesigModal(id = '', name = '', deptId = '', status = 'active') {
        document.getElementById('desig-modal-title').textContent = id ? 'Update Designation' : 'Add Designation';
        document.getElementById('desig-id').value = id;
        document.getElementById('desig-name').value = name;
        document.getElementById('desig-dept-id').value = deptId;
        document.getElementById('desig-status').value = status;
        document.getElementById('desig-modal').classList.add('active');
    }

    function closeModal(id) {
        document.getElementById(id).classList.remove('active');
    }
</script>

<?= $this->endSection() ?>
