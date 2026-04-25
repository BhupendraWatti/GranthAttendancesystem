<?php

namespace App\Services;

use App\Models\PunchLogModel;
use App\Models\EmployeeModel;
use App\Models\SyncLogModel;

/**
 * SyncService — Data Synchronization Orchestrator
 * 
 * Orchestrates the full sync pipeline:
 * 1. Fetch data from eTimeOffice API
 * 2. Normalize the response
 * 3. Validate the data
 * 4. Store raw punch logs (idempotent via INSERT IGNORE)
 * 5. Auto-discover and upsert employees
 * 6. Process attendance
 * 
 * IDEMPOTENCY:
 * - Uses INSERT IGNORE for punch_logs (UNIQUE on emp_code + punch_time)
 * - Uses UPSERT for attendance_daily (UNIQUE on emp_code + date)
 * - Tracks sync state via sync_logs
 * - Prevents concurrent runs via isRunning() check
 * 
 * DEPENDENCY INJECTION:
 * - Services are injected via constructor (or retrieved from container)
 * - Allows easy mocking for testing
 * - Promotes loose coupling
 */
class SyncService
{
    private const SOURCE_INCREMENTAL = 'DownloadLastPunchData';
    private const SOURCE_RANGE = 'DownloadPunchData';

    private ApiService $apiService;
    private NormalizationService $normalizationService;
    private ValidationService $validationService;
    private AttendanceService $attendanceService;
    private PunchLogModel $punchLogModel;
    private EmployeeModel $employeeModel;
    private SyncLogModel $syncLogModel;

    /**
     * Constructor with dependency injection
     * 
     * @param ApiService|null $apiService If null, retrieves from CI container
     * @param NormalizationService|null $normalizationService If null, retrieves from CI container
     * @param ValidationService|null $validationService If null, retrieves from CI container
     * @param AttendanceService|null $attendanceService If null, retrieves from CI container
     */
    public function __construct(
        ?ApiService $apiService = null,
        ?NormalizationService $normalizationService = null,
        ?ValidationService $validationService = null,
        ?AttendanceService $attendanceService = null
    ) {
        $this->apiService            = $apiService ?? \Config\Services::apiservice();
        $this->normalizationService  = $normalizationService ?? new NormalizationService();
        $this->validationService    = $validationService ?? new ValidationService();
        $this->attendanceService    = $attendanceService ?? \Config\Services::attendanceservice();
        $this->punchLogModel       = new PunchLogModel();
        $this->employeeModel       = new EmployeeModel();
        $this->syncLogModel       = new SyncLogModel();
    }

    /** eTimeOffice may return 500/timeout on one endpoint but succeed on another (see API1.xls). */
    private function isVendorTransientFailure(?string $error): bool
    {
        if ($error === null || $error === '') {
            return false;
        }

        return strpos($error, '500') !== false
            || stripos($error, 'timeout') !== false
            || stripos($error, 'timed out') !== false
            || stripos($error, 'Empty reply') !== false;
    }

    /**
     * DownloadPunchData → DownloadInOutPunchData → DownloadPunchDataMCID (per vendor API set).
     *
     * @return array{success:bool, data:mixed, source:?string, error:?string}
     */
    private function fetchPunchRangeWithFallbacks(string $fromDate, string $toDate, string $context = ''): array
    {
        $chain = [
            ['call' => 'downloadPunchData', 'source' => 'DownloadPunchData'],
            ['call' => 'downloadInOutPunchData', 'source' => 'DownloadInOutPunchData'],
            ['call' => 'downloadPunchDataMCID', 'source' => 'DownloadPunchDataMCID'],
        ];

        $lastError = null;
        foreach ($chain as $step) {
            $method      = $step['call'];
            $apiResponse = $this->apiService->$method($fromDate, $toDate);
            $ok          = (bool) ($apiResponse['success'] ?? false);

            if ($ok) {
                return [
                    'success' => true,
                    'data'    => $apiResponse['data'] ?? [],
                    'source'  => $step['source'],
                    'error'   => null,
                ];
            }

            $lastError = (string) ($apiResponse['error'] ?? 'Unknown error');
            log_message('warning', "[SyncService] {$step['source']} failed ({$context}): {$lastError}");

            if (!$this->isVendorTransientFailure($lastError)) {
                break;
            }
        }

        return [
            'success' => false,
            'data'    => null,
            'source'  => null,
            'error'   => $lastError,
        ];
    }

