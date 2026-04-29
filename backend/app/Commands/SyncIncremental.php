<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Services\SyncService;

/**
 * Spark Command: Incremental Sync
 *
 * Usage: php spark sync:incremental
 * Schedule: Every 5 minutes via cron/task scheduler
 *
 * Crontab example (every 5 min):
 *   cd /path/to/backend && php spark sync:incremental >> writable/logs/sync.log 2>&1
 *
 * Windows Task Scheduler:
 *   Action: D:\xampp\php\php.exe
 *   Arguments: spark sync:incremental
 *   Start in: D:\Programs\Attendance software\backend
 */
class SyncIncremental extends BaseCommand
{
    protected $group = 'Sync';
    protected $name = 'sync:incremental';
    protected $description = 'Run incremental sync — fetch latest punches since last sync';
    protected $usage = 'sync:incremental';

    public function run(array $params)
    {
        CLI::write('Starting incremental sync...', 'yellow');

        try {
            $syncService = new SyncService();
            $result = $syncService->runIncremental();

            if ($result['status'] === 'success') {
                CLI::write('Incremental sync completed successfully!', 'green');
                CLI::write("  Records fetched: {$result['records_fetched']}", 'white');
                CLI::write("  Records saved: {$result['records_saved']}", 'white');

                if (!empty($result['dates_processed'])) {
                    CLI::write('  Dates processed: ' . implode(', ', $result['dates_processed']), 'white');
                }

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
            log_message('critical', '[SyncIncremental] ' . $e->getMessage());
        }
    }
}
