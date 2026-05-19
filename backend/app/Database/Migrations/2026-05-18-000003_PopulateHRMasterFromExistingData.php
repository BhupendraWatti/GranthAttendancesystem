<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class PopulateHRMasterFromExistingData extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        // 1. Extract and Insert unique Departments
        $db->query("INSERT IGNORE INTO departments (name, status, created_at, updated_at) 
                    SELECT DISTINCT department, 'active', NOW(), NOW() 
                    FROM employees WHERE department IS NOT NULL AND department != ''");

        // 2. Extract and Insert unique Designations
        $db->query("INSERT IGNORE INTO designations (name, status, created_at, updated_at) 
                    SELECT DISTINCT designation, 'active', NOW(), NOW() 
                    FROM employees WHERE designation IS NOT NULL AND designation != ''");

        // 3. Link Departments back to Employees
        $db->query("UPDATE employees e 
                    JOIN departments d ON d.name = e.department 
                    SET e.department_id = d.id 
                    WHERE e.department_id IS NULL");

        // 4. Link Designations back to Employees
        $db->query("UPDATE employees e 
                    JOIN designations dg ON dg.name = e.designation 
                    SET e.designation_id = dg.id 
                    WHERE e.designation_id IS NULL");

        // 5. Try to link Designations to Departments based on existing employee pairings
        $db->query("UPDATE designations dg
                    JOIN employees e ON e.designation_id = dg.id
                    SET dg.department_id = e.department_id
                    WHERE dg.department_id IS NULL AND e.department_id IS NOT NULL");
    }

    public function down()
    {
        // No action needed for down as this is a data population script
    }
}
