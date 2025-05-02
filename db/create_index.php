<?php
require_once 'connection.php';

try {
    $conn->exec('CREATE INDEX IF NOT EXISTS idx_event_attendance_date ON event_attendance(event_id, member_id, attendance_date)');
    echo "Index created successfully";
} catch(PDOException $e) {
    echo "Error creating index: " . $e->getMessage();
}