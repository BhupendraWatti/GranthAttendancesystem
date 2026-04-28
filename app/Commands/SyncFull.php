<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Services\SyncService;

/**
 * Spark Command: Full Sync
 * 
 * Usage: php spark sync:full [date]
 * Schedule: Daily at midnight via cron/task scheduler
 * 
 * Crontab example:
 *   0 0 * * * cd /path/to/backend && php spark sync:full >> writable/logs/sync.log 2>&1
 *
 * Windows Task Scheduler:
 *   Action: D:\xampp\php\php.exe
 *   Arguments: spark sync:full
 *   Start in: D:\Programs\Attendance software\backend
 */
class SyncFull extends BaseCommand
{
    protected $group       = 'Sync';
    protected $name        = 'sync:full';
    protected $description = 'Run full sync — fetch all punches for a date (default: today)';
    protected $usage       = 'sync:full [date]';
    protected $arguments   = [
        'date' => 'Date to sync in YYYY-MM-DD format (optional, defaults to today)',
    ];

    public function run(array $params)
    {
        $date = $params[0] ?? date('Y-m-d');
        $lockPath = WRITEPATH . 'sync_full.lock';

        CLI::write("Starting full sync for date: {$date}...", 'yellow');

        if (is_file($lockPath)) {
            CLI::write('Another sync:full run is already in progress. Skipping.', 'yellow');
            log_message('warning', '[SyncFull] Execution skipped due to lock file');
            return;
        }

        try {
            file_put_contents($lockPath, (string) time());
            log_message('info', '[SyncFull] Step start: runFull');
            $syncService = new SyncService();
            $result = $syncService->runFull($date);
            log_message('info', '[SyncFull] Step end: runFull');

            if ($result['status'] === 'success') {
                CLI::write('Full sync completed successfully!', 'green');
                CLI::write("  Date: {$result['date']}", 'white');
                CLI::write("  Records fetched: {$result['records_fetched']}", 'white');
                CLI::write("  Records saved: {$result['records_saved']}", 'white');

                if (!empty($result['validation'])) {
                    $v = $result['validation'];
                    if ($v['total'] > 0) {
                        CLI::write("  Validation: {$v['errors']} errors, {$v['warnings']} warnings", 'yellow');
                    }
                }
            } elseif ($result['status'] === 'skipped') {
                CLI::write("Sync skipped: {$result['reason']}", 'yellow');
            } else {
                CLI::error("Sync failed: " . ($result['error'] ?? 'Unknown error'));
            }

        } catch (\Throwable $e) {
            CLI::error('Fatal error: ' . $e->getMessage());
            log_message('critical', '[SyncFull] ' . $e->getMessage());
        } finally {
            if (is_file($lockPath)) {
                @unlink($lockPath);
            }
        }
    }
}