    /**
     * Run incremental sync — fetch only new punches since last sync
     * Designed to run every 5 minutes via cron
     *
     * @return array Sync result summary
     */
    public function runIncremental(): array
    {
        // Prevent concurrent execution
        if ($this->syncLogModel->isRunning()) {
            log_message('warning', '[SyncService] Sync already running, skipping');
            return ['status' => 'skipped', 'reason' => 'Already running'];
        }

        $syncId = $this->syncLogModel->startSync('incremental');

        try {
            // Cursor from last successful incremental (DownloadLastPunchData only). When absent, do not
            // call DownloadLastPunchData with a synthetic MMYYYY$NNNNNNNN — runtime logs show eTimeOffice
            // returns HTTP 500 for that seed; use today's DownloadPunchData first instead.
            $cursorId     = $this->syncLogModel->getLastRecordId();
            $lastRecordId = null;
            $today        = date('Y-m-d');

            if (empty($cursorId)) {
                log_message('info', '[SyncService] No LastRecord cursor; skipping DownloadLastPunchData, trying range endpoints for today');
                $fetch               = $this->fetchPunchRangeWithFallbacks($today, $today, 'incremental_no_cursor');
                $incrementalSource   = $fetch['source'] ?? self::SOURCE_RANGE;
                $apiResponse         = ['success' => $fetch['success'], 'data' => $fetch['data'] ?? []];

                if (!($apiResponse['success'] ?? false)) {
                    $finalError = $fetch['error'] ?? 'Unknown error';
                    log_message('info', "[SyncService] Incremental bootstrap (no cursor) failed. Completing with 0 punches. Error: {$finalError}");
                    $this->syncLogModel->completeSync($syncId, [
                        'records_fetched' => 0,
                        'last_record_id'  => null,
                    ]);
                    return [
                        'status'          => 'success',
                        'records_fetched' => 0,
                        'records_saved'   => 0,
                        'message'         => 'Sync completed automatically (No data available)',
                    ];
                }
            } else {
                $lastRecordId = $cursorId;
                log_message('info', "[SyncService] Incremental sync with last_record_id={$lastRecordId}");
                $apiResponse       = $this->apiService->downloadLastPunchData($lastRecordId);
                $incrementalSource = self::SOURCE_INCREMENTAL;

                if (!($apiResponse['success'] ?? false)) {
                    $errorMsg = $apiResponse['error'] ?? 'Unknown error';
                    if ($this->isVendorTransientFailure($errorMsg)) {
                        log_message('warning', "[SyncService] Incremental primary endpoint failed ({$errorMsg}); trying range fallbacks for today");
                        $fetch               = $this->fetchPunchRangeWithFallbacks($today, $today, 'incremental_after_lastpunch_fail');
                        $incrementalSource   = $fetch['source'] ?? self::SOURCE_RANGE;
                        $apiResponse         = ['success' => $fetch['success'], 'data' => $fetch['data'] ?? []];
                    }

                    if (!($apiResponse['success'] ?? false)) {
                        $finalError = $apiResponse['error'] ?? (($fetch ?? [])['error'] ?? 'Unknown error');
                        log_message('info', "[SyncService] All incremental endpoints returned no data/500. Completing with 0 punches. Error: {$finalError}");
                        $this->syncLogModel->completeSync($syncId, [
                            'records_fetched' => 0,
                            // Do not persist a LastPunch cursor when no data was ingested — stale IDs force a 500
                            // DownloadLastPunchData on every subsequent run (see writable/logs).
                            'last_record_id' => null,
                        ]);
                        return [
                            'status'          => 'success',
                            'records_fetched' => 0,
                            'records_saved'   => 0,
                            'message'         => 'Sync completed automatically (No data available)',
                        ];
                    }
                }
            }

            if ($incrementalSource === self::SOURCE_INCREMENTAL) {
                $normalized = $this->normalizationService->normalizeLastPunchData($apiResponse['data'] ?? []);
                $records = $normalized['records'] ?? [];
                $newLastRecordId = $normalized['last_record_id'] ?? $lastRecordId;
            } else {
                $records = $this->normalizationService->normalizePunchData($apiResponse['data'] ?? [], $incrementalSource);
                // Range/InOut do not supply a DownloadLastPunchData cursor; never re-save a bogus last_record_id
                // from a prior failed LastPunch attempt (would block the no-cursor bootstrap path).
                $newLastRecordId = null;
            }

            // Validate
            $records = $this->validationService->validatePunchRecords($records);

            // Store raw punch logs
            $saved = $this->storePunchLogs($records);

            // Auto-discover employees from punch data
            $this->discoverEmployees($records);

            // Determine affected dates and process attendance
            $affectedDates = $this->getAffectedDates($records);
            foreach ($affectedDates as $date) {
                $this->attendanceService->processDay($date);
            }

            // Complete sync log
            $this->syncLogModel->completeSync($syncId, [
                'records_fetched' => count($records),
                'records_saved'   => $saved,
                'last_record_id'  => $newLastRecordId,
            ]);

            $result = [
                'status'          => 'success',
                'sync_type'       => 'incremental',
                'records_fetched' => count($records),
                'records_saved'   => $saved,
                'dates_processed' => $affectedDates,
                'validation'      => $this->validationService->getSummary(),
            ];

            log_message('info', '[SyncService] Incremental sync completed: ' . json_encode($result));
            return $result;

        } catch (\Throwable $e) {
            $this->syncLogModel->failSync($syncId, $e->getMessage());
            log_message('error', '[SyncService] Incremental sync failed: ' . $e->getMessage());

            return [
                'status' => 'failed',
                'error'  => $e->getMessage(),
            ];
        }
    }

