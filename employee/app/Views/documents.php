<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<div class="page-header">
    <p class="text-muted"
        style="text-transform: uppercase; letter-spacing: 0.1em; font-size: 0.75rem; font-weight: 600; margin-bottom: 0.5rem;">
        Secure Storage</p>
    <h2 class="font-display">Corporate Documents</h2>
</div>

<div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem; margin-bottom: 2rem;">

    <?php 
        $coreTypes = ['joining', 'offer', 'contract', 'id_proof', 'performance', 'incentive'];
        $personalDocs = array_filter($employeeDocuments, fn($d) => in_array($d['document_type'], $coreTypes));
        $otherDocs = array_filter($employeeDocuments, fn($d) => !in_array($d['document_type'], $coreTypes));
    ?>

    <!-- Personal Documents -->
    <div class="card">
        <div class="card-header">
            <h3>Personal Files</h3>
            <span class="text-muted" style="font-size: 0.75rem;">Private archives</span>
        </div>

        <div class="card-body" style="padding: 0;">
            <div style="display: flex; flex-direction: column;">
                <?php if (!empty($personalDocs)): ?>
                    <?php foreach ($personalDocs as $doc): ?>
                        <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--color-border); display: flex; justify-content: space-between; align-items: center; transition: background 0.2s;"
                            onmouseover="this.style.background='var(--color-surface-muted)'"
                            onmouseout="this.style.background='transparent'">
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <div
                                    style="width: 40px; height: 40px; border-radius: 8px; background: var(--color-accent-soft); display: flex; align-items: center; justify-content: center; color: var(--color-accent);">
                                    <span class="material-symbols-outlined">description</span>
                                </div>
                                <div>
                                    <div style="font-size: 0.875rem; font-weight: 600;"><?= esc($doc['title']) ?></div>
                                    <div class="text-muted" style="font-size: 0.75rem;">
                                        <?= date('d M Y', strtotime($doc['created_at'])) ?></div>
                                </div>
                            </div>
                            <a href="<?= site_url("documents/download/employee/{$doc['id']}") ?>" class="btn btn-outline"
                                style="padding: 0.5rem; border-radius: 50%; min-width: 36px; height: 36px;">
                                <span class="material-symbols-outlined" style="font-size: 1.25rem;">download</span>
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="padding: 4rem 2rem; text-align: center;">
                        <span class="material-symbols-outlined"
                            style="font-size: 3rem; color: var(--color-border); margin-bottom: 1rem;">folder_open</span>
                        <p class="text-muted" style="font-size: 0.875rem;">No personal documents discovered.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Other Documents -->
    <div class="card">
        <div class="card-header">
            <h3>Other Files</h3>
            <span class="text-muted" style="font-size: 0.75rem;">Academic & Credentials</span>
        </div>

        <div class="card-body" style="padding: 0;">
            <div style="display: flex; flex-direction: column;">
                <?php if (!empty($otherDocs)): ?>
                    <?php foreach ($otherDocs as $doc): ?>
                        <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--color-border); display: flex; justify-content: space-between; align-items: center; transition: background 0.2s;"
                            onmouseover="this.style.background='var(--color-surface-muted)'"
                            onmouseout="this.style.background='transparent'">
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <div
                                    style="width: 40px; height: 40px; border-radius: 8px; background: #FFF7ED; display: flex; align-items: center; justify-content: center; color: #F97316;">
                                    <span class="material-symbols-outlined">school</span>
                                </div>
                                <div>
                                    <div style="font-size: 0.875rem; font-weight: 600;"><?= esc($doc['title']) ?></div>
                                    <div class="text-muted" style="font-size: 0.75rem;">
                                        <?= strtoupper(str_replace('_', ' ', $doc['document_type'])) ?></div>
                                </div>
                            </div>
                            <a href="<?= site_url("documents/download/employee/{$doc['id']}") ?>" class="btn btn-outline"
                                style="padding: 0.5rem; border-radius: 50%; min-width: 36px; height: 36px;">
                                <span class="material-symbols-outlined" style="font-size: 1.25rem;">download</span>
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="padding: 4rem 2rem; text-align: center;">
                        <span class="material-symbols-outlined"
                            style="font-size: 3rem; color: var(--color-border); margin-bottom: 1rem;">badge</span>
                        <p class="text-muted" style="font-size: 0.875rem;">No other files discovered.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Company Documents -->
    <div class="card">
        <div class="card-header">
            <h3>Enterprise Policies</h3>
            <span class="text-muted" style="font-size: 0.75rem;">Publicly accessible</span>
        </div>

        <div class="card-body" style="padding: 0;">
            <div style="display: flex; flex-direction: column;">
                <?php if (!empty($companyDocuments)): ?>
                    <?php foreach ($companyDocuments as $doc): ?>
                        <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--color-border); display: flex; justify-content: space-between; align-items: center; transition: background 0.2s;"
                            onmouseover="this.style.background='var(--color-surface-muted)'"
                            onmouseout="this.style.background='transparent'">
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <div
                                    style="width: 40px; height: 40px; border-radius: 8px; background: #F1F5F9; display: flex; align-items: center; justify-content: center; color: var(--color-text-dim);">
                                    <span class="material-symbols-outlined">policy</span>
                                </div>
                                <div>
                                    <div style="font-size: 0.875rem; font-weight: 600;"><?= esc($doc['title']) ?></div>
                                    <div class="text-muted"
                                        style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">
                                        <?= esc($doc['category'] ?? 'General') ?></div>
                                </div>
                            </div>
                            <a href="<?= site_url("documents/download/company/{$doc['id']}") ?>" class="btn btn-outline"
                                style="padding: 0.5rem; border-radius: 50%; min-width: 36px; height: 36px;">
                                <span class="material-symbols-outlined" style="font-size: 1.25rem;">download</span>
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="padding: 4rem 2rem; text-align: center;">
                        <span class="material-symbols-outlined"
                            style="font-size: 3rem; color: var(--color-border); margin-bottom: 1rem;">corporate_fare</span>
                        <p class="text-muted" style="font-size: 0.875rem;">No enterprise policies currently active.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div>

<?= $this->endSection() ?>