<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Services\SyncService;

class ForceSync extends BaseCommand
{
    protected $group       = 'Sync';
    protected $name        = 'sync:force';
    protected $description = 'Forces the sync service to run outside the web context.';

    public function run(array $params)
    {
        $service = new SyncService();
        CLI::write("Running Incremental Sync...");
        $res1 = $service->runIncremental();
        print_r($res1);

        CLI::write("Running Full Range Sync (-3 days)...");
        $fromDate = date('Y-m-d', strtotime('-3 days'));
        $toDate = date('Y-m-d');
        $res2 = $service->runFullRange($fromDate, $toDate);
        print_r($res2);
    }
}
