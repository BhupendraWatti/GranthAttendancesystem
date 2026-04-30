<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<div class="page-header">
    <div>
        <h2><?= esc($pageTitle) ?></h2>
        <p>Access your personal records and important company policies.</p>
    </div>
</div>

<div class="card">
    <div class="card-header flex justify-between items-center">
        <div class="tabs flex gap-4" id="document-tabs">
            <button class="tab-btn active" data-target="personal-docs">
                <i class="fa-solid fa-user-tag mr-2"></i> My Documents
            </button>
            <button class="tab-btn" data-target="company-docs">
                <i class="fa-solid fa-building-shield mr-2"></i> Company Policies
            </button>
        </div>
        <div class="relative search-wrapper">
            <input type="text" id="doc-search" class="form-control" placeholder="Search documents..." style="max-width:250px;">
            <i class="fa-solid fa-magnifying-glass absolute right-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
        </div>
    </div>
    
    <div class="card-body p-0">
        <!-- Personal Documents Tab -->
        <div id="personal-docs" class="tab-content active">
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Document Title</th>
                            <th>Type</th>
                            <th>Version</th>
                            <th>Date</th>
                            <th class="text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($employeeDocuments)): ?>
                            <?php foreach ($employeeDocuments as $doc): ?>
                                <tr class="doc-row">
                                    <td>
                                        <div class="flex items-center gap-3">
                                            <div class="doc-icon-box">
                                                <i class="fa-solid fa-file-pdf text-red-500"></i>
                                            </div>
                                            <div class="font-bold"><?= esc($doc['title']) ?></div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge--accent"><?= esc(ucfirst($doc['document_type'])) ?></span>
                                    </td>
                                    <td class="font-mono text-sm">v<?= esc($doc['version']) ?></td>
                                    <td class="text-muted"><?= date('d M Y', strtotime($doc['created_at'])) ?></td>
                                    <td class="text-right">
                                        <a href="<?= site_url('documents/download/employee/' . $doc['id']) ?>" class="btn btn--primary btn--sm">
                                            <i class="fa-solid fa-download mr-1"></i> Download
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5">
                                    <div class="empty-state py-16">
                                        <div class="empty-icon text-5xl mb-4 opacity-20">📂</div>
                                        <h4>No documents available</h4>
                                        <p class="text-muted">Your personal documents haven't been uploaded yet.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Company Documents Tab -->
        <div id="company-docs" class="tab-content hidden">
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Policy / Guideline</th>
                            <th>Category</th>
                            <th>Version</th>
                            <th>Last Updated</th>
                            <th class="text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($companyDocuments)): ?>
                            <?php foreach ($companyDocuments as $doc): ?>
                                <tr class="doc-row">
                                    <td>
                                        <div class="flex items-center gap-3">
                                            <div class="doc-icon-box">
                                                <i class="fa-solid fa-shield-halved text-primary"></i>
                                            </div>
                                            <div class="font-bold"><?= esc($doc['title']) ?></div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge--info"><?= esc(ucfirst($doc['category'])) ?></span>
                                    </td>
                                    <td class="font-mono text-sm">v<?= esc($doc['version']) ?></td>
                                    <td class="text-muted"><?= date('d M Y', strtotime($doc['created_at'])) ?></td>
                                    <td class="text-right">
                                        <a href="<?= site_url('documents/download/company/' . $doc['id']) ?>" class="btn btn--primary btn--sm">
                                            <i class="fa-solid fa-download mr-1"></i> Download
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5">
                                    <div class="empty-state py-16">
                                        <div class="empty-icon text-5xl mb-4 opacity-20">📜</div>
                                        <h4>No policies found</h4>
                                        <p class="text-muted">General company policies will appear here.</p>
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

<style>
.tab-btn {
    padding: 12px 20px;
    font-weight: 600;
    color: var(--gray-500);
    border-bottom: 3px solid transparent;
    transition: all 0.2s;
}
.tab-btn:hover {
    color: var(--primary);
}
.tab-btn.active {
    color: var(--primary);
    border-bottom-color: var(--primary);
}
.doc-icon-box {
    width: 40px;
    height: 40px;
    background: var(--gray-100);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
}
.doc-row:hover {
    background-color: var(--gray-50);
}
.hidden { display: none; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tabButtons = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');
    const searchInput = document.getElementById('doc-search');

    // Tab Switching
    tabButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            const target = btn.getAttribute('data-target');
            
            tabButtons.forEach(b => b.classList.remove('active'));
            tabContents.forEach(c => c.classList.add('hidden'));
            
            btn.classList.add('active');
            document.getElementById(target).classList.remove('hidden');
        });
    });

    // Simple Search Filter
    searchInput.addEventListener('input', function() {
        const query = this.value.toLowerCase();
        const rows = document.querySelectorAll('.doc-row');
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(query) ? '' : 'none';
        });
    });
});
</script>

<?= $this->endSection() ?>
