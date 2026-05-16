<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddWorkModeAndDayType extends Migration
{
    public function up()
    {
        // 1. Add new columns to attendance_daily
        $this->forge->addColumn('attendance_daily', [
            'attendance_status' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                'after'      => 'status'
            ],
            'work_mode' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'null'       => true,
                'after'      => 'attendance_status'
            ],
            'day_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'working_day',
                'after'      => 'work_mode'
            ],
        ]);

        // 2. Sync old status data to new attendance_status column for backward compatibility
        $db = \Config\Database::connect();
        $db->query("UPDATE attendance_daily SET attendance_status = status");

        // 3. Create attendance_overrides table
        $this->forge->addField([
            'id'              => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'emp_code'        => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'attendance_date' => [
                'type' => 'DATE',
            ],
            'override_type'   => [
                'type'       => 'VARCHAR',
                'constraint' => 10, // 'wfh' or 'wfo'
            ],
            'approved_by'     => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'remarks'         => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at'      => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at'      => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['emp_code', 'attendance_date']);
        $this->forge->createTable('attendance_overrides', true);
    }

    public function down()
    {
        $this->forge->dropColumn('attendance_daily', 'attendance_status');
        $this->forge->dropColumn('attendance_daily', 'work_mode');
        $this->forge->dropColumn('attendance_daily', 'day_type');
        $this->forge->dropTable('attendance_overrides', true);
    }
}
