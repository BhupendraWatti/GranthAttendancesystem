<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<div class="page-header animate-in">
    <div style="display: flex; justify-content: space-between; align-items: flex-end;">
        <div>
            <h2 class="page-title">Core Document Registry</h2>
            <p class="page-subtitle">Official company issued documents for workforce onboarding.</p>
        </div>
        <div style="display: flex; gap: 1rem; align-items: center;">
            <div style="text-align: right;">
                <div
                    style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; color: var(--color-text-dim);">
                    Core Registry</div>
                <div style="font-size: 1.5rem; font-weight: 800; color: #3b82f6;">
                    <?= count($documents ?? []) ?></div>
            </div>
            <div
                style="width: 48px; height: 48px; border-radius: var(--radius-md); background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: 1.25rem;">
                <i class="fa-solid fa-file-contract"></i>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
    <!-- Upload Form -->
    <div class="lg:col-span-4 animate-in" style="animation-delay: 0.1s;">
        <div class="card" style="background: linear-gradient(135deg, white 0%, #f0f7ff 100%); border-left: 4px solid #3b82f6; position: sticky; top: 2rem;">
            <div class="card-header" style="padding-bottom: 0.5rem;">
                <h3 style="color: #1e40af;"><i class="fa-solid fa-cloud-arrow-up mr-2"></i> Upload Core Doc</h3>
                <p style="font-size: 0.75rem; color: #60a5fa;">Official employment documentation</p>
            </div>
            <div class="card-body">
                <form action="<?= site_url('documents/upload/employee') ?>" method="POST" enctype="multipart/form-data">
                    <div class="form-group mb-5">
                        <label class="form-label">Employee Selection</label>
                        <select name="emp_codes[]" class="tom-select" multiple placeholder="Select employees..." required>
                            <?php foreach ($employees as $emp): ?>
                                <option value="<?= esc($emp['emp_code']) ?>"><?= esc($emp['name']) ?> (<?= esc($emp['emp_code']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group mb-5">
                        <label class="form-label">Document Title</label>
                        <input type="text" name="title" class="form-input" placeholder="e.g. Joining Letter May 2024" required>
                    </div>

                    <div class="form-group mb-5">
                        <label class="form-label">Document Category</label>
                        <select name="document_type" class="form-input" required>
                            <option value="joining">Joining Letter</option>
                            <option value="offer">Offer Letter</option>
                            <option value="contract">Contract Agreement</option>
                            <option value="id_proof">ID Proof</option>
                            <option value="incentive">Incentive Letter</option>
                            <option value="performance">Performance Review</option>
                        </select>
                    </div>

                    <div class="form-group mb-6">
                        <div class="mini-upload-area" id="drop-area">
                            <input type="file" name="document" id="document" class="mini-file-input" required>
                            <div class="mini-upload-content">
                                <i class="fa-solid fa-upload"></i>
                                <span>Select official file</span>
                            </div>
                        </div>
                        <div id="file-name" class="file-name-display"></div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block" style="background: #3b82f6; border-color: #3b82f6; padding: 0.75rem;">
                        <i class="fa-solid fa-shield-check mr-2"></i> Deploy to Registry
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Document List -->
    <div class="lg:col-span-8 animate-in" style="animation-delay: 0.2s;">
        <div class="card h-full">
            <div class="card-header">
                <h3><i class="fa-solid fa-list-check mr-2"></i> core_documents_archive</h3>
                <div class="flex items-center gap-3">
                    <div class="relative">
                        <input type="text" id="table-search" class="form-input" placeholder="Search registry..."
                            style="padding: 0.5rem 1rem 0.5rem 2.5rem; max-width: 220px; font-size: 0.8rem;">
                        <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-wrapper">
                    <table id="data-table">
                        <thead>
                            <tr style="background: #f8fafc;">
                                <th>Associate</th>
                                <th>File Identifier</th>
                                <th>Classification</th>
                                <th style="text-align: right;">Registry Ops</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($documents)): ?>
                                <?php foreach ($documents as $doc): ?>
                                    <tr>
                                        <td>
                                            <div class="flex items-center gap-3">
                                                <div style="width: 32px; height: 32px; border-radius: 6px; background: #e0f2fe; display: flex; align-items: center; justify-content: center; color: #0369a1; font-weight: 800; font-size: 0.75rem;">
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
                                                v<?= esc($doc['version']) ?> · Indexed <?= date('d M Y', strtotime($doc['created_at'])) ?>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge" style="background: #f1f5f9; color: #475569; font-size: 0.6rem; padding: 0.25rem 0.5rem;">
                                                <?= strtoupper(str_replace('_', ' ', $doc['document_type'])) ?>
                                            </span>
                                        </td>
                                        <td style="text-align: right;">
                                            <div class="flex gap-2" style="justify-content: flex-end;">
                                                <a href="<?= site_url('documents/download/employee/' . $doc['id']) ?>" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.8rem; gap: 0.5rem; background: #3b82f6; border-color: #3b82f6;" title="Download">
                                                    <i class="fa-solid fa-download"></i>
                                                    Retrieve
                                                </a>
                                                <form action="<?= site_url('documents/delete') ?>" method="POST" onsubmit="return confirm('Purge document from registry?')" class="inline">
                                                    <input type="hidden" name="id" value="<?= $doc['id'] ?>">
                                                    <input type="hidden" name="type" value="employee">
                                                    <button type="submit" class="btn btn-outline" style="padding: 0.5rem 1rem; font-size: 0.8rem; color: var(--color-danger); border-color: var(--color-border);" title="Delete">
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
                                            <i class="fa-solid fa-folder-closed" style="font-size: 2rem; color: #cbd5e1; margin-bottom: 1rem;"></i>
                                            <p style="color: #94a3b8; font-size: 0.875rem;">Registry is empty</p>
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

        const fileInput = document.getElementById('document');
        const fileName = document.getElementById('file-name');
        const dropArea = document.getElementById('drop-area');

        if (fileInput && fileName && dropArea) {
            fileInput.addEventListener('change', function() {
                if (this.files && this.files.length > 0) {
                    fileName.innerHTML = '<i class="fa-solid fa-check-circle"></i> ' + this.files[0].name;
                    fileName.classList.add('active');
                    dropArea.style.borderColor = 'var(--color-success)';
                }
            });

            ['dragenter', 'dragover'].forEach(eventName => {
                dropArea.addEventListener(eventName, e => {
                    e.preventDefault();
                    dropArea.classList.add('dragover');
                });
            });

            ['dragleave', 'drop'].forEach(eventName => {
                dropArea.addEventListener(eventName, e => {
                    e.preventDefault();
                    dropArea.classList.remove('dragover');
                });
            });
        }
    });
