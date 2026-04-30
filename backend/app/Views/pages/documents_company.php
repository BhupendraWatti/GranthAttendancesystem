<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<div class="page-header">
    <div>
        <h2>Company Documents</h2>
        <p>Manage company policies, guidelines, and shared resources.</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
    <!-- Upload Form -->
    <div class="lg:col-span-5">
        <div class="card h-full">
            <div class="card-header">
                <h3><i class="fa-solid fa-building-shield mr-2"></i> Upload Policy</h3>
            </div>
            <div class="card-body">
                <form action="<?= site_url('documents/upload/company') ?>" method="POST" enctype="multipart/form-data">
                    <div class="form-group mb-6">
                        <label for="title">Document Title</label>
                        <input type="text" name="title" id="title" class="form-control" placeholder="e.g. Leave Policy 2026" required>
                    </div>

                    <div class="form-group mb-6">
                        <label for="category">Category</label>
                        <select name="category" id="category" class="form-control" required>
                            <option value="policy">Policy</option>
                            <option value="guideline">Guideline</option>
                            <option value="form">Form/Template</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div class="form-group mb-8">
                        <label>File Upload</label>
                        <div class="file-drop-area" id="drop-area">
                            <i class="fa-solid fa-file-pdf text-4xl mb-3 text-gray-400"></i>
                            <p class="mb-2 font-medium">Click to upload or drag and drop</p>
                            <span class="text-xs text-muted">PDF or DOCX (Max 10MB)</span>
                            <input type="file" name="document" id="document" class="file-input" required>
                        </div>
                        <div id="file-name" class="mt-2 text-sm text-primary font-bold hidden"></div>
                    </div>

                    <button type="submit" class="btn btn--accent btn--block btn--lg">
                        <i class="fa-solid fa-cloud-arrow-up mr-2"></i> Publish Document
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Document List -->
    <div class="lg:col-span-7">
        <div class="card h-full">
            <div class="card-header">
                <h3><i class="fa-solid fa-library-building mr-2"></i> Resource Library</h3>
                <div class="flex items-center gap-3">
                    <div class="relative">
                        <input type="text" id="table-search" class="form-control pl-9" placeholder="Search library..." style="max-width:220px;">
                        <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-wrapper">
                    <table id="data-table">
                        <thead>
                            <tr>
                                <th>Document Title</th>
                                <th>Category</th>
                                <th>Details</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($documents)): ?>
                                <?php foreach ($documents as $doc): ?>
                                    <tr>
                                        <td><strong><?= esc($doc['title']) ?></strong></td>
                                        <td>
                                            <span class="badge badge--<?= $doc['category'] === 'policy' ? 'info' : 'warning' ?>">
                                                <?= esc(ucfirst($doc['category'])) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="text-xs text-muted">v<?= esc($doc['version']) ?> · <?= date('d M Y', strtotime($doc['created_at'])) ?></div>
                                        </td>
                                        <td>
                                            <div class="flex gap-2">
                                                <a href="<?= site_url('documents/download/company/' . $doc['id']) ?>" class="btn btn--sm btn--outline" title="Download">
                                                    <i class="fa-solid fa-download"></i>
                                                </a>
                                                <form action="<?= site_url('documents/delete') ?>" method="POST" onsubmit="return confirm('Are you sure you want to delete this document?')" class="inline">
                                                    <input type="hidden" name="id" value="<?= $doc['id'] ?>">
                                                    <input type="hidden" name="type" value="company">
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
                                            <i class="fa-solid fa-book text-5xl mb-4 opacity-20"></i>
                                            <h4>No company documents found</h4>
                                            <p>Upload policies, guidelines, or templates for your employees.</p>
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
