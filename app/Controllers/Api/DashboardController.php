<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Services\DashboardService;

/**
 * DashboardController — Dashboard data endpoints
 * 
 * GET /api/dashboard/summary      — Today's attendance metrics
 * GET /api/dashboard/attendance   — Today's attendance table rows
 * GET /api/dashboard/live-punches — Recent punch entries feed
 */
class DashboardController extends ResourceController
{
    protected $format = 'json';
    private DashboardService $dashboardService;

    public function __construct()
    {
        $this->dashboardService = new DashboardService();
    }

    /**
     * GET /api/dashboard/summary
     * 
     * Query params: ?date=YYYY-MM-DD (optional, defaults to today)
     * 
     * This endpoint is read-only and reflects current DB state.
     */
    public function summary()
    {
        $date = $this->request->getGet('date') ?? date('Y-m-d');
        try {
            $summary = $this->dashboardService->getSummary($date);
            return $this->respond([
                'status' => 'success',
                'data'   => $summary,
            ]);
        } catch (\Throwable $e) {
            log_message('error', '[DashboardController] Summary error: ' . $e->getMessage());
            return $this->failServerError('Failed to fetch dashboard summary');
        }
    }

    /**
     * GET /api/dashboard/attendance
     */
    public function attendance()
    {
        $date = $this->request->getGet('date') ?? date('Y-m-d');
        try {
            $rows = $this->dashboardService->getAttendanceTable($date);
            return $this->respond([
                'status' => 'success',
                'data'   => $rows,
            ]);
        } catch (\Throwable $e) {
            log_message('error', '[DashboardController] Attendance error: ' . $e->getMessage());
            return $this->failServerError('Failed to fetch attendance table');
        }
    }

    /**
     * GET /api/dashboard/live-punches
     * 
     * Query params: ?limit=20 (optional)
     */
    public function livePunches()
    {
        $limit = (int) ($this->request->getGet('limit') ?? 20);
        $limit = min(max($limit, 5), 100); // Clamp between 5 and 100

        try {
            $punches = $this->dashboardService->getLivePunches($limit);

            return $this->respond([
                'status' => 'success',
                'data'   => $punches,
            ]);
        } catch (\Throwable $e) {
            log_message('error', '[DashboardController] Live punches error: ' . $e->getMessage());
            return $this->failServerError('Failed to fetch live punches');
        }
    }
}
