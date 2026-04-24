<?php
/**
 * AttendPro — Database Setup & Connection Test
 * 
 * Usage: Navigate to http://localhost:8080/db_test.php
 * 
 * This script:
 * 1. Tests MySQL connectivity on ports 3306 and 3307
 * 2. Creates the `attendance_system` database if it doesn't exist
 * 3. Creates all required tables
 * 4. Reports status
 * 
 * DELETE THIS FILE AFTER SETUP IS COMPLETE
 */
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>AttendPro — DB Setup</title>
    <style>
        body { font-family: system-ui, sans-serif; max-width: 700px; margin: 40px auto; padding: 0 20px; background: #0f1117; color: #e0e0e0; }
        h1 { color: #818cf8; }
        .ok { color: #34d399; font-weight: bold; }
        .fail { color: #f87171; font-weight: bold; }
        .warn { color: #fbbf24; font-weight: bold; }
        .box { background: #1a1d29; border: 1px solid #2d3044; border-radius: 8px; padding: 16px; margin: 12px 0; }
        code { background: #2d3044; padding: 2px 6px; border-radius: 4px; }
        table { border-collapse: collapse; width: 100%; }
        td, th { padding: 8px 12px; border: 1px solid #2d3044; text-align: left; }
        th { background: #1e2133; }
        a { color: #818cf8; }
    </style>
</head>
<body>
<h1>🔧 AttendPro — DB Setup & Test</h1>

<?php
$results = [];
$workingPort = null;
$workingHost = null;

// Test all combinations
$tests = [
    ['localhost', 3306],
    ['127.0.0.1', 3306],
    ['localhost', 3307],
    ['127.0.0.1', 3307],
];

echo '<div class="box"><h3>1. MySQL Connectivity Tests</h3><table>';
echo '<tr><th>Host</th><th>Port</th><th>Status</th><th>Details</th></tr>';

foreach ($tests as [$host, $port]) {
    $conn = @new mysqli($host, 'root', '', '', $port);
    if ($conn->connect_errno) {
        echo "<tr><td>$host</td><td>$port</td><td class='fail'>✗ FAILED</td><td>" . htmlspecialchars($conn->connect_error) . "</td></tr>";
    } else {
        echo "<tr><td>$host</td><td>$port</td><td class='ok'>✓ OK</td><td>Connected successfully</td></tr>";
        if ($workingPort === null) {
            $workingPort = $port;
            $workingHost = $host;
        }
        $conn->close();
    }
}
echo '</table></div>';

if ($workingPort === null) {
    echo '<div class="box"><h3 class="fail">❌ MySQL is NOT running</h3>';
    echo '<p>Please start MySQL from XAMPP Control Panel and reload this page.</p>';
    echo '<ol>';
    echo '<li>Open <strong>XAMPP Control Panel</strong></li>';
    echo '<li>Click <strong>"Start"</strong> next to <strong>MySQL</strong></li>';
    echo '<li>Wait for it to turn <span class="ok">green</span></li>';
    echo '<li>Reload this page</li>';
    echo '</ol></div>';
    echo '</body></html>';
    exit;
}

echo "<div class='box'><p class='ok'>✓ MySQL is running on <code>$workingHost:$workingPort</code></p></div>";

// Check if we need to update .env port
$envFile = dirname(__DIR__) . '/.env';
if (file_exists($envFile)) {
    $envContent = file_get_contents($envFile);
    $currentPort = null;
    if (preg_match('/database\.default\.port\s*=\s*(\d+)/', $envContent, $m)) {
        $currentPort = (int)$m[1];
    }
    
    if ($currentPort !== $workingPort) {
        echo "<div class='box'><h3 class='warn'>⚠ Port Mismatch</h3>";
        echo "<p>Your <code>.env</code> has port <code>$currentPort</code> but MySQL is on port <code>$workingPort</code></p>";
        
        // Auto-fix
        $envContent = preg_replace('/database\.default\.port\s*=\s*\d+/', "database.default.port = $workingPort", $envContent);
        if (file_put_contents($envFile, $envContent)) {
            echo "<p class='ok'>✓ Auto-fixed! Updated .env to port $workingPort</p>";
        } else {
            echo "<p class='fail'>Could not auto-fix. Manually update .env: <code>database.default.port = $workingPort</code></p>";
        }
        echo "</div>";
    } else {
        echo "<div class='box'><p class='ok'>✓ .env port matches MySQL port ($workingPort)</p></div>";
    }
    
    // Also fix hostname if needed
    $currentHost = null;
    if (preg_match('/database\.default\.hostname\s*=\s*(\S+)/', $envContent, $m)) {
        $currentHost = $m[1];
    }
}

// Step 2: Create database if needed
$conn = new mysqli($workingHost, 'root', '', '', $workingPort);
echo '<div class="box"><h3>2. Database Check</h3>';

$result = $conn->query("SHOW DATABASES LIKE 'attendance_system'");
if ($result->num_rows === 0) {
    echo "<p class='warn'>⚠ Database 'attendance_system' does not exist. Creating...</p>";
    if ($conn->query("CREATE DATABASE attendance_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci")) {
        echo "<p class='ok'>✓ Database created!</p>";
    } else {
        echo "<p class='fail'>✗ Failed to create database: " . $conn->error . "</p>";
    }
} else {
    echo "<p class='ok'>✓ Database 'attendance_system' exists</p>";
}
echo '</div>';

// Step 3: Create tables
$conn->select_db('attendance_system');
echo '<div class="box"><h3>3. Table Setup</h3>';

$tables = [
    'employees' => "CREATE TABLE IF NOT EXISTS `employees` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `emp_code` VARCHAR(50) NOT NULL UNIQUE,
        `name` VARCHAR(255) NOT NULL,
        `department` VARCHAR(100) DEFAULT NULL,
        `designation` VARCHAR(100) DEFAULT NULL,
        `employee_type` ENUM('full_time','intern') DEFAULT 'full_time',
        `status` ENUM('active','inactive') DEFAULT 'active',
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX `idx_emp_code` (`emp_code`),
        INDEX `idx_status` (`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    
    'punch_logs' => "CREATE TABLE IF NOT EXISTS `punch_logs` (
        `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
        `emp_code` VARCHAR(50) NOT NULL,
        `punch_time` DATETIME NOT NULL,
        `source` VARCHAR(50) DEFAULT 'api',
        `raw_data` TEXT DEFAULT NULL,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY `uk_emp_punch` (`emp_code`, `punch_time`),
        INDEX `idx_emp_code` (`emp_code`),
        INDEX `idx_punch_time` (`punch_time`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    
    'attendance_daily' => "CREATE TABLE IF NOT EXISTS `attendance_daily` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `emp_code` VARCHAR(50) NOT NULL,
        `date` DATE NOT NULL,
        `first_in` DATETIME DEFAULT NULL,
        `last_out` DATETIME DEFAULT NULL,
        `work_minutes` INT DEFAULT 0,
        `late_minutes` INT DEFAULT 0,
        `status` ENUM('present','half_day','absent') DEFAULT 'absent',
        `punch_count` INT DEFAULT 0,
        `employee_type` ENUM('full_time','intern') DEFAULT 'full_time',
        `required_minutes` INT DEFAULT 510,
        `validation_errors` TEXT DEFAULT NULL,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY `uk_emp_date` (`emp_code`, `date`),
        INDEX `idx_date` (`date`),
        INDEX `idx_status` (`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    
    'sync_logs' => "CREATE TABLE IF NOT EXISTS `sync_logs` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `sync_type` VARCHAR(50) NOT NULL DEFAULT 'incremental',
        `status` ENUM('running','success','failed') DEFAULT 'running',
        `records_fetched` INT DEFAULT 0,
        `records_saved` INT DEFAULT 0,
        `last_record_id` VARCHAR(100) DEFAULT NULL,
        `error_message` TEXT DEFAULT NULL,
        `started_at` DATETIME DEFAULT NULL,
        `completed_at` DATETIME DEFAULT NULL,
        INDEX `idx_status` (`status`),
        INDEX `idx_started_at` (`started_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    
    'admins' => "CREATE TABLE IF NOT EXISTS `admins` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `username` VARCHAR(100) NOT NULL UNIQUE,
        `password_hash` VARCHAR(255) NOT NULL,
        `role` VARCHAR(50) DEFAULT 'admin',
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
];

$allOk = true;
foreach ($tables as $name => $sql) {
    if ($conn->query($sql)) {
        echo "<p class='ok'>✓ Table <code>$name</code> — OK</p>";
    } else {
        echo "<p class='fail'>✗ Table <code>$name</code> — " . $conn->error . "</p>";
        $allOk = false;
    }
}

// Also clear any stuck "running" syncs
$conn->query("UPDATE sync_logs SET status='failed', error_message='Reset by db_test.php — was stuck running', completed_at=NOW() WHERE status='running'");

echo '</div>';

// Step 4: Final verification
echo '<div class="box"><h3>4. Final Verification</h3>';
$result = $conn->query("SHOW TABLES");
echo '<table><tr><th>Table</th><th>Rows</th></tr>';
while ($row = $result->fetch_array()) {
    $tableName = $row[0];
    $countResult = $conn->query("SELECT COUNT(*) as cnt FROM `$tableName`");
    $count = $countResult ? $countResult->fetch_assoc()['cnt'] : '?';
    echo "<tr><td><code>$tableName</code></td><td>$count</td></tr>";
}
echo '</table></div>';

$conn->close();

echo '<div class="box"><h3 class="ok">✅ Setup Complete!</h3>';
echo '<p>Your database is ready. Next steps:</p>';
echo '<ol>';
echo '<li>Start the CI4 server: <code>php spark serve --port 8080</code></li>';
echo '<li>Go to <a href="http://localhost:8080/login">http://localhost:8080/login</a></li>';
echo '<li>Login with your eTimeOffice credentials</li>';
echo '<li>Navigate to <strong>Sync Data</strong> and run a sync</li>';
echo '</ol>';
echo '<p class="warn">⚠ Delete this file after setup: <code>public/db_test.php</code></p>';
echo '</div>';
?>
</body>
</html>
