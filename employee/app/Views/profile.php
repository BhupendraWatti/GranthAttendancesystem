<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<?php
  $name = $employee['name'] ?? 'Associate';
  $empCode = $employee['emp_code'] ?? 'N/A';
  $email = $employee['email'] ?? 'No corporate email assigned';
  $designation = $employee['designation'] ?? 'Personnel Associate';
?>

<div class="page-header">
    <p class="text-muted" style="text-transform: uppercase; letter-spacing: 0.1em; font-size: 0.75rem; font-weight: 600; margin-bottom: 0.5rem;">Identity Profile</p>
    <h2 class="font-display">Personnel Identity</h2>
</div>

<div style="display: grid; grid-template-columns: repeat(12, 1fr); gap: 1.5rem;">
    <!-- Profile Header Card -->
    <div style="grid-column: span 8;">
        <div class="card" style="display: flex; flex-direction: row; gap: 2.5rem; align-items: center; padding: 3rem;">
            <div style="width: 120px; height: 120px; border-radius: 12px; background: var(--color-surface-muted); border: 1px solid var(--color-border); display: flex; align-items: center; justify-content: center; font-family: var(--font-display); font-size: 3rem; color: var(--color-primary); font-weight: 800; box-shadow: var(--shadow-sm);">
                <?= strtoupper(substr($name, 0, 1)) ?>
            </div>
            
            <div style="flex: 1;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <div>
                        <h3 class="font-display" style="font-size: 2rem; margin-bottom: 0.25rem; color: var(--color-primary);"><?= esc($name) ?></h3>
                        <p style="color: var(--color-accent); font-weight: 700; font-size: 0.875rem; text-transform: uppercase; letter-spacing: 0.05em;"><?= esc($designation) ?></p>
                    </div>
                    <span class="badge" style="background: #F0FDF4; color: #166534; border: 1px solid #BBF7D0;">Active Service</span>
                </div>
                
                <div style="display: flex; gap: 3rem; margin-top: 2rem;">
                    <div>
                        <div style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; color: var(--color-text-dim); margin-bottom: 0.25rem; letter-spacing: 0.05em;">Corporate Email</div>
                        <div style="font-size: 0.9375rem; font-weight: 600; color: var(--color-text-main);"><?= esc($email) ?></div>
                    </div>
                    <div>
                        <div style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; color: var(--color-text-dim); margin-bottom: 0.25rem; letter-spacing: 0.05em;">Personnel Code</div>
                        <div style="font-size: 0.9375rem; font-weight: 600; color: var(--color-text-main); letter-spacing: 0.05em;"><?= esc($empCode) ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Registry Details -->
    <div style="grid-column: span 4;">
        <div class="card" style="height: 100%;">
            <div class="card-header">
                <h3>Employment Registry</h3>
            </div>
            <div class="card-body" style="display: flex; flex-direction: column; gap: 1.5rem;">
                <div>
                    <label class="text-muted" style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; display: block; margin-bottom: 0.375rem;">Department</label>
                    <div style="font-size: 1rem; font-weight: 600;"><?= esc($employee['department'] ?? 'General Ops') ?></div>
                </div>
                
                <div>
                    <label class="text-muted" style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; display: block; margin-bottom: 0.375rem;">Service Category</label>
                    <div style="font-size: 1rem; font-weight: 600;"><?= ucwords(str_replace('_', ' ', $employee['employee_type'] ?? 'Full Time')) ?></div>
                </div>

                <div>
                    <label class="text-muted" style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; display: block; margin-bottom: 0.375rem;">Joining Date</label>
                    <div style="font-size: 1rem; font-weight: 600;"><?= date('d M Y', strtotime($employee['created_at'] ?? 'now')) ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
