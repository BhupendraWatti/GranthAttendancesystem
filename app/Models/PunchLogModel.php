<?php

namespace App\Models;

use CodeIgniter\Model;

class PunchLogModel extends Model
{
    protected $table            = 'punch_logs';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $useTimestamps    = false;
    protected $createdField     = 'created_at';

    protected $allowedFields = [
        'emp_code',
        'punch_time',
        'source',
        'raw_data',
        'created_at',
    ];

    /**
     * Insert punch log, ignoring duplicates (idempotent)
     * Uses INSERT IGNORE to skip duplicate (emp_code, punch_time)
     */
    public function insertIgnore(array $data): bool
    {
        $db = \Config\Database::connect();

        $data['created_at'] = $data['created_at'] ?? date('Y-m-d H:i:s');

        if (is_array($data['raw_data'] ?? null)) {
            $data['raw_data'] = json_encode($data['raw_data']);
        }

        // Use ON DUPLICATE KEY UPDATE instead of INSERT IGNORE
        // INSERT IGNORE suppresses ALL errors; this only handles duplicate (emp_code, punch_time)
        $sql = "INSERT INTO {$this->table} (emp_code, punch_time, source, raw_data, created_at) 
                VALUES (?, ?, ?, ?, ?) 
                ON DUPLICATE KEY UPDATE created_at = created_at";

        return $db->query($sql, [
            $data['emp_code'],
            $data['punch_time'],
            $data['source'] ?? 'api',
            $data['raw_data'] ?? null,
            $data['created_at'],
        ]);
    }

    /**
     * Batch insert with duplicate handling — uses multi-row INSERT for performance
     * Processes records in chunks of 100 for optimal MySQL performance
     */
    public function batchInsertIgnore(array $records): int
    {
        if (empty($records)) {
            return 0;
        }

        $db = \Config\Database::connect();
        $saved = 0;
        $chunks = array_chunk($records, 100);

        foreach ($chunks as $chunk) {
            $placeholders = [];
            $bindings = [];

            foreach ($chunk as $record) {
                $createdAt = $record['created_at'] ?? date('Y-m-d H:i:s');
                $rawData = is_array($record['raw_data'] ?? null) ? json_encode($record['raw_data']) : ($record['raw_data'] ?? null);

                $placeholders[] = '(?, ?, ?, ?, ?)';
                $bindings[] = $record['emp_code'];
                $bindings[] = $record['punch_time'];
                $bindings[] = $record['source'] ?? 'api';
                $bindings[] = $rawData;
                $bindings[] = $createdAt;
            }

            $sql = "INSERT INTO {$this->table} (emp_code, punch_time, source, raw_data, created_at)
                    VALUES " . implode(', ', $placeholders) . "
                    ON DUPLICATE KEY UPDATE created_at = created_at";

            try {
                $db->query($sql, $bindings);
                $saved += count($chunk);
            } catch (\Throwable $e) {
                // Fallback to individual inserts if batch fails
                log_message('warning', '[PunchLogModel] Batch insert failed, falling back: ' . $e->getMessage());
                foreach ($chunk as $record) {
                    if ($this->insertIgnore($record)) {
                        $saved++;
                    }
                }
            }
        }

        return $saved;
    }

    /**
     * Get punches for a specific employee on a specific date
     */
    public function getPunchesForDate(string $empCode, string $date): array
    {
        return $this->where('emp_code', $empCode)
                    ->where('DATE(punch_time)', $date)
                    ->orderBy('punch_time', 'ASC')
                    ->findAll();
    }

    /**
     * Get all punches for a specific date grouped by employee
     */
    public function getAllPunchesForDate(string $date): array
    {
        return $this->where('DATE(punch_time)', $date)
                    ->orderBy('emp_code', 'ASC')
                    ->orderBy('punch_time', 'ASC')
                    ->findAll();
    }

    /**
     * Get latest N punches (for live feed)
     */
    public function getLatestPunches(int $limit = 20): array
    {
        return $this->select('punch_logs.*, employees.name')
                    ->join('employees', 'employees.emp_code = punch_logs.emp_code', 'left')
                    ->orderBy('punch_logs.punch_time', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }

    /**
     * Latest punches on a specific calendar day (Y-m-d), server/DB session timezone for DATE().
     */
    public function getLatestPunchesForDate(string $date, int $limit = 20): array
    {
        return $this->select('punch_logs.*, employees.name')
                    ->join('employees', 'employees.emp_code = punch_logs.emp_code', 'left')
                    ->where('DATE(punch_logs.punch_time)', $date)
                    ->orderBy('punch_logs.punch_time', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }

    /**
     * Get punches from the last N minutes
     */
    public function getRecentPunches(int $minutes = 30): array
    {
        $since = date('Y-m-d H:i:s', strtotime("-{$minutes} minutes"));

        return $this->select('punch_logs.*, employees.name')
                    ->join('employees', 'employees.emp_code = punch_logs.emp_code', 'left')
                    ->where('punch_logs.punch_time >=', $since)
                    ->orderBy('punch_logs.punch_time', 'DESC')
                    ->findAll();
    }

    /**
     * SQL helper to remove duplicates while preserving smallest id.
     */
    public function getDuplicateCleanupQuery(): string
    {
        return "DELETE p1 FROM punch_logs p1
INNER JOIN punch_logs p2
  ON p1.emp_code = p2.emp_code
 AND p1.punch_time = p2.punch_time
 AND p1.id > p2.id";
    }
}
