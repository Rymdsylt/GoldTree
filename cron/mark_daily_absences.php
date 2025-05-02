<?php
require_once '../db/connection.php';

try {
    $today = date('Y-m-d');
    
    // Get all ongoing events
    $stmt = $conn->prepare("
        SELECT e.id, e.title, m.id as member_id 
        FROM events e 
        CROSS JOIN members m 
        WHERE e.status = 'ongoing' 
        AND e.start_datetime <= NOW() 
        AND e.end_datetime >= NOW()
    ");
    $stmt->execute();
    $membersEvents = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($membersEvents as $record) {
        // Check if attendance already marked for today
        $stmt = $conn->prepare("
            SELECT id 
            FROM event_attendance 
            WHERE event_id = ? 
            AND member_id = ? 
            AND attendance_date = ?
        ");
        $stmt->execute([$record['id'], $record['member_id'], $today]);
        
        if (!$stmt->fetch()) {
            // Mark as absent if no attendance record exists
            $stmt = $conn->prepare("
                INSERT INTO event_attendance 
                (event_id, member_id, attendance_status, attendance_date) 
                VALUES (?, ?, 'absent', ?)
            ");
            $stmt->execute([$record['id'], $record['member_id'], $today]);
        }
    }
    
    echo "Successfully marked absences for " . date('Y-m-d H:i:s');
} catch (Exception $e) {
    error_log("Error marking absences: " . $e->getMessage());
    echo "Error: " . $e->getMessage();
}