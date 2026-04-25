<?php

namespace App\Services;

/**
 * NormalizationService — API Response Normalizer
 * 
 * Converts all eTimeOffice API responses into a unified internal format.
 * Handles varying response structures from different API endpoints.
 */
class NormalizationService
{
    /**
     * Standard normalized punch record structure
     */
    private array $template = [
        'emp_code'   => '',
        'name'       => '',
        'punch_time' => '',
        'source'     => 'api',
    ];

    /**
     * Normalize API response from DownloadPunchData
     * Expected format: array of records with punch timestamp info
     *
     * @param array $apiResponse Raw API response
     * @param string $source Source identifier
     * @return array Normalized punch records
     */
    public function normalizePunchData(array $apiResponse, string $source = 'DownloadPunchData'): array
    {
        $normalized = [];
        $data = $apiResponse['data'] ?? $apiResponse;

        if (!is_array($data)) {
            log_message('warning', "[NormalizationService] Non-array data received for {$source}");
            return [];
        }

        // Handle nested responses based on API endpoint
        if (isset($data['InOutPunchData']) && is_array($data['InOutPunchData'])) {
            $data = $data['InOutPunchData'];
        } elseif (isset($data['PunchData']) && is_array($data['PunchData'])) {
            $data = $data['PunchData'];
        }

        if ($source === 'DownloadPunchDataMCID' && is_array($data)) {
            foreach (['PunchDataMCID', 'MCIDPunchData'] as $mcidKey) {
                if (isset($data[$mcidKey]) && is_array($data[$mcidKey])) {
                    $data = $data[$mcidKey];
                    break;
                }
            }
        }

        foreach ($data as $record) {
            // InOutPunchData gives us IN and OUT in a single record.
            if ($source === 'DownloadInOutPunchData') {
                $parsedInOut = $this->parseInOutRecord($record, $source);
                foreach ($parsedInOut as $p) {
                    $normalized[] = $p;
                }
            } else {
                $parsed = $this->parseRecord($record, $source);
                if ($parsed !== null) {
                    $normalized[] = $parsed;
                }
            }
        }

        log_message('info', "[NormalizationService] Normalized " . count($normalized) . " records from {$source}");

        return $normalized;
    }

    /**
     * Normalize API response from DownloadLastPunchData
     * May include a LastRecordId field for incremental sync tracking
     *
     * @param array $apiResponse Raw API response
     * @return array ['records' => normalized records, 'last_record_id' => string|null]
     */
    public function normalizeLastPunchData(array $apiResponse): array
    {
        $data = $apiResponse['data'] ?? $apiResponse;
        $lastRecordId = null;
        $records = [];

        if (is_array($data)) {
            // Extract last record ID if present
            if (isset($data['LastRecordId']) || isset($data['LastRecord']) || isset($data['last_record_id'])) {
                $lastRecordId = $data['LastRecordId'] ?? $data['LastRecord'] ?? $data['last_record_id'];
                unset($data['LastRecordId'], $data['LastRecord'], $data['last_record_id']);
            }

            // Check for nested data array
            $punchData = $data['PunchData'] ?? $data['DownloadLastPunchData'] ?? $data;

            if (is_array($punchData)) {
                foreach ($punchData as $record) {
                    $parsed = $this->parseRecord($record, 'DownloadLastPunchData');
                    if ($parsed !== null) {
                        $records[] = $parsed;
                    }
                }
            }
        }

        return [
            'records'        => $records,
            'last_record_id' => $lastRecordId,
        ];
    }

    /**
     * Parse a single API record into normalized format
     *
     * @param mixed $record Single record from API
     * @param string $source Source identifier
     * @return array|null Normalized record or null if invalid
     */
    private function parseRecord($record, string $source): ?array
    {
        if (!is_array($record)) {
            return null;
        }

        // Try to extract employee code (various possible field names)
        $empCode = $this->extractField($record, [
            'EmpCode', 'Empcode', 'empcode', 'emp_code', 'EmployeeCode',
            'EmpId', 'empid', 'emp_id',
        ]);

        if (empty($empCode)) {
            log_message('debug', "[NormalizationService] Skipping record with no emp_code: " . json_encode($record));
            return null;
        }

        // Try to extract employee name
        $name = $this->extractField($record, [
            'Name', 'name', 'EmpName', 'EmployeeName', 'emp_name',
        ]) ?? '';

        // Try to extract punch time
        $punchTime = $this->extractPunchTime($record);

        if (empty($punchTime)) {
            log_message('debug', "[NormalizationService] Skipping record with no punch_time for emp: {$empCode}");
            return null;
        }

        return [
            'emp_code'   => trim($empCode),
            'name'       => trim($name),
            'punch_time' => $punchTime,
            'source'     => $source,
            'raw_data'   => $record,
        ];
    }

