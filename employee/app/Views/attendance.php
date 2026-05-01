<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<div class="page-header">
    <p class="text-muted"
        style="text-transform: uppercase; letter-spacing: 0.1em; font-size: 0.75rem; font-weight: 600; margin-bottom: 0.5rem;">
        personal Registry</p>
    <h2 class="font-display">My Attendance</h2>
</div>

<div style="display: grid; grid-template-columns: repeat(12, 1fr); gap: 1.5rem;">
    <!-- Calendar View -->
    <div style="grid-column: span 8; display: flex; flex-direction: column; gap: 1.5rem;">
        <div class="card">
            <div class="card-header">
                <h3>Monthly Presence Log</h3>
                <form method="get" action="<?= site_url('attendance') ?>" style="display: flex; gap: 0.5rem;">
                    <select name="month" class="form-input"
                        style="padding: 0.375rem 0.75rem; font-size: 0.8125rem; width: auto;">
                        <?php for ($m = 1; $m <= 12; $m++): ?>
                            <option value="<?= $m ?>" <?= $m == $month ? 'selected' : '' ?>>
                                <?= date('F', mktime(0, 0, 0, $m, 1)) ?></option>
                        <?php endfor; ?>
                    </select>
                    <button type="submit" class="btn btn-outline"
                        style="padding: 0.375rem 0.75rem; font-size: 0.8125rem;">Filter</button>
                </form>
            </div>

            <div class="card-body">
                <!-- Professional Grid Calendar -->
                <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 0.5rem;">
                    <?php
                    $days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
                    foreach ($days as $day):
                        ?>
                        <div
                            style="text-align: center; font-size: 0.7rem; font-weight: 700; color: var(--color-text-dim); text-transform: uppercase; padding: 0.5rem;">
                            <?= $day ?></div>
                    <?php endforeach; ?>

                    <?php
                    for ($i = 0; $i < $firstDow + $daysInMonth; $i++):
                        $dayNum = $i - $firstDow + 1;
                        if ($dayNum < 1):
                            ?>
                            <div></div>
                        <?php else:
                            $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $dayNum);
                            $row = $byDate[$dateStr] ?? null;
                            $st = $row['status'] ?? null;
                            $hasData = ($st !== null);
                            ?>
                            <div
                                style="aspect-ratio: 1; border-radius: 8px; border: 1px solid <?= $hasData ? 'var(--color-border)' : 'transparent' ?>; background: <?= $st ? 'var(--color-surface-muted)' : 'transparent' ?>; display: flex; flex-direction: column; align-items: center; justify-content: center; position: relative;">
                                <span
                                    style="font-size: 0.9375rem; font-weight: <?= $st ? '700' : '400' ?>; color: <?= $st ? 'var(--color-primary)' : 'var(--color-text-dim)' ?>;"><?= $dayNum ?></span>
                                <?php if ($st): ?>
                                    <div
                                        style="width: 4px; height: 4px; border-radius: 50%; background: <?= $st === 'present' ? 'var(--color-success)' : ($st === 'half_day' ? 'var(--color-warning)' : 'var(--color-error)') ?>; margin-top: 0.25rem;">
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; endfor; ?>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3>Detailed Activity Log</h3>
            </div>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Service Date</th>
                            <th>Status</th>
                            <th>First In</th>
                            <th>Last Out</th>
                            <th>Work Hours</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_reverse($rows) as $r): ?>
                            <?php $st = $r['status'] ?? 'absent'; ?>
                            <tr>
                                <td style="font-weight: 500;"><?= date('D, d M', strtotime($r['date'])) ?></td>
                                <td><span
                                        class="badge badge--<?= esc($st) ?>"><?= esc(ucfirst(str_replace('_', ' ', $st))) ?></span>
                                </td>
                                <td><?= $r['first_in'] ? date('H:i', strtotime($r['first_in'])) : '—' ?></td>
                                <td><?= $r['last_out'] ? date('H:i', strtotime($r['last_out'])) : '—' ?></td>
                                <td style="font-weight: 600; color: var(--color-primary);">
                                    <?= esc((string) ($r['total_hours'] ?? '0')) ?>h</td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($rows)): ?>
                            <tr>
                                <td colspan="5" style="text-align: center; padding: 4rem; color: var(--color-text-dim);">No
                                    active service records found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Summary Sidebar -->
    <div style="grid-column: span 4; display: flex; flex-direction: column; gap: 1.5rem;">
        <div class="card">
            <div class="card-header">
                <h3>Metrics Summary</h3>
            </div>
            <div class="card-body" style="display: flex; flex-direction: column; gap: 1.25rem;">
                <?php
                $summaryCounts = ['present' => 0, 'half_day' => 0, 'absent' => 0];
                foreach ($rows as $r)
                    if (isset($summaryCounts[$r['status']]))
                        $summaryCounts[$r['status']]++;
                ?>
                <div
                    style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; background: #F0FDF4; border-radius: 8px;">
                    <span style="font-size: 0.8125rem; font-weight: 600; color: #166534;">Present Days</span>
                    <span
                        style="font-size: 1.25rem; font-weight: 700; color: #166534;"><?= $summaryCounts['present'] ?></span>
                </div>
                <div
                    style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; background: #FFFBEB; border-radius: 8px;">
                    <span style="font-size: 0.8125rem; font-weight: 600; color: #92400E;">Half Days</span>
                    <span
                        style="font-size: 1.25rem; font-weight: 700; color: #92400E;"><?= $summaryCounts['half_day'] ?></span>
                </div>
                <div
                    style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; background: #FEF2F2; border-radius: 8px;">
                    <span style="font-size: 0.8125rem; font-weight: 600; color: #991B1B;">Absent Days</span>
                    <span
                        style="font-size: 1.25rem; font-weight: 700; color: #991B1B;"><?= $summaryCounts['absent'] ?></span>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3>Attendance Directives</h3>
            </div>
            <div class="card-body">
                <ul
                    style="list-style: none; display: flex; flex-direction: column; gap: 1rem; font-size: 0.8125rem; color: var(--color-text-dim);">
                    <li style="display: flex; gap: 0.75rem;">
                        <span style="color: var(--color-accent); font-weight: 700;">&bull;</span>
                        <span>Service requirement: 8.0 hours per service day.</span>
                    </li>
                    <li style="display: flex; gap: 0.75rem;">
                        <span style="color: var(--color-accent); font-weight: 700;">&bull;</span>
                        <span>Partial attendance: 4.0 to 8.0 hours.</span>
                    </li>
                    <li style="display: flex; gap: 0.75rem;">
                        <span style="color: var(--color-accent); font-weight: 700;">&bull;</span>
                        <span>Threshold for tardiness: Service start + 15 minutes.</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>