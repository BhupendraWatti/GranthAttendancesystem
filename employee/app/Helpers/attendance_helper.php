<?php

/**
 * Attendance Helper
 * 
 * Centralized logic for attendance related calculations.
 */

if (!function_exists('isWeekendOff')) {
    /**
     * Determines if a given date is a weekend off based on business rules:
     * - Sunday = Always Off
     * - 1st Saturday = Off
     * - 3rd Saturday = Off
     * - All other days = Working Day
     * 
     * @param string $date Date in Y-m-d format
     * @return bool True if it's a weekend off, false otherwise
     */
    function isWeekendOff(string $date): bool
    {
        $timestamp = strtotime($date);
        $dow = (int)date('w', $timestamp); // 0 (Sunday) to 6 (Saturday)

        // 1. Sunday is always off
        if ($dow === 0) {
            return true;
        }

        // 2. Check for 1st and 3rd Saturday
        if ($dow === 6) {
            $dayOfMonth = (int)date('d', $timestamp);
            $month = date('m', $timestamp);
            $year = date('Y', $timestamp);
            
            $satCount = 0;
            // Iterate from the 1st of the month to the current day
            for ($d = 1; $d <= $dayOfMonth; $d++) {
                $time = mktime(0, 0, 0, $month, $d, $year);
                if ((int)date('w', $time) === 6) {
                    $satCount++;
                }
            }

            // Return true if it's the 1st or 3rd Saturday
            if ($satCount === 1 || $satCount === 3) {
                return true;
            }
        }

        return false;
    }
}
