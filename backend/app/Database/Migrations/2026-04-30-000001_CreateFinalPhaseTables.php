<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateFinalPhaseTables extends Migration
{
    public function up()
    {
        // 1. Leave Requests Table
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'emp_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
            ],
            'leave_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
            ],
            'from_date' => [
                'type' => 'DATE',
                'null' => false,
            ],
            'to_date' => [
                'type' => 'DATE',
                'null' => false,
            ],
            'from_session' => [
                'type'       => 'ENUM',
                'constraint' => ['full', 'half_morning', 'half_afternoon'],
                'default'    => 'full',
                'null'       => false,
            ],
            'to_session' => [
                'type'       => 'ENUM',
                'constraint' => ['full', 'half_morning', 'half_afternoon'],
                'default'    => 'full',
                'null'       => false,
            ],
            'reason' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'approved', 'rejected'],
                'default'    => 'pending',
                'null'       => false,
            ],
            'admin_comment' => [
                'type' => 'TEXT',
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
        $this->forge->addKey('emp_code');
        $this->forge->addKey('status');
        $this->forge->createTable('leave_requests', true);

        // 2. Leave Balance Table
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'emp_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
            ],
            'leave_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
            ],
            'total' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,1',
                'default'    => 0,
                'null'       => false,
            ],
            'used' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,1',
                'default'    => 0,
                'null'       => false,
            ],
            'remaining' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,1',
                'default'    => 0,
                'null'       => false,
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
        $this->forge->addUniqueKey(['emp_code', 'leave_type']);
        $this->forge->createTable('leave_balance', true);

        // 3. Holidays Table
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'date' => [
                'type' => 'DATE',
                'null' => false,
            ],
            'type' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'default'    => 'public', // public, optional, etc.
                'null'       => false,
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
        $this->forge->addUniqueKey('date');
        $this->forge->createTable('holidays', true);

        // 4. Notifications Table
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'emp_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true, // null means global/admin
            ],
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'message' => [
                'type' => 'TEXT',
                'null' => false,
            ],
            'type' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
            ],
            'is_read' => [
                'type'       => 'BOOLEAN',
                'default'    => false,
                'null'       => false,
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
        $this->forge->addKey('emp_code');
        $this->forge->addKey('is_read');
        $this->forge->createTable('notifications', true);
    }

    public function down()
    {
        $this->forge->dropTable('notifications', true);
        $this->forge->dropTable('holidays', true);
        $this->forge->dropTable('leave_balance', true);
        $this->forge->dropTable('leave_requests', true);
    }
}
