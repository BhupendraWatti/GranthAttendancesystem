<?php

namespace App\Models;

use CodeIgniter\Model;

class SyncLogModel extends Model
{
    protected $table            = 'sync_logs';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $useTimestamps    = false;

    protected $allowedFields = [
        'sync_type',
        'status',
        'records_fetched',
        'records_saved',
        'last_record_id',
        'error_message',
        'started_at',
        'completed_at',
    ];

    /**
     * Get the last successful sync record
     */
    public function getLastSuccessful(string $syncType = 'incremental'): ?array
    {
        return $this->where('sync_type', $syncType)
                    ->where('status', 'success')
                    ->orderBy('completed_at', 'DESC')
                    ->first();
    }

    /**
     * Get the last record ID from the most recent successful incremental sync
     */
    public function getLastRecordId(): ?string
    {
        $record = $this->getLastSuccessful('incremental');
        return $record['last_record_id'] ?? null;
    }

    /**
     * Start a new sync log entry (returns the ID)
     */
    public function startSync(string $syncType): int
    {
        $this->insert([
            'sync_type'  => $syncType,
            'status'     => 'running',
            'started_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->getInsertID();
    }

    /**
     * Complete a sync log entry
     */
    public function completeSync(int $id, array $data): bool
    {
        return $this->update($id, array_merge($data, [
            'status'       => 'success',
            'completed_at' => date('Y-m-d H:i:s'),
        ]));
    }

    /**
     * Mark a sync as failed
     */
    public function failSync(int $id, string $error): bool
    {
        return $this->update($id, [
            'status'        => 'failed',
            'error_message' => $error,
            'completed_at'  => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Get recent sync history
     */
    public function getRecent(int $limit = 10): array
    {
        return $this->orderBy('started_at', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }

    /**
     * Check if a sync is currently running
     */
    public function isRunning(): bool
    {
        // A sync is considered "running" only if it started within the last 15 minutes
        // This prevents crashed syncs from permanently blocking all future runs
        $staleThreshold = date('Y-m-d H:i:s', strtotime('-15 minutes'));

        return $this->where('status', 'running')
                    ->where('started_at >=', $staleThreshold)
                    ->countAllResults() > 0;
    }

    /**
     * Check if there has been at least one successful sync today (any type).
     * Used by DashboardController to decide whether to auto-trigger.
     *
     * @return bool true if a successful sync exists for today
     */
    public function hasSuccessfulSyncToday(): bool
    {
        $todayStart = date('Y-m-d') . ' 00:00:00';

        return $this->where('status', 'success')
                    ->where('completed_at >=', $todayStart)
                    ->countAllResults() > 0;
    }
}
