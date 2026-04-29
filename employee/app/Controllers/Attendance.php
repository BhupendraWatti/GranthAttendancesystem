<?php

namespace App\Controllers;

use App\Models\AttendanceDailyModel;

class Attendance extends BaseController
{
    public function index()
    {
        $empCode = (string) session()->get('empcode');
        $year = (int) ($this->request->getGet('year') ?: date('Y'));
        $month = (int) ($this->request->getGet('month') ?: date('m'));

        $rows = (new AttendanceDailyModel())->getMonthly($empCode, $year, $month);
        $byDate = [];
        foreach ($rows as &$r) {
            $r['total_hours'] = round(($r['work_minutes'] ?? 0) / 60, 2);
            $byDate[$r['date']] = $r;
        }
        unset($r);

        $start = sprintf('%04d-%02d-01', $year, $month);
        $daysInMonth = (int) date('t', strtotime($start));
        $firstDow = (int) date('w', strtotime($start)); // 0=Sun

        return view('attendance', [
            'year' => $year,
            'month' => $month,
            'rows' => $rows,
            'byDate' => $byDate,
            'daysInMonth' => $daysInMonth,
            'firstDow' => $firstDow,
        ]);
    }
}

