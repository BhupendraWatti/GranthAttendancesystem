<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<div class="page-header animate-in">
    <div style="display: flex; justify-content: space-between; align-items: flex-end;">
        <div>
            <h2 class="page-title">Employee Document Registry</h2>
            <p class="page-subtitle">Centralized management of joining letters, offer letters, and personal files.</p>
        </div>
        <div style="display: flex; gap: 1rem; align-items: center;">
            <div style="text-align: right;">
                <div
                    style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; color: var(--color-text-dim);">
                    Total Documents</div>
                <div style="font-size: 1.5rem; font-weight: 800; color: var(--color-primary);">
                    <?= count($documents ?? []) ?></div>
            </div>
            <div
                style="width: 48px; height: 48px; border-radius: var(--radius-md); background: linear-gradient(135deg, var(--color-accent) 0%, #6366f1 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: 1.25rem;">
                <i class="fa-solid fa-file-lines"></i>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
    <!-- Upload Form -->
    <div class="lg:col-span-5 animate-in" style="animation-delay: 0.1s;">
        <div class="card h-full"
            style="background: linear-gradient(135deg, white 0%, var(--color-surface-muted) 100%);">
            <div class="card-header">
                <h3><i class="fa-solid fa-cloud-arrow-up mr-2"></i> Upload Document</h3>
            </div>
            <div class="card-body">
                <form action="<?= site_url('documents/upload/employee') ?>" method="POST" enctype="multipart/form-data">
                    <div class="form-group mb-6">
                        <label for="emp_codes" class="form-label">Select Employee(s)</label>
                        <select name="emp_codes[]" id="emp_codes" class="tom-select" multiple
                            placeholder="Search or select employees..." required>
                            <?php foreach ($employees as $emp): ?>
                                <option value="<?= esc($emp['emp_code']) ?>"><?= esc($emp['name']) ?>
                                    (<?= esc($emp['emp_code']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted mt-1 block"
                            style="font-size: 0.75rem; color: var(--color-text-dim);">You can assign this document to
                            multiple employees at once.</small>
                    </div>

                    <div class="form-group mb-6">
                        <label for="title" class="form-label">Document Title</label>
                        <input type="text" name="title" id="title" class="form-input"
                            placeholder="e.g. Appointment Letter" required>
                    </div>

                    <div class="form-group mb-6">
                        <label for="document_type" class="form-label">Document Type</label>
                        <select name="document_type" id="document_type" class="form-input" required>
                            <option value="joining">Joining Letter</option>
                            <option value="offer">Offer Letter</option>
                            <option value="incentive">Incentive Letter</option>
                            <option value="id_proof">ID Proof</option>
                            <option value="performance">Performance Review</option>
                            <option value="contract">Contract Agreement</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div class="form-group mb-8">
                        <label class="form-label">File Upload</label>
                        <div class="premium-upload-area" id="drop-area">
                            <div class="upload-icon">
                                <i class="fa-solid fa-cloud-arrow-up"></i>
                            </div>
                            <div class="upload-text">
                                <div style="font-weight: 600; color: var(--color-primary); margin-bottom: 0.5rem;">Drag
                                    & drop files here</div>
                                <div style="font-size: 0.875rem; color: var(--color-text-dim);">or click to browse from
                                    your device</div>
                            </div>
                            <div class="upload-hint">
                                <span>PDF, DOCX, JPG, PNG</span>
                                <span>Max 5MB</span>
                            </div>
                            <input type="file" name="document" id="document" class="file-input" required>
                        </div>
                        <div id="file-name" class="file-name-display"></div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block"
                        style="padding: 0.875rem 1.5rem; gap: 0.5rem;">
                        <i class="fa-solid fa-check-circle"></i>
                        Upload & Assign
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Document List -->
    <div class="lg:col-span-7 animate-in" style="animation-delay: 0.2s;">
        <div class="card h-full">
            <div class="card-header">
                <h3><i class="fa-solid fa-file-lines mr-2"></i> Recent Uploads</h3>
                <div class="flex items-center gap-3">
                    <div class="relative">
                        <input type="text" id="table-search" class="form-input" placeholder="Search files..."
                            style="padding: 0.5rem 1rem 0.5rem 2.5rem; max-width: 220px;">
                        <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"
                            style="color: var(--color-text-dim);"></i>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-wrapper" style="border-radius: var(--radius-lg); overflow: hidden;">
                    <table id="data-table" style="border: none;">
                        <thead>
                            <tr
                                style="background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-light) 100%);">
                                <th style="color: white; border: none; padding: 1rem 1.5rem;">Employee</th>
                                <th style="color: white; border: none; padding: 1rem 1.5rem;">Document Info</th>
                                <th style="color: white; border: none; padding: 1rem 1.5rem;">Type</th>
                                <th style="color: white; border: none; padding: 1rem 1.5rem; text-align: right;">Action
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($documents)): ?>
                                <?php foreach ($documents as $doc): ?>
                                    <tr style="border-bottom: 1px solid var(--color-border); transition: all 0.2s;">
                                        <td style="padding: 1.25rem 1.5rem;">
                                            <div class="flex items-center gap-3">
                                                <div
                                                    style="width: 40px; height: 40px; border-radius: var(--radius-md); background: linear-gradient(135deg, var(--color-accent) 0%, #6366f1 100%); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 0.875rem;">
                                                    <?= strtoupper(substr($doc['employee_name'], 0, 1)) ?>
                                                </div>
                                                <div>
                                                    <div
                                                        style="font-weight: 700; color: var(--color-primary); font-size: 0.9375rem;">
                                                        <?= esc($doc['employee_name']) ?></div>
                                                    <div style="font-size: 0.75rem; color: var(--color-text-dim);">
                                                        <?= esc($doc['emp_code']) ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td style="padding: 1.25rem 1.5rem;">
                                            <div style="font-weight: 600; color: var(--color-primary); font-size: 0.9375rem;">
                                                <?= esc($doc['title']) ?></div>
                                            <div style="font-size: 0.75rem; color: var(--color-text-dim); margin-top: 0.25rem;">
                                                <i class="fa-solid fa-code-branch" style="margin-right: 0.25rem;"></i>
                                                v<?= esc($doc['version']) ?> ·
                                                <?= date('d M Y', strtotime($doc['created_at'])) ?>
                                            </div>
                                        </td>
                                        <td style="padding: 1.25rem 1.5rem;">
                                            <span class="badge badge--info"
                                                style="font-size: 0.7rem; padding: 0.375rem 0.875rem; font-weight: 700; letter-spacing: 0.05em;">
                                                <?= esc(ucfirst($doc['document_type'])) ?>
                                            </span>
                                        </td>
                                        <td style="padding: 1.25rem 1.5rem; text-align: right;">
                                            <div class="flex gap-2" style="justify-content: flex-end;">
                                                <a href="<?= site_url('documents/download/employee/' . $doc['id']) ?>"
                                                    class="btn btn-primary"
                                                    style="padding: 0.5rem 1rem; font-size: 0.8rem; gap: 0.5rem;"
                                                    title="Download">
                                                    <i class="fa-solid fa-download"></i>
                                                    Retrieve
                                                </a>
                                                <form action="<?= site_url('documents/delete') ?>" method="POST"
                                                    onsubmit="return confirm('Are you sure you want to delete this document?')"
                                                    class="inline">
                                                    <input type="hidden" name="id" value="<?= $doc['id'] ?>">
                                                    <input type="hidden" name="type" value="employee">
                                                    <button type="submit" class="btn btn-outline"
                                                        style="padding: 0.5rem 1rem; font-size: 0.8rem; color: var(--color-danger); border-color: var(--color-border);"
                                                        title="Delete">
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
                                        <div class="empty-state"
                                            style="display: flex; flex-direction: column; align-items: center; gap: 1.5rem;">
                                            <div
                                                style="width: 80px; height: 80px; border-radius: 50%; background: var(--color-surface-muted); display: flex; align-items: center; justify-content: center;">
                                                <i class="fa-solid fa-folder-open"
                                                    style="font-size: 2rem; color: var(--color-text-dim);"></i>
                                            </div>
                                            <div>
                                                <div
                                                    style="font-weight: 700; color: var(--color-text-dim); font-size: 1.125rem; margin-bottom: 0.5rem;">
                                                    No documents yet</div>
                                                <p style="font-size: 0.875rem; color: var(--color-text-dim);">Upload your
                                                    first employee document using the form on the left.</p>
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

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Initialize TomSelect
        new TomSelect('#emp_codes', {
            plugins: ['remove_button'],
            maxOptions: 50,
            render: {
                option: function (data, escape) {
                    return '<div>' + escape(data.text) + '</div>';
                },
                item: function (data, escape) {
                    return '<div>' + escape(data.text) + '</div>';
                }
            }
        });

        // File input UX
        const fileInput = document.getElementById('document');
        const fileName = document.getElementById('file-name');
        const dropArea = document.getElementById('drop-area');

        fileInput.addEventListener('change', function () {
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
    });
</script>

<style>
    .premium-upload-area {
        position: relative;
        border: 2px dashed var(--color-border);
        border-radius: var(--radius-lg);
        padding: 2.5rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        background: linear-gradient(135deg, var(--color-surface-muted) 0%, white 100%);
    }

    .premium-upload-area:hover {
        border-color: var(--color-accent);
        background: linear-gradient(135deg, #e0e7ff 0%, white 100%);
        transform: translateY(-2px);
    }

    .premium-upload-area.dragover {
        border-color: var(--color-accent);
        background: linear-gradient(135deg, #c7d2fe 0%, #e0e7ff 100%);
    }

    .upload-icon {
        width: 64px;
        height: 64px;
        margin: 0 auto 1.5rem;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--color-accent) 0%, #6366f1 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.5rem;
    }

    .upload-hint {
        display: flex;
        gap: 1rem;
        justify-content: center;
        margin-top: 1rem;
        font-size: 0.75rem;
        color: var(--color-text-dim);
    }

    .upload-hint span {
        background: var(--color-surface-muted);
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-weight: 600;
    }

    .file-input {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        cursor: pointer;
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
</style>

<?= $this->endSection() ?>