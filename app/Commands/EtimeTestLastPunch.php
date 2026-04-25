<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Services;

/**
 * Smoke-test DownloadLastPunchData using the same ApiService code path as the web app.
 * Produces log lines with [LastPunch-rawQS] and literal LastRecord=$ in the URL.
 *
 * Usage: php spark etime:test-lastpunch [LastRecordId]
 */
class EtimeTestLastPunch extends BaseCommand
{
    protected $group       = 'Etime';
    protected $name        = 'etime:test-lastpunch';
    protected $description = 'CLI smoke test for DownloadLastPunchData (current ApiService / LastPunch-rawQS)';
    protected $usage       = 'etime:test-lastpunch [<LastRecordId>]';

    public function run(array $params)
    {
        $id = $params[0] ?? '';
        if ($id === '') {
            $id = Services::apiservice()->generateLastRecordId(null, 1);
        }

        CLI::write("Using LastRecord={$id}", 'yellow');
        log_message('info', "[EtimeTestLastPunch] CLI invoking downloadLastPunchData LastRecord={$id}");

        $out = Services::apiservice()->downloadLastPunchData($id);

        if ($out['success'] ?? false) {
            CLI::write('Result: success, HTTP ' . ($out['status'] ?? '?'), 'green');
        } else {
            CLI::error('Result: failed — ' . ($out['error'] ?? 'unknown'));
        }

        CLI::write('See writable/logs (today) for: DownloadLastPunchData ... [LastPunch-rawQS]', 'white');
    }
}
