<?php

namespace App\Models;

use CodeIgniter\Model;

class HolidayModel extends Model
{
    protected $table            = 'holidays';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';

    protected $allowedFields = [
        'title',
        'date',
        'type',
    ];

    /**
     * Get upcoming holidays
     */
    public function getUpcoming(): array
    {
        return $this->where('date >=', date('Y-m-d'))->orderBy('date', 'ASC')->findAll();
    }

    /**
     * Check if a date is a holiday
     */
    public function isHoliday(string $date): bool
    {
        return $this->where('date', $date)->countAllResults() > 0;
    }

    /**
     * Get holidays in a range
     */
    public function getInRange(string $startDate, string $endDate): array
    {
        return $this->where('date >=', $startDate)->where('date <=', $endDate)->findAll();
    }
}
