<?php

namespace App\Services;

/**
 * ValidationService — Data Quality Assurance Layer
 * 
 * Validates punch and attendance data for integrity issues.
 * Returns structured errors — does NOT auto-fix silently.
 */
class ValidationService
{
    private array $errors = [];

    /**
     * Validate a batch of normalized punch records
     *
     * @param array $records Normalized punch records
     * @return array Validated records (invalid ones are flagged, not removed)
     */
    public function validatePunchRecords(array $records): array
    {
        $this->errors = [];
        $seen = [];

        foreach ($records as $index => &$record) {
            // Check for invalid timestamps
            if (empty($record['punch_time'])) {
                $this->addError('invalid_timestamp', $record['emp_code'] ?? 'unknown', null, 
                    "Record #{$index}: Missing punch_time", 'error');
                continue;
            }

            $punchDt = \DateTime::createFromFormat('Y-m-d H:i:s', $record['punch_time']);
            if ($punchDt === false) {
                $this->addError('invalid_timestamp', $record['emp_code'], null,
                    "Record #{$index}: Invalid timestamp format: {$record['punch_time']}", 'error');
                continue;
            }

            // Check for future timestamps
            $now = new \DateTime();
            if ($punchDt > $now) {
                $this->addError('future_timestamp', $record['emp_code'], $punchDt->format('Y-m-d'),
                    "Punch time is in the future: {$record['punch_time']}", 'warning');
            }

            // Check for very old timestamps (older than 1 year)
            $oneYearAgo = (new \DateTime())->modify('-1 year');
            if ($punchDt < $oneYearAgo) {
                $this->addError('old_timestamp', $record['emp_code'], $punchDt->format('Y-m-d'),
                    "Punch time is older than 1 year: {$record['punch_time']}", 'warning');
            }

            // Check for duplicates within this batch
            $key = $record['emp_code'] . '|' . $record['punch_time'];
            if (isset($seen[$key])) {
                $this->addError('duplicate', $record['emp_code'], $punchDt->format('Y-m-d'),
                    "Duplicate punch in batch: {$record['punch_time']}", 'warning');
            }
            $seen[$key] = true;
        }

        return $records;
    }

    /**
     * Validate processed attendance record
     *
     * @param array $attendance Processed attendance record
     * @return array Validation errors for this record
     */
    public function validateAttendance(array $attendance): array
    {
        $recordErrors = [];

        // Check missing OUT punch (only first_in, no last_out, or same value)
        if (!empty($attendance['first_in']) && empty($attendance['last_out'])) {
            $recordErrors[] = [
                'type'     => 'missing_out',
                'emp_code' => $attendance['emp_code'],
                'date'     => $attendance['date'],
                'message'  => 'Missing OUT punch — only IN punch recorded',
                'severity' => 'warning',
            ];
        }

        // Check negative work time
        if (($attendance['work_minutes'] ?? 0) < 0) {
            $recordErrors[] = [
                'type'     => 'negative_work',
                'emp_code' => $attendance['emp_code'],
                'date'     => $attendance['date'],
                'message'  => "Negative work minutes: {$attendance['work_minutes']}",
                'severity' => 'error',
            ];
        }

        // Check unreasonably long work time (> 16 hours)
        if (($attendance['work_minutes'] ?? 0) > 960) {
            $recordErrors[] = [
                'type'     => 'excessive_work',
                'emp_code' => $attendance['emp_code'],
                'date'     => $attendance['date'],
                'message'  => "Work minutes exceed 16 hours: {$attendance['work_minutes']}",
                'severity' => 'warning',
            ];
        }

        // Check if first_in is after last_out
        if (!empty($attendance['first_in']) && !empty($attendance['last_out'])) {
            $inTime  = new \DateTime($attendance['first_in']);
            $outTime = new \DateTime($attendance['last_out']);
            if ($inTime > $outTime) {
                $recordErrors[] = [
                    'type'     => 'invalid_time_order',
                    'emp_code' => $attendance['emp_code'],
                    'date'     => $attendance['date'],
                    'message'  => "first_in ({$attendance['first_in']}) is after last_out ({$attendance['last_out']})",
                    'severity' => 'error',
                ];
            }
        }

        return $recordErrors;
    }

    /**
     * Add a structured error
     */
    private function addError(string $type, string $empCode, ?string $date, string $message, string $severity = 'warning'): void
    {
        $this->errors[] = [
            'type'     => $type,
            'emp_code' => $empCode,
            'date'     => $date,
            'message'  => $message,
            'severity' => $severity,
        ];
    }

    /**
     * Get all accumulated errors
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Check if there are any critical errors
     */
    public function hasCriticalErrors(): bool
    {
        foreach ($this->errors as $error) {
            if ($error['severity'] === 'error') {
                return true;
            }
        }
        return false;
    }

    /**
     * Clear accumulated errors
     */
    public function clearErrors(): void
    {
        $this->errors = [];
    }

    /**
     * Get error summary
     */
    public function getSummary(): array
    {
        $summary = [
            'total'    => count($this->errors),
            'errors'   => 0,
            'warnings' => 0,
            'by_type'  => [],
        ];

        foreach ($this->errors as $error) {
            if ($error['severity'] === 'error') {
                $summary['errors']++;
            } else {
                $summary['warnings']++;
            }

            $type = $error['type'];
            $summary['by_type'][$type] = ($summary['by_type'][$type] ?? 0) + 1;
        }

        return $summary;
    }
}
