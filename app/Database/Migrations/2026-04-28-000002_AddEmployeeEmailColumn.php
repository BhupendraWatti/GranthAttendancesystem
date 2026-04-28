<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddEmployeeEmailColumn extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('employees')) {
            return;
        }

        if (!$this->db->fieldExists('email', 'employees')) {
            $this->forge->addColumn('employees', [
                'email' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                    'after'      => 'emp_code',
                ],
            ]);
        }

        // Add a unique index for email (nullable is fine; multiple NULL allowed)
        $indexes = $this->db->getIndexData('employees');
        if (!isset($indexes['employees_email_unique'])) {
            $this->forge->addKey('email', false, true, 'employees_email_unique');
            $this->forge->processIndexes('employees');
        }
    }

    public function down()
    {
        if (!$this->db->tableExists('employees')) {
            return;
        }

        if ($this->db->fieldExists('email', 'employees')) {
            // Drop index if it exists
            $indexes = $this->db->getIndexData('employees');
            if (isset($indexes['employees_email_unique'])) {
                $this->forge->dropKey('employees', 'employees_email_unique');
            }
            $this->forge->dropColumn('employees', 'email');
        }
    }
}

