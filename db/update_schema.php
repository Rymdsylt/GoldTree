<?php
require_once 'connection.php';

try {
    // Add attendance_date column if it doesn't exist
    $conn->exec("ALTER TABLE event_attendance ADD COLUMN IF NOT EXISTS attendance_date DATE DEFAULT CURRENT_DATE");
    
    // Create index for performance
    $conn->exec("CREATE INDEX IF NOT EXISTS idx_event_attendance_date ON event_attendance(event_id, member_id, attendance_date)");
    
    echo "Schema updated successfully";
} catch(PDOException $e) {
    echo "Error updating schema: " . $e->getMessage();
}