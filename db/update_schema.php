<?php
require_once 'connection.php';

try {

    $conn->exec("ALTER TABLE event_attendance ADD COLUMN IF NOT EXISTS attendance_date DATE DEFAULT CURRENT_DATE");
    

    $conn->exec("CREATE INDEX IF NOT EXISTS idx_event_attendance_date ON event_attendance(event_id, member_id, attendance_date)");
    
    
    $conn->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS privacy_agreement BOOLEAN DEFAULT NULL");
    
    echo "Schema updated successfully";
} catch(PDOException $e) {
    echo "Error updating schema: " . $e->getMessage();
}
?>