<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Services\SyncService;
use App\Models\SyncLogModel;

/**
 * SyncController — Sync management endpoints
 * 
 * POST /api/sync/run    — Trigger manual sync
 * GET  /api/sync/status — Get sync history and status
 */
class SyncController extends ResourceController
{
    protected $format = 'json';

    /**
     * POST /api/sync/run
     * 
     * Body: { 
     *   type: "incremental" | "full" | "full_range",
     *   date: "YYYY-MM-DD" (optional, for full sync),
     *   from_date: "YYYY-MM-DD" (for full_range),
     *   to_date: "YYYY-MM-DD" (for full_range)
     * }
     */
    public function run()
    {
        $input = $this->request->getJSON(true) ?? [];
        $syncType = $input['type'] ?? 'incremental';

        try {
            $syncService = new SyncService();

            switch ($syncType) {
                case 'full':
                    $date = $input['date'] ?? date('Y-m-d');
                    $result = $syncService->runFull($date);
                    break;

                case 'full_range':
                    $fromDate = $input['from_date'] ?? date('Y-m-d');
                    $toDate   = $input['to_date'] ?? date('Y-m-d');
                    $result = $syncService->runFullRange($fromDate, $toDate);
                    break;

                case 'incremental':
                default:
                    $result = $syncService->runIncremental();
                    break;
            }

            $statusCode = ($result['status'] === 'success') ? 200 : 500;

            return $this->respond([
                'status' => $result['status'],
                'data'   => $result,
            ], $statusCode);

        } catch (\Throwable $e) {
            log_message('error', '[SyncController] Sync run error: ' . $e->getMessage());
            return $this->failServerError('Sync failed: ' . $e->getMessage());
        }
    }

    /**
     * GET /api/sync/status
     * 
     * Query params: ?limit=10 (optional)
     */
    public function status()
    {
        try {
            $limit = (int) ($this->request->getGet('limit') ?? 10);
            $syncLogModel = new SyncLogModel();

            $history    = $syncLogModel->getRecent($limit);
            $isRunning  = $syncLogModel->isRunning();
            $lastSync   = $syncLogModel->getLastSuccessful();

            return $this->respond([
                'status' => 'success',
                'data'   => [
                    'is_running'     => $isRunning,
                    'last_sync'      => $lastSync,
                    'history'        => $history,
                ],
            ]);
        } catch (\Throwable $e) {
            log_message('error', '[SyncController] Status error: ' . $e->getMessage());
            return $this->failServerError('Failed to fetch sync status');
        }
    }
}
