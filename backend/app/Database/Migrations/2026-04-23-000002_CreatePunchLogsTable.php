<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePunchLogsTable extends Migration
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
            'punch_time' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'source' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'default'    => 'api',
                'null'       => false,
            ],
            'raw_data' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('emp_code');
        $this->forge->addKey('punch_time');
        $this->forge->addUniqueKey(['emp_code', 'punch_time'], 'unique_emp_punch');
        $this->forge->createTable('punch_logs', true);
    }

    public function down()
    {
        $this->forge->dropTable('punch_logs', true);
    }
}
