<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCompOffToAttendance extends Migration
{
    public function up()
    {
        // Add 'comp_off' to the status enum in attendance_daily table
        // Since SQLite doesn't support MODIFY COLUMN for ENUMs easily, 
        // and we are likely using MySQL based on the upsertAttendance method,
        // we'll use a raw SQL for MySQL.
        
        $db = \Config\Database::connect();
        if ($db->getPlatform() === 'MySQLi') {
            $db->query("ALTER TABLE attendance_daily MODIFY COLUMN status ENUM('present', 'half_day', 'absent', 'leave', 'holiday', 'comp_off') NOT NULL DEFAULT 'absent'");
        }
    }

    public function down()
    {
        $db = \Config\Database::connect();
        if ($db->getPlatform() === 'MySQLi') {
            $db->query("ALTER TABLE attendance_daily MODIFY COLUMN status ENUM('present', 'half_day', 'absent', 'leave', 'holiday') NOT NULL DEFAULT 'absent'");
        }
    }
}
