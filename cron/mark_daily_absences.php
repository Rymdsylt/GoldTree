<?php
require_once '../db/connection.php';

// Check if database is PostgreSQL
$isPostgres = (getenv('DATABASE_URL') !== false);

try {
    $today = date('Y-m-d');
    
    // Use database-specific timestamp function
    if ($isPostgres) {
        $stmt = $conn->prepare("
            SELECT e.id, e.title, m.id as member_id 
            FROM events e 
            CROSS JOIN members m 
            WHERE e.status = 'ongoing' 
            AND e.start_datetime <= CURRENT_TIMESTAMP 
            AND e.end_datetime >= CURRENT_TIMESTAMP
        ");
    } else {
        $stmt = $conn->prepare("
            SELECT e.id, e.title, m.id as member_id 
            FROM events e 
            CROSS JOIN members m 
            WHERE e.status = 'ongoing' 
            AND e.start_datetime <= NOW() 
            AND e.end_datetime >= NOW()
        ");
    }
    $stmt->execute();
    $membersEvents = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($membersEvents as $record) {
        $eventStartDateStmt = $conn->prepare("
            SELECT start_datetime 
            FROM events 
            WHERE id = ?
        ");
        $eventStartDateStmt->execute([$record['id']]);
        $eventStartDate = $eventStartDateStmt->fetchColumn();

        $stmt = $conn->prepare("
            SELECT id 
            FROM event_attendance 
            WHERE event_id = ? 
            AND member_id = ? 
            AND attendance_date = ?
        ");
        $stmt->execute([$record['id'], $record['member_id'], $today]);
        
        if (!$stmt->fetch()) {
  
            $status = (strtotime($today) < strtotime($eventStartDate)) ? 'no_record' : 'absent';
          
            $stmt = $conn->prepare("
                INSERT INTO event_attendance 
                (event_id, member_id, attendance_status, attendance_date) 
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$record['id'], $record['member_id'], $status, $today]);
        }
    }
    
    echo "Successfully marked absences for " . date('Y-m-d H:i:s');
} catch (Exception $e) {
    error_log("Error marking absences: " . $e->getMessage());
    echo "Error: " . $e->getMessage();
}