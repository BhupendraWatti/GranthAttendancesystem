<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterEmployeesTableForHRMaster extends Migration
{
    public function up()
    {
        $this->forge->addColumn('employees', [
            'date_of_joining' => [
                'type' => 'DATE',
                'null' => true,
                'after' => 'name',
            ],
            'department_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'department',
            ],
            'designation_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'designation',
            ],
            'shift_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'designation_id',
            ],
            'employment_status' => [
                'type'       => 'ENUM',
                'constraint' => ['active', 'resigned', 'suspended', 'on_leave'],
                'default'    => 'active',
                'null'       => false,
                'after'      => 'status',
            ],
            'is_profile_locked' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'null'       => false,
                'after'      => 'employment_status',
            ],
        ]);

        $this->forge->addKey('department_id');
        $this->forge->addKey('designation_id');
        $this->forge->addKey('shift_id');
        $this->forge->addKey('employment_status');
    }

    public function down()
    {
        $this->forge->dropColumn('employees', [
            'date_of_joining',
            'department_id',
            'designation_id',
            'shift_id',
            'employment_status',
            'is_profile_locked'
        ]);
    }
}
