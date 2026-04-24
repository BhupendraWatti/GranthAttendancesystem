<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=attendance_system', 'root', '');
    $stmt = $pdo->query('DESCRIBE employees');
    foreach ($stmt as $row) {
        echo $row['Field'] . ' | ' . $row['Type'] . ' | ' . $row['Null'] . ' | ' . $row['Default'] . PHP_EOL;
    }
} catch (Exception $e) {
    echo 'ERROR: ' . $e->getMessage();
}
