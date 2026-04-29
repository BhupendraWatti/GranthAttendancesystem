<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddEmployeeAuthFields extends Migration
{
    public function up()
    {
        $fields = [];

        if (!$this->db->fieldExists('email', 'employees')) {
            $fields['email'] = [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'emp_code',
            ];
        }

        if (!$this->db->fieldExists('salary', 'employees')) {
            $fields['salary'] = [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
                'after'      => 'employee_type',
            ];
        }

        if (!$this->db->fieldExists('otp_hash', 'employees')) {
            $fields['otp_hash'] = [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'status',
            ];
        }

        if (!$this->db->fieldExists('otp_expires_at', 'employees')) {
            $fields['otp_expires_at'] = [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'otp_hash',
            ];
        }

        if (!$this->db->fieldExists('otp_attempts', 'employees')) {
            $fields['otp_attempts'] = [
                'type'       => 'TINYINT',
                'constraint' => 3,
                'default'    => 0,
                'after'      => 'otp_expires_at',
            ];
        }

        if (!$this->db->fieldExists('otp_last_sent_at', 'employees')) {
            $fields['otp_last_sent_at'] = [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'otp_attempts',
            ];
        }

        if ($fields) {
            $this->forge->addColumn('employees', $fields);
        }
    }

    public function down()
    {
        $drop = [];
        foreach (['email', 'salary', 'otp_hash', 'otp_expires_at', 'otp_attempts', 'otp_last_sent_at'] as $col) {
            if ($this->db->fieldExists($col, 'employees')) {
                $drop[] = $col;
            }
        }

        if ($drop) {
            $this->forge->dropColumn('employees', $drop);
        }
    }
}

