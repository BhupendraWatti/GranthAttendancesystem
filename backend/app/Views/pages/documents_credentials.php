<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<div class="page-header animate-in">
    <div style="display: flex; justify-content: space-between; align-items: flex-end;">
        <div>
            <h2 class="page-title">Employee Credentials Registry</h2>
            <p class="page-subtitle">Personal records, academic certificates, and historical payroll data.</p>
        </div>
        <div style="display: flex; gap: 1rem; align-items: center;">
            <div style="text-align: right;">
                <div
                    style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; color: var(--color-text-dim);">
                    Credential Store</div>
                <div style="font-size: 1.5rem; font-weight: 800; color: #f97316;">
                    <?= count($documents ?? []) ?></div>
            </div>
            <div
                style="width: 48px; height: 48px; border-radius: var(--radius-md); background: linear-gradient(135deg, #f97316 0%, #ea580c 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: 1.25rem;">
                <i class="fa-solid fa-graduation-cap"></i>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
    <!-- Upload Form -->
    <div class="lg:col-span-4 animate-in" style="animation-delay: 0.1s;">
        <div class="card" style="background: linear-gradient(135deg, white 0%, #fff7ed 100%); border-left: 4px solid #f97316; position: sticky; top: 2rem;">
            <div class="card-header" style="padding-bottom: 0.5rem;">
                <h3 style="color: #9a3412;"><i class="fa-solid fa-cloud-arrow-up mr-2"></i> Store Credential</h3>
                <p style="font-size: 0.75rem; color: #fb923c;">Education, Experience & Payslips</p>
            </div>
            <div class="card-body">
                <form action="<?= site_url('documents/upload/employee') ?>" method="POST" enctype="multipart/form-data">
                    <div class="form-group mb-5">
                        <label class="form-label">Associate Selection</label>
                        <select name="emp_codes[]" class="tom-select" multiple placeholder="Select associates..." required>
                            <?php foreach ($employees as $emp): ?>
                                <option value="<?= esc($emp['emp_code']) ?>"><?= esc($emp['name']) ?> (<?= esc($emp['emp_code']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group mb-5">
                        <label class="form-label">Credential Title</label>
                        <input type="text" name="title" class="form-input" placeholder="e.g. Master's Degree Certificate" required>
                    </div>

                    <div class="form-group mb-5">
                        <label class="form-label">Classification</label>
                        <select name="document_type" class="form-input" required>
                            <option value="old_payslip">Old Payslip</option>
                            <option value="degree">Degree Certificate</option>
                            <option value="marksheet">Marksheet / Education</option>
                            <option value="prev_exp">Experience Letter</option>
                            <option value="other">Other Credential</option>
                        </select>
                    </div>

                    <div class="form-group mb-6">
                        <div class="mini-upload-area" style="border-color: #fdba74;">
                            <input type="file" name="document" class="mini-file-input" required>
                            <div class="mini-upload-content" style="color: #f97316;">
                                <i class="fa-solid fa-certificate"></i>
                                <span>Select credential file</span>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block" style="background: #f97316; border-color: #f97316; padding: 0.75rem;">
                        <i class="fa-solid fa-bookmark mr-2"></i> Commit to Store
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Document List -->
    <div class="lg:col-span-8 animate-in" style="animation-delay: 0.2s;">
        <div class="card h-full">
            <div class="card-header">
                <h3><i class="fa-solid fa-box-archive mr-2"></i> credentials_repository</h3>
                <div class="flex items-center gap-3">
                    <div class="relative">
                        <input type="text" id="table-search" class="form-input" placeholder="Filter vault..."
                            style="padding: 0.5rem 1rem 0.5rem 2.5rem; max-width: 220px; font-size: 0.8rem;">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-wrapper">
                    <table id="data-table">
                        <thead>
                            <tr style="background: #fffaf5;">
                                <th>Associate</th>
                                <th>Credential Info</th>
                                <th>Type</th>
                                <th style="text-align: right;">Vault Ops</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($documents)): ?>
                                <?php foreach ($documents as $doc): ?>
                                    <tr>
                                        <td>
                                            <div class="flex items-center gap-3">
                                                <div style="width: 32px; height: 32px; border-radius: 6px; background: #fff7ed; display: flex; align-items: center; justify-content: center; color: #ea580c; font-weight: 800; font-size: 0.75rem;">
                                                    <?= strtoupper(substr($doc['employee_name'], 0, 1)) ?>
                                                </div>
                                                <div>
                                                    <div style="font-weight: 700; color: var(--color-primary); font-size: 0.875rem;">
                                                        <?= esc($doc['employee_name']) ?></div>
                                                    <div style="font-size: 0.7rem; color: var(--color-text-dim);">
                                                        <?= esc($doc['emp_code']) ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div style="font-weight: 600; color: var(--color-primary); font-size: 0.875rem;">
                                                <?= esc($doc['title']) ?></div>
                                            <div style="font-size: 0.65rem; color: var(--color-text-dim); margin-top: 2px;">
                                                Indexed <?= date('d M Y', strtotime($doc['created_at'])) ?>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge" style="background: #fff7ed; color: #ea580c; font-size: 0.6rem; padding: 0.25rem 0.5rem; border: 1px solid #fed7aa;">
                                                <?= strtoupper(str_replace('_', ' ', $doc['document_type'])) ?>
                                            </span>
                                        </td>
                                        <td style="text-align: right;">
                                            <div class="flex gap-2" style="justify-content: flex-end;">
                                                <a href="<?= site_url('documents/download/employee/' . $doc['id']) ?>" class="btn-icon btn-icon--orange" title="Retrieve">
                                                    <i class="fa-solid fa-download"></i>
                                                </a>
                                                <form action="<?= site_url('documents/delete') ?>" method="POST" onsubmit="return confirm('Purge credential from vault?')" class="inline">
                                                    <input type="hidden" name="id" value="<?= $doc['id'] ?>">
                                                    <input type="hidden" name="type" value="employee">
                                                    <button type="submit" class="btn-icon btn-icon--danger">
                                                        <i class="fa-solid fa-trash-can"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" style="text-align: center; padding: 4rem;">
                                        <div class="empty-state">
                                            <i class="fa-solid fa-vault" style="font-size: 2.5rem; color: #fed7aa; margin-bottom: 1rem;"></i>
                                            <p style="color: #94a3b8; font-size: 0.875rem;">Credential vault is empty</p>
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

<script>
    document.addEventListener('DOMContentLoaded', function () {
        new TomSelect('select[multiple]', { plugins: ['remove_button'] });
    });
</script>

<style>
    .mini-upload-area {
        position: relative;
        border: 2px dashed #fed7aa;
        border-radius: var(--radius-md);
        padding: 1.5rem;
        text-align: center;
        background: rgba(255,255,255,0.5);
        transition: all 0.2s;
    }
    .mini-upload-area:hover {
        border-color: #f97316;
        background: white;
    }
    .mini-file-input {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        cursor: pointer;
        z-index: 2;
    }
    .mini-upload-content {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.5rem;
        color: #f97316;
        pointer-events: none;
    }
    .mini-upload-content i { font-size: 1.25rem; }
    .mini-upload-content span { font-size: 0.8rem; font-weight: 600; }

    .btn-icon {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        background: #f1f5f9;
        color: #475569;
        border: 1px solid #e2e8f0;
        transition: all 0.2s;
    }
    .btn-icon--orange:hover { background: #f97316; color: white; border-color: #f97316; }
    .btn-icon--danger:hover { background: #ef4444; color: white; border-color: #ef4444; }

    .form-label { display: block; font-size: 0.7rem; font-weight: 800; text-transform: uppercase; color: #9a3412; margin-bottom: 0.5rem; }
    .form-input { width: 100%; padding: 0.625rem 0.875rem; border: 1px solid #fed7aa; border-radius: var(--radius-md); font-size: 0.8125rem; background: white; }
    .btn-block { width: 100%; }
</style>

<?= $this->endSection() ?>