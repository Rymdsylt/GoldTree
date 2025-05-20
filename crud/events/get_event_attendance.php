<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../db/connection.php';
require_once '../../auth/login_status.php';

header('Content-Type: application/json');

if (!isset($_GET['event_id'])) {
    echo json_encode(['success' => false, 'message' => 'Event ID is required']);
    exit;
}

$eventId = $_GET['event_id'];

try {

    $eventStmt = $conn->prepare("
        SELECT title, start_datetime, end_datetime 
        FROM events 
        WHERE id = ? AND status = 'ongoing'
    ");
    $eventStmt->execute([$eventId]);
    $event = $eventStmt->fetch(PDO::FETCH_ASSOC);

    if (!$event) {
        echo json_encode(['success' => false, 'message' => 'Event not found or not ongoing']);
        exit;
    }

    $memberStmt = $conn->prepare("
        SELECT DISTINCT 
            m.id,            m.first_name,
            m.last_name,
            COALESCE(ea.attendance_status, 'no_record') as attendance_status
        FROM members m
        INNER JOIN users u ON m.user_id = u.id
        INNER JOIN event_assignments ea_link ON u.id = ea_link.user_id
        LEFT JOIN event_attendance ea ON (
            ea.member_id = m.id 
            AND ea.event_id = ? 
            AND DATE(ea.attendance_date) = CURRENT_DATE
        )
        WHERE ea_link.event_id = ?
        ORDER BY m.first_name, m.last_name
    ");
    
    $memberStmt->execute([$eventId, $eventId]);
    $members = $memberStmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'event' => $event,
        'members' => $members
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>