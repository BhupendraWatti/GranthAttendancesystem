<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run()
    {
        $username = env('ADMIN_USERNAME', 'admin');
        $password = env('ADMIN_PASSWORD', 'Admin2026Secure');

        // Check if admin already exists
        $existing = $this->db->table('admins')->where('username', $username)->get()->getRow();

        if ($existing) {
            echo "Admin user '{$username}' already exists. Skipping.\n";
            return;
        }

        $this->db->table('admins')->insert([
            'username'   => $username,
            'password'   => password_hash($password, PASSWORD_BCRYPT),
            'role'       => 'super_admin',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        echo "Admin user '{$username}' created successfully.\n";
    }
}
