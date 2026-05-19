<?php
$db = new mysqli('127.0.0.1', 'root', '', 'attendance_system');
$db->query("UPDATE sync_logs SET status = 'failed', error_message = 'Fatal error aborted sync', completed_at = NOW() WHERE status = 'running'");
echo 'Updated: ' . $db->affected_rows;
