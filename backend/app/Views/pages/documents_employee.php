<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<div class="page-header">
    <div>
        <h2>Employee Documents</h2>
        <p>Manage joining letters, offer letters, and other employee-specific files.</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
    <!-- Upload Form -->
    <div class="lg:col-span-5">
        <div class="card h-full">
            <div class="card-header">
                <h3><i class="fa-solid fa-cloud-arrow-up mr-2"></i> Upload Document</h3>
            </div>
            <div class="card-body">
                <form action="<?= site_url('documents/upload/employee') ?>" method="POST" enctype="multipart/form-data">
                    <div class="form-group mb-6">
                        <label for="emp_codes">Select Employee(s)</label>
                        <select name="emp_codes[]" id="emp_codes" class="tom-select" multiple placeholder="Search or select employees..." required>
                            <?php foreach ($employees as $emp): ?>
                                <option value="<?= esc($emp['emp_code']) ?>"><?= esc($emp['name']) ?> (<?= esc($emp['emp_code']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted mt-1 block">You can assign this document to multiple employees at once.</small>
                    </div>

                    <div class="form-group mb-6">
                        <label for="title">Document Title</label>
                        <input type="text" name="title" id="title" class="form-control" placeholder="e.g. Appointment Letter" required>
                    </div>

                    <div class="form-group mb-6">
                        <label for="document_type">Document Type</label>
                        <select name="document_type" id="document_type" class="form-control" required>
                            <option value="joining">Joining Letter</option>
                            <option value="offer">Offer Letter</option>
                            <option value="incentive">Incentive Letter</option>
                            <option value="id_proof">ID Proof</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div class="form-group mb-8">
                        <label>File Upload</label>
                        <div class="file-drop-area" id="drop-area">
                            <i class="fa-solid fa-file-pdf text-4xl mb-3 text-gray-400"></i>
                            <p class="mb-2 font-medium">Click to upload or drag and drop</p>
                            <span class="text-xs text-muted">PDF, DOCX, JPG or PNG (Max 5MB)</span>
                            <input type="file" name="document" id="document" class="file-input" required>
                        </div>
                        <div id="file-name" class="mt-2 text-sm text-primary font-bold hidden"></div>
                    </div>

                    <button type="submit" class="btn btn--accent btn--block btn--lg">
                        <i class="fa-solid fa-check-circle mr-2"></i> Upload & Assign
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Document List -->
    <div class="lg:col-span-7">
        <div class="card h-full">
            <div class="card-header">
                <h3><i class="fa-solid fa-file-lines mr-2"></i> Recent Uploads</h3>
                <div class="flex items-center gap-3">
                    <div class="relative">
                        <input type="text" id="table-search" class="form-control pl-9" placeholder="Search files..." style="max-width:220px;">
                        <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-wrapper">
                    <table id="data-table">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Document Info</th>
                                <th>Type</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($documents)): ?>
                                <?php foreach ($documents as $doc): ?>
                                    <tr>
                                        <td>
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded bg-gray-100 flex items-center justify-center text-xs font-bold text-gray-600">
                                                    <?= strtoupper(substr($doc['employee_name'], 0, 1)) ?>
                                                </div>
                                                <div>
                                                    <div class="font-bold"><?= esc($doc['employee_name']) ?></div>
                                                    <div class="text-xs text-muted"><?= esc($doc['emp_code']) ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="font-medium"><?= esc($doc['title']) ?></div>
                                            <div class="text-xs text-muted">v<?= esc($doc['version']) ?> · <?= date('d M Y', strtotime($doc['created_at'])) ?></div>
                                        </td>
                                        <td>
                                            <span class="badge badge--accent"><?= esc(ucfirst($doc['document_type'])) ?></span>
                                        </td>
                                        <td>
                                            <div class="flex gap-2">
                                                <a href="<?= site_url('documents/download/employee/' . $doc['id']) ?>" class="btn btn--sm btn--outline" title="Download">
                                                    <i class="fa-solid fa-download"></i>
                                                </a>
                                                <form action="<?= site_url('documents/delete') ?>" method="POST" onsubmit="return confirm('Are you sure you want to delete this document?')" class="inline">
                                                    <input type="hidden" name="id" value="<?= $doc['id'] ?>">
                                                    <input type="hidden" name="type" value="employee">
                                                    <button type="submit" class="btn btn--sm btn--outline text-danger hover:bg-red-50 border-gray-200" title="Delete">
                                                        <i class="fa-solid fa-trash-can"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center py-16">
                                        <div class="empty-state">
                                            <i class="fa-solid fa-folder-open text-5xl mb-4 opacity-20"></i>
                                            <h4>No documents yet</h4>
                                            <p>Upload your first employee document using the form on the left.</p>
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
document.addEventListener('DOMContentLoaded', function() {
    // Initialize TomSelect
    new TomSelect('#emp_codes', {
        plugins: ['remove_button'],
        maxOptions: 50,
        render: {
            option: function(data, escape) {
                return '<div>' + escape(data.text) + '</div>';
            },
            item: function(data, escape) {
                return '<div>' + escape(data.text) + '</div>';
            }
        }
    });

    // File input UX
    const fileInput = document.getElementById('document');
    const fileName = document.getElementById('file-name');
    const dropArea = document.getElementById('drop-area');

    fileInput.addEventListener('change', function() {
        if (this.files && this.files.length > 0) {
            fileName.textContent = 'Selected: ' + this.files[0].name;
            fileName.classList.remove('hidden');
            dropArea.classList.add('border-accent');
        }
    });

    ['dragenter', 'dragover'].forEach(eventName => {
        dropArea.addEventListener(eventName, e => {
            e.preventDefault();
            dropArea.classList.add('bg-gray-50', 'border-accent');
        });
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropArea.addEventListener(eventName, e => {
            e.preventDefault();
            dropArea.classList.remove('bg-gray-50', 'border-accent');
        });
    });
});
</script>

<style>
.file-drop-area {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 32px;
    border: 2px dashed var(--gray-300);
    border-radius: var(--border-radius);
    text-align: center;
    cursor: pointer;
    transition: var(--transition);
}
.file-drop-area:hover {
    border-color: var(--accent);
    background: var(--accent-50);
}
.file-input {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    cursor: pointer;
}
</style>

<?= $this->endSection() ?>
