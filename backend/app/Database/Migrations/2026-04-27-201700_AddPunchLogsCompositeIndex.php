<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPunchLogsCompositeIndex extends Migration
{
    public function up()
    {
        // Safety index for frequent date/emp based queries.
        $existing = $this->db->query("SHOW INDEX FROM punch_logs WHERE Key_name = 'idx_punch_emp_time'")->getResultArray();
        if (empty($existing)) {
            $this->db->query('CREATE INDEX idx_punch_emp_time ON punch_logs (emp_code, punch_time)');
        }
    }

    public function down()
    {
        $existing = $this->db->query("SHOW INDEX FROM punch_logs WHERE Key_name = 'idx_punch_emp_time'")->getResultArray();
        if (!empty($existing)) {
            $this->db->query('DROP INDEX idx_punch_emp_time ON punch_logs');
        }
    }
}
