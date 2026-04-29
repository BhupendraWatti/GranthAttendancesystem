<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAttendanceDailyTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'emp_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
            ],
            'date' => [
                'type' => 'DATE',
                'null' => false,
            ],
            'first_in' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'last_out' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'work_minutes' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'null'       => false,
            ],
            'late_minutes' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'null'       => false,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['present', 'half_day', 'absent'],
                'null'       => false,
            ],
            'punch_count' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'null'       => false,
            ],
            'employee_type' => [
                'type'       => 'ENUM',
                'constraint' => ['full_time', 'intern'],
                'default'    => 'full_time',
                'null'       => false,
            ],
            'required_minutes' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 510,
                'null'       => false,
            ],
            'validation_errors' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['emp_code', 'date'], 'unique_emp_date');
        $this->forge->addKey('date');
        $this->forge->addKey('status');
        $this->forge->createTable('attendance_daily', true);
    }

    public function down()
    {
        $this->forge->dropTable('attendance_daily', true);
    }
}
