<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddManualEntryProtection extends Migration
{
    public function up()
    {
        $this->forge->addColumn('attendance_daily', [
            'is_manual_entry' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'after'      => 'validation_errors',
            ],
            'is_locked' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'after'      => 'is_manual_entry',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('attendance_daily', 'is_manual_entry');
        $this->forge->dropColumn('attendance_daily', 'is_locked');
    }
}
