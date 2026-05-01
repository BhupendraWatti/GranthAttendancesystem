<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;
use App\Services\SyncService;
use App\Models\SyncLogModel;

/**
 * Web SyncController — Server-rendered Sync page
 * 
 * GET  /sync     — Display sync status and history
 * POST /sync/run — Trigger sync operation
 */
class SyncController extends BaseController
{
    /**
     * GET /sync — Show sync page
     */
    public function index()
    {
        $history   = [];
        $isRunning = false;
        $lastSync  = null;

        try {
            $syncLogModel = new SyncLogModel();
            $history   = $syncLogModel->getRecent(20);
            $isRunning = $syncLogModel->isRunning();
            $lastSync  = $syncLogModel->getLastSuccessful();
        } catch (\Throwable $e) {
            log_message('error', '[Web\\SyncController] index error: ' . $e->getMessage());
            if (strpos($e->getMessage(), 'Unable to connect') !== false || strpos($e->getMessage(), 'Connection refused') !== false) {
                session()->setFlashdata('error', 'Database connection failed. Please start MySQL in XAMPP Control Panel before syncing.');
            } else {
                session()->setFlashdata('error', 'Failed to load sync history: ' . $e->getMessage());
            }
        }

        return view('pages/sync', [
            'pageTitle'  => 'Sync Data',
            'activePage' => 'sync',
            'history'    => $history,
            'isRunning'  => $isRunning,
            'lastSync'   => $lastSync,
        ]);
    }

    /**
     * POST /sync/run — Execute sync
     */
    public function run()
    {
        $syncType = $this->request->getPost('type') ?? 'incremental';

        try {
            $syncService = new SyncService();

            switch ($syncType) {
                case 'full':
                    $date   = $this->request->getPost('date') ?? date('Y-m-d');
                    $result = $syncService->runFull($date);
                    break;

                case 'full_range':
                    $fromDate = $this->request->getPost('from_date') ?? date('Y-m-d');
                    $toDate   = $this->request->getPost('to_date') ?? date('Y-m-d');
                    $result   = $syncService->runFullRange($fromDate, $toDate);
                    break;

                case 'incremental':
                default:
                    $result = $syncService->runIncremental();
                    break;
            }

            if (($result['status'] ?? '') === 'success') {
                $fetched = (int) ($result['records_fetched'] ?? 0);
                $saved   = (int) ($result['records_saved'] ?? 0);
                $msg     = "Sync completed! Fetched: {$fetched}, Saved: {$saved}";
                if ($fetched === 0 && $saved === 0) {
                    $msg .= ' No punch rows were imported. eTimeOffice often returns HTTP 500 for “today”; try Full Sync with From = first of month and To = yesterday (or another past range), then check backend/writable/logs. Incremental only pulls since the last sync cursor.';

                    return redirect()->to(site_url('sync'))->with('warning', $msg);
                }

                return redirect()->to(site_url('sync'))->with('success', $msg);
            } else {
                $error = $result['error'] ?? 'Unknown error';
                return redirect()->to(site_url('sync'))->with('error', "Sync failed: {$error}");
            }

        } catch (\Throwable $e) {
            log_message('error', '[Web\\SyncController] Sync error: ' . $e->getMessage());
            return redirect()->to(site_url('sync'))->with('error', 'Sync failed: ' . $e->getMessage());
        }
    }
}
