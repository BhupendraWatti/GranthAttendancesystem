<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAttendanceSummaryTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'emp_code' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => false,
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
            'total_hours' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'default' => 0.00,
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['present', 'half_day', 'absent'],
                'null' => false,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['emp_code', 'date'], 'unique_emp_date_summary');
        $this->forge->addKey('status');
        $this->forge->createTable('attendance_summary', true);
    }

    public function down()
    {
        $this->forge->dropTable('attendance_summary', true);
    }
}