    /**
     * Run full sync — fetch all punches for today (or specified date)
     * Designed to run daily at midnight via cron
     *
     * @param string|null $date Date to sync (default: today)
     * @return array Sync result summary
     */
    public function runFull(?string $date = null): array
    {
        if ($this->syncLogModel->isRunning()) {
            log_message('warning', '[SyncService] Sync already running, skipping');
            return ['status' => 'skipped', 'reason' => 'Already running'];
        }

        $syncId = $this->syncLogModel->startSync('full');
        $date = $date ?? date('Y-m-d');

        try {
            // Fetch full day data
            log_message('info', "[SyncService] Full sync for date: {$date}");
            $fetch      = $this->fetchPunchRangeWithFallbacks($date, $date, 'full_single_day');
            $fullSource = $fetch['source'] ?? self::SOURCE_RANGE;
            $apiResponse = ['success' => $fetch['success'], 'data' => $fetch['data'] ?? []];

            if (!($apiResponse['success'] ?? false)) {
                $finalError = $fetch['error'] ?? 'Unknown error';
                if ($this->isVendorTransientFailure($finalError)) {
                    log_message('info', "[SyncService] Full sync range endpoints returned no data/500. Treating as 0 punches. Error: {$finalError}");
                    $this->syncLogModel->completeSync($syncId, [
                        'records_fetched' => 0,
                    ]);
                    return [
                        'status' => 'success',
                        'records_fetched' => 0,
                        'records_saved' => 0,
                        'message' => 'Full sync completed automatically (No data available)'
                    ];
                }
                throw new \RuntimeException('API call failed: ' . $finalError);
            }

            // Normalize
            $records = $this->normalizationService->normalizePunchData($apiResponse['data'] ?? [], $fullSource);

            // Validate
            $records = $this->validationService->validatePunchRecords($records);

            // Store raw punch logs (INSERT IGNORE handles duplicates)
            $saved = $this->storePunchLogs($records);

            // Auto-discover employees
            $this->discoverEmployees($records);

            // Reprocess attendance for this date
            $this->attendanceService->processDay($date);

            // Complete sync log
            $this->syncLogModel->completeSync($syncId, [
                'records_fetched' => count($records),
                'records_saved'   => $saved,
            ]);

            $result = [
                'status'          => 'success',
                'sync_type'       => 'full',
                'date'            => $date,
                'records_fetched' => count($records),
                'records_saved'   => $saved,
                'validation'      => $this->validationService->getSummary(),
            ];

            log_message('info', '[SyncService] Full sync completed: ' . json_encode($result));
            return $result;

        } catch (\Throwable $e) {
            $this->syncLogModel->failSync($syncId, $e->getMessage());
            log_message('error', '[SyncService] Full sync failed: ' . $e->getMessage());

            return [
                'status' => 'failed',
                'error'  => $e->getMessage(),
            ];
        }
    }

