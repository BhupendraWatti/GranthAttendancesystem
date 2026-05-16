<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCompOffCreditFlag extends Migration
{
    public function up()
    {
        $this->forge->addColumn('attendance_daily', [
            'is_compoff_credited' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'after'      => 'is_locked',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('attendance_daily', 'is_compoff_credited');
    }
}
