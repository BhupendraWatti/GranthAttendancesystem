<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=attendance_system', 'root', '');
    $pdo->exec("ALTER TABLE employees ADD COLUMN salary DECIMAL(10,2) NULL DEFAULT NULL AFTER employee_type");
    echo "Column 'salary' added successfully.";
} catch (Exception $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "Column 'salary' already exists.";
    } else {
        echo 'ERROR: ' . $e->getMessage();
    }
}
