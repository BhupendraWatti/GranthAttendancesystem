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
        $month = (int) date('n');
        $rows = $dailyModel->getMonthly($empCode, $year, $month);

        // Fill gaps for full month visibility on dashboard
        helper('attendance');
        $holidayModel = new \App\Models\HolidayModel();
        $indexed = [];
        foreach ($rows as $r) {
            $indexed[$r['date']] = $r;
        }

        $fullMonthRows = [];
        $daysToProcess = (int)date('d'); // Only show up to today on dashboard recent activity
        for ($d = 1; $d <= $daysToProcess; $d++) {
            $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $d);
            if (isset($indexed[$dateStr])) {
                $fullMonthRows[] = $indexed[$dateStr];
            } else {
                $dayType = 'working_day';
                if (isWeekendOff($dateStr)) {
                    $dayType = 'weekend';
                } elseif ($holidayModel->isHoliday($dateStr)) {
                    $dayType = 'holiday';
                }

                $fullMonthRows[] = [
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
        $monthRows = $fullMonthRows;

        $totalMinutesMonth = 0;
        foreach ($monthRows as &$r) {
            $r['total_hours'] = round(($r['work_minutes'] ?? 0) / 60, 2);
            $totalMinutesMonth += (int)($r['work_minutes'] ?? 0);
        }
        unset($r);

        // Update TodayRow if it was missing in DB
        if (empty($todayRow)) {
            $todayRow = end($monthRows);
        }

        // Fixed Monthly Goal: 204 hours (24 days * 8.5 hours)
        $requiredHoursMonth = 204.0;
        $totalHoursMonth = round($totalMinutesMonth / 60, 2);

        $counts = ['present' => 0, 'half_day' => 0, 'absent' => 0, 'work_from_home' => 0];
        foreach ($monthRows as $r) {
            $st = $r['attendance_status'] ?? $r['status'] ?? 'absent';
            $dayType = $r['day_type'] ?? 'working_day';
            
            // Only count absences on working days
            if (($dayType === 'weekend' || $dayType === 'holiday') && $st === 'absent' && empty($r['first_in'])) {
                continue;
            }

            if (isset($counts[$st])) {
                $counts[$st]++;
            }
        }

        $recent = array_slice(array_reverse($monthRows), 0, 5);

        return view('dashboard', [
            'employee' => $employee,
            'todayRow' => $todayRow,
            'todayHours' => $this->formatHours($todayRow['total_hours'] ?? null),
            'counts' => $counts,
            'recent' => $recent,
            'totalHoursMonth' => $totalHoursMonth,
            'requiredHoursMonth' => $requiredHoursMonth,
        ]);
    }
}

