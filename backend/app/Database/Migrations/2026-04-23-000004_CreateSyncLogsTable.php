<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSyncLogsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'sync_type' => [
                'type'       => 'ENUM',
                'constraint' => ['incremental', 'full'],
                'null'       => false,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['running', 'success', 'failed'],
                'null'       => false,
            ],
            'records_fetched' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'records_saved' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'last_record_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'error_message' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'started_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'completed_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('sync_type');
        $this->forge->addKey('status');
        $this->forge->createTable('sync_logs', true);
    }

    public function down()
    {
        $this->forge->dropTable('sync_logs', true);
    }
}
