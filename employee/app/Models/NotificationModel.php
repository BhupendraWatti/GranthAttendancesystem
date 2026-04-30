<?php

namespace App\Models;

use CodeIgniter\Model;

class NotificationModel extends Model
{
    protected $table            = 'notifications';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';

    protected $allowedFields = [
        'emp_code',
        'title',
        'message',
        'type',
        'is_read',
    ];

    /**
     * Get unread notifications for an employee
     */
    public function getUnread(string $empCode): array
    {
        return $this->where('emp_code', $empCode)->where('is_read', false)->orderBy('created_at', 'DESC')->findAll();
    }

    /**
     * Get all notifications for an employee
     */
    public function getByEmployee(string $empCode, int $limit = 20): array
    {
        return $this->where('emp_code', $empCode)->orderBy('created_at', 'DESC')->findAll($limit);
    }

    /**
     * Mark all as read
     */
    public function markAllAsRead(string $empCode): bool
    {
        return $this->where('emp_code', $empCode)->set(['is_read' => true])->update();
    }
}
