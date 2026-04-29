<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\AttendanceDailyModel;
use App\Models\EmployeeModel;
use App\Services\AttendanceService;

/**
 * AttendanceController — Attendance data endpoints
 * 
 * GET /api/attendance/daily   — Daily attendance report
 * GET /api/attendance/monthly — Monthly attendance report
 */
class AttendanceController extends ResourceController
{
    protected $format = 'json';

    // Shared service instance for monthly summary calculations
    private AttendanceService $attendanceService;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->attendanceService = \Config\Services::attendanceservice();
    }

    /**
     * GET /api/attendance/daily
     * 
     * Query params: ?date=YYYY-MM-DD (required)
     */
    public function daily()
    {
        $date = $this->request->getGet('date');

        if (empty($date)) {
            $date = date('Y-m-d');
        }

        try {
            $model = new AttendanceDailyModel();
            $records = $model->getForDate($date);

            return $this->respond([
                'status' => 'success',
                'data'   => [
                    'date'    => $date,
                    'records' => $records,
                    'total'   => count($records),
                ],
            ]);
        } catch (\Throwable $e) {
            log_message('error', '[AttendanceController] Daily error: ' . $e->getMessage());
            return $this->failServerError('Failed to fetch daily attendance');
        }
    }

    /**
     * GET /api/attendance/monthly
     * 
     * Query params: 
     *   ?year=YYYY (required)
     *   &month=M (required)
     *   &emp_code=EMP001 (optional — if omitted, returns all employees)
     */
    public function monthly()
    {
        $year    = (int) ($this->request->getGet('year') ?? date('Y'));
        $month   = (int) ($this->request->getGet('month') ?? date('n'));
        $empCode = $this->request->getGet('emp_code');

        try {
            $model = new AttendanceDailyModel();

            if (!empty($empCode)) {
                $records = $model->getMonthly($empCode, $year, $month);
            } else {
                $records = $model->getAllMonthly($year, $month);
            }

            // Calculate summary stats using AttendanceService
            $summary = $this->attendanceService->calculateMonthlySummary($records);

            return $this->respond([
                'status' => 'success',
                'data'   => [
                    'year'    => $year,
                    'month'   => $month,
                    'records' => $records,
                    'summary' => $summary,
                    'total'   => count($records),
                ],
            ]);
        } catch (\Throwable $e) {
            log_message('error', '[AttendanceController] Monthly error: ' . $e->getMessage());
            return $this->failServerError('Failed to fetch monthly attendance');
        }
    }
}
