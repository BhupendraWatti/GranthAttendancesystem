<?php

namespace App\Controllers;

use App\Models\AttendanceDailyModel;
use App\Models\EmployeeModel;

class Dashboard extends BaseController
{
    private function formatHours(?string $decimalHours): string
    {
        $h = (float) ($decimalHours ?? 0);
        $totalMinutes = (int) round($h * 60);
        $hours = intdiv($totalMinutes, 60);
        $minutes = $totalMinutes % 60;
        return sprintf('%dh %02dm', $hours, $minutes);
    }

    public function index()
    {
        $empCode = (string) session()->get('empcode');
        $today = date('Y-m-d');

        $employee = (new EmployeeModel())->findByCode($empCode);
        $dailyModel = new AttendanceDailyModel();
        $todayRow = $dailyModel->where('emp_code', $empCode)->where('date', $today)->first() ?? [];
        if (!empty($todayRow)) {
            $todayRow['total_hours'] = round(($todayRow['work_minutes'] ?? 0) / 60, 2);
        }

        $year = (int) date('Y');
        $month = (int) date('m');
        $monthRows = $dailyModel->getMonthly($empCode, $year, $month);

        foreach ($monthRows as &$r) {
            $r['total_hours'] = round(($r['work_minutes'] ?? 0) / 60, 2);
        }
        unset($r);

        $counts = ['present' => 0, 'half_day' => 0, 'absent' => 0];
        foreach ($monthRows as $r) {
            $status = $r['status'] ?? '';
            if (isset($counts[$status])) {
                $counts[$status]++;
            }
        }

        $recent = array_slice(array_reverse($monthRows), 0, 5);

        return view('dashboard', [
            'employee' => $employee,
            'todayRow' => $todayRow,
            'todayHours' => $this->formatHours($todayRow['total_hours'] ?? null),
            'counts' => $counts,
            'recent' => $recent,
        ]);
    }
}

