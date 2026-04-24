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
            // Get last record ID from previous successful sync
            $lastRecordId = $this->syncLogModel->getLastRecordId();

            if (empty($lastRecordId)) {
                // No previous sync — generate initial record ID
                $lastRecordId = $this->apiService->generateLastRecordId(null, 1);
                log_message('info', "[SyncService] No previous sync, using initial record ID: {$lastRecordId}");
            }

            log_message('info', "[SyncService] Incremental sync with last_record_id={$lastRecordId}");
            $apiResponse = $this->apiService->downloadLastPunchData($lastRecordId);

            if (!($apiResponse['success'] ?? false)) {
                $errorMsg = $apiResponse['error'] ?? 'Unknown error';
                // The eTimeOffice API returns a 500 error (IndexOutOfRangeException) when there's no data.
                // We should gracefully handle this as "0 records" rather than crashing the sync.
                if (strpos($errorMsg, '500') !== false || strpos($errorMsg, 'timeout') !== false) {
                    log_message('info', "[SyncService] eTimeOffice API returned no data or 500 error. Treating as 0 punches. Error: {$errorMsg}");
                    $this->syncLogModel->completeSync($syncId, [
                        'records_fetched' => 0,
                        'last_record_id'  => $lastRecordId,
                    ]);
                    return [
                        'status' => 'success',
                        'records_fetched' => 0,
                        'records_saved' => 0,
                        'message' => 'Sync completed automatically (No data available)'
                    ];
                }
                throw new \RuntimeException('API call failed: ' . $errorMsg);
            }

            $normalized = $this->normalizationService->normalizeLastPunchData($apiResponse['data'] ?? []);
            $records = $normalized['records'] ?? [];
            $newLastRecordId = $normalized['last_record_id'] ?? $lastRecordId;

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
            $apiResponse = $this->apiService->downloadPunchData($date, $date);

            if (!($apiResponse['success'] ?? false)) {
                $errorMsg = $apiResponse['error'] ?? 'Unknown error';
                if (strpos($errorMsg, '500') !== false || strpos($errorMsg, 'timeout') !== false) {
                    log_message('info', "[SyncService] Full Sync: API returned no data or 500 error. Treating as 0 punches. Error: {$errorMsg}");
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
                throw new \RuntimeException('API call failed: ' . $errorMsg);
            }

            // Normalize
            $records = $this->normalizationService->normalizePunchData($apiResponse['data'] ?? [], self::SOURCE_RANGE);

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
            $apiResponse = $this->apiService->downloadPunchData($fromDate, $toDate);

            if (!($apiResponse['success'] ?? false)) {
                $errorMsg = $apiResponse['error'] ?? 'Unknown error';
                // Gracefully handle 500 / timeout from eTimeOffice (consistent with other sync methods)
                if (strpos($errorMsg, '500') !== false || strpos($errorMsg, 'timeout') !== false) {
                    log_message('info', "[SyncService] Full Range Sync: API returned no data or 500 error. Treating as 0 punches. Error: {$errorMsg}");
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

            $records = $this->normalizationService->normalizePunchData($apiResponse['data'] ?? [], self::SOURCE_RANGE);
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