</script>

<style>
    .mini-upload-area {
        position: relative;
        border: 2px dashed #cbd5e1;
        border-radius: var(--radius-md);
        padding: 1.5rem;
        text-align: center;
        background: rgba(255,255,255,0.5);
        transition: all 0.2s;
    }
    .mini-upload-area:hover, .mini-upload-area.dragover {
        border-color: #3b82f6;
        background: white;
    }
    .file-name-display {
        margin-top: 1rem;
        padding: 0.75rem 1rem;
        background: linear-gradient(135deg, #dcfce7 0%, #d1fae5 100%);
        border: 1px solid #86efac;
        border-radius: var(--radius-md);
        font-size: 0.875rem;
        font-weight: 600;
        color: #059669;
        display: none;
        align-items: center;
        gap: 0.5rem;
    }
    .file-name-display.active {
        display: flex;
    }
    .file-name-display i {
        font-size: 1rem;
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
        color: #64748b;
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
    .btn-icon:hover { background: #3b82f6; color: white; border-color: #3b82f6; }
    .btn-icon--danger:hover { background: #ef4444; border-color: #ef4444; }

    .form-label { display: block; font-size: 0.7rem; font-weight: 800; text-transform: uppercase; color: #64748b; margin-bottom: 0.5rem; }
    .form-input { width: 100%; padding: 0.625rem 0.875rem; border: 1px solid #e2e8f0; border-radius: var(--radius-md); font-size: 0.8125rem; background: white; }
    .btn-block { width: 100%; }
</style>

<?= $this->endSection() ?>