    /**
     * Run full sync for a date range
     */
    public function runFullRange(string $fromDate, string $toDate): array
    {
        if ($this->syncLogModel->isRunning()) {
            return ['status' => 'skipped', 'reason' => 'Already running'];
        }

        $syncId = $this->syncLogModel->startSync('full');

        try {
            log_message('info', "[SyncService] Full range sync from {$fromDate} to {$toDate}");
            $fetch = $this->fetchPunchRangeWithFallbacks($fromDate, $toDate, 'full_range');

            if (!($fetch['success'] ?? false)) {
                $errorMsg = $fetch['error'] ?? 'Unknown error';
                if ($this->isVendorTransientFailure($errorMsg)) {
                    log_message('info', "[SyncService] Full Range Sync: all range endpoints failed. Treating as 0 punches. Error: {$errorMsg}");
                    $this->syncLogModel->completeSync($syncId, [
                        'records_fetched' => 0,
                        'records_saved'   => 0,
                    ]);
                    return [
                        'status'          => 'success',
                        'records_fetched' => 0,
                        'records_saved'   => 0,
                        'message'         => 'Full range sync completed (No data available from API)',
                    ];
                }
                throw new \RuntimeException('API call failed: ' . $errorMsg);
            }

            $rangeSource = $fetch['source'] ?? self::SOURCE_RANGE;
            $records     = $this->normalizationService->normalizePunchData($fetch['data'] ?? [], $rangeSource);
            $records = $this->validationService->validatePunchRecords($records);
            $saved = $this->storePunchLogs($records);
            $this->discoverEmployees($records);

            $affectedDates = $this->getAffectedDates($records);
            foreach ($affectedDates as $date) {
                $this->attendanceService->processDay($date);
            }

            $this->syncLogModel->completeSync($syncId, [
                'records_fetched' => count($records),
                'records_saved'   => $saved,
            ]);

            $result = [
                'status'          => 'success',
                'sync_type'       => 'full_range',
                'records_fetched' => count($records),
                'records_saved'   => $saved,
                'dates_processed' => $affectedDates,
                'validation'      => $this->validationService->getSummary(),
            ];

            log_message('info', '[SyncService] Full range sync completed: ' . json_encode($result));
            return $result;

        } catch (\Throwable $e) {
            $this->syncLogModel->failSync($syncId, $e->getMessage());
            log_message('error', '[SyncService] Full range sync failed: ' . $e->getMessage());
            return ['status' => 'failed', 'error' => $e->getMessage()];
        }
    }

    /**
     * Store normalized punch records into punch_logs
     */
    private function storePunchLogs(array $records): int
    {
        $data = array_map(function ($record) {
            return [
                'emp_code'   => $record['emp_code'],
                'punch_time' => $record['punch_time'],
                'source'     => $record['source'] ?? 'api',
                'raw_data'   => $record['raw_data'] ?? null,
            ];
        }, $records);

        return $this->punchLogModel->batchInsertIgnore($data);
    }

    /**
     * Auto-discover and upsert employees from punch data
     */
    private function discoverEmployees(array $records): void
    {
        $seen = [];
        foreach ($records as $record) {
            $empCode = $record['emp_code'];
            if (isset($seen[$empCode]) || empty($empCode)) {
                continue;
            }
            $seen[$empCode] = true;

            $this->employeeModel->upsertByCode([
                'emp_code' => $empCode,
                'name'     => $record['name'] ?? $empCode,
            ]);
        }
    }

    /**
     * Extract unique dates from punch records
     */
    private function getAffectedDates(array $records): array
    {
        $dates = [];
        foreach ($records as $record) {
            if (!empty($record['punch_time'])) {
                $date = substr($record['punch_time'], 0, 10); // Y-m-d from Y-m-d H:i:s
                $dates[$date] = true;
            }
        }
        return array_keys($dates);
    }
}
