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
        
        // Fill gaps for full month visibility
        helper('attendance');
        $holidayModel = new \App\Models\HolidayModel();
        $indexed = [];
        foreach ($rows as $r) {
            $indexed[$r['date']] = $r;
        }

        $fullRows = [];
        $daysInMonth = (int) date('t', mktime(0, 0, 0, $month, 1, $year));
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $d);
            if (isset($indexed[$dateStr])) {
                $fullRows[] = $indexed[$dateStr];
            } else {
                $dayType = 'working_day';
                if (isWeekendOff($dateStr)) {
                    $dayType = 'weekend';
                } elseif ($holidayModel->isHoliday($dateStr)) {
                    $dayType = 'holiday';
                }

                $fullRows[] = [
                    'emp_code' => $empCode,
                    'date' => $dateStr,
                    'first_in' => null,
                    'last_out' => null,
                    'work_minutes' => 0,
                    'late_minutes' => 0,
                    'status' => 'absent',
                    'attendance_status' => 'absent',
                    'day_type' => $dayType,
                    'work_mode' => null,
                    'punch_count' => 0,
                ];
            }
        }
        $rows = $fullRows;

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
            'empCode' => $empCode,
        ]);
    }
}