    /**
     * Parse InOutPunchData which contains INTime, OUTTime, DateString
     */
    private function parseInOutRecord(array $record, string $source): array
    {
        $punches = [];
        
        $empCode = $this->extractField($record, ['EmpCode', 'Empcode']);
        if (empty($empCode)) return [];
        
        $name = $this->extractField($record, ['Name', 'EmpName']) ?? '';
        $dateStr = $this->extractField($record, ['DateString']);
        if (empty($dateStr)) return [];

        $inTime = $this->extractField($record, ['INTime']);
        $outTime = $this->extractField($record, ['OUTTime']);

        // Format is dd/mm/yyyy for DateString and HH:MM for time
        if ($inTime && $inTime !== '--:--') {
            $parsedIn = $this->parseDateTime($dateStr . ' ' . $inTime);
            if ($parsedIn) {
                $punches[] = [
                    'emp_code'   => trim($empCode),
                    'name'       => trim($name),
                    'punch_time' => $parsedIn,
                    'source'     => $source,
                    'raw_data'   => $record,
                ];
            }
        }

        if ($outTime && $outTime !== '--:--') {
            $parsedOut = $this->parseDateTime($dateStr . ' ' . $outTime);
            if ($parsedOut) {
                $punches[] = [
                    'emp_code'   => trim($empCode),
                    'name'       => trim($name),
                    'punch_time' => $parsedOut,
                    'source'     => $source,
                    'raw_data'   => $record,
                ];
            }
        }

        return $punches;
    }

    /**
     * Extract a field from record trying multiple possible key names
     */
    private function extractField(array $record, array $possibleKeys): ?string
    {
        foreach ($possibleKeys as $key) {
            if (isset($record[$key]) && $record[$key] !== '') {
                return (string) $record[$key];
            }
        }
        return null;
    }

    /**
     * Extract and parse punch time from various possible formats
     */
    private function extractPunchTime(array $record): ?string
    {
        // Possible field names for punch datetime
        $timeFields = [
            'PunchDate', 'DateTimeRecord', 'PunchTime', 'punchtime', 'punch_time',
            'DateTime', 'datetime', 'LogDate', 'LogTime',
            'AttendanceDate', 'DateTimeIn', 'PunchDateTime',
        ];

        $rawTime = $this->extractField($record, $timeFields);

        if ($rawTime === null) {
            // Try combining separate Date and Time fields
            $date = $this->extractField($record, ['Date', 'date', 'LogDate', 'AttDate']);
            $time = $this->extractField($record, ['Time', 'time', 'LogTime', 'AttTime']);

            if ($date && $time) {
                $rawTime = $date . ' ' . $time;
            }
        }

        if (empty($rawTime)) {
            return null;
        }

        return $this->parseDateTime($rawTime);
    }

    /**
     * Parse various datetime formats into Y-m-d H:i:s
     */
    public function parseDateTime(string $datetime): ?string
    {
        $datetime = trim($datetime);

        // List of known formats to try
        $formats = [
            'Y-m-d H:i:s',           // 2026-04-23 10:30:00
            'Y-m-d\TH:i:s',          // 2026-04-23T10:30:00
            'Y-m-d\TH:i:s.u',        // 2026-04-23T10:30:00.000
            'd/m/Y H:i:s',           // 23/04/2026 10:30:00
            'd/m/Y H:i',             // 23/04/2026 10:30
            'd/m/Y_H:i',             // 23/04/2026_10:30
            'd-m-Y H:i:s',           // 23-04-2026 10:30:00
            'd-m-Y H:i',             // 23-04-2026 10:30
            'm/d/Y H:i:s',           // 04/23/2026 10:30:00
            'Y/m/d H:i:s',           // 2026/04/23 10:30:00
            'd/m/Y h:i:s A',         // 23/04/2026 10:30:00 AM
            'Y-m-d',                 // 2026-04-23
            'd/m/Y',                 // 23/04/2026
        ];

        foreach ($formats as $format) {
            $dt = \DateTime::createFromFormat($format, $datetime);
            if ($dt !== false) {
                return $dt->format('Y-m-d H:i:s');
            }
        }

        // Try PHP's flexible parsing as last resort
        try {
            $dt = new \DateTime($datetime);
            return $dt->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            log_message('error', "[NormalizationService] Cannot parse datetime: {$datetime}");
            return null;
        }
    }
}
