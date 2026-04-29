<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;
use App\Services\DashboardService;

/**
 * Web DashboardController — Server-rendered Dashboard
 * 
 * GET /dashboard — Display dashboard with stats, roster, and live feed
 */
class DashboardController extends BaseController
{
    private DashboardService $dashboardService;

    public function __construct()
    {
        $this->dashboardService = new DashboardService();
    }

    /**
     * GET /dashboard
     */
    public function index()
    {
        $date = $this->request->getGet('date') ?? date('Y-m-d');

        try {
            $summary     = $this->dashboardService->getSummary($date);
            $livePunches = $this->dashboardService->getLivePunches(15, $date);
            $attendance  = $this->dashboardService->getAttendanceTable($date);
        } catch (\Throwable $e) {
            log_message('error', '[Web\\DashboardController] Error: ' . $e->getMessage());
            // Surface DB connection errors to the user via flash message
            if (strpos($e->getMessage(), 'Unable to connect') !== false || strpos($e->getMessage(), 'Connection refused') !== false) {
                session()->setFlashdata('error', 'Database connection failed. Please ensure MySQL is running in XAMPP Control Panel.');
            }
            $summary     = [];
            $livePunches = [];
            $attendance  = [];
        }

        return view('pages/dashboard', [
            'pageTitle'   => 'Dashboard',
            'activePage'  => 'dashboard',
            'date'        => $date,
            'summary'     => $summary,
            'livePunches' => $livePunches,
            'attendance'  => $attendance,
        ]);
    }
}
