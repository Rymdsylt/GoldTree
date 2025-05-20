<?php
require_once '../../db/connection.php';
require_once '../../auth/login_status.php';

header('Content-Type: application/json');

try {
    if (!isset($_GET['event_id'])) {
        throw new Exception('Event ID is required');
    }

    $eventId = filter_input(INPUT_GET, 'event_id', FILTER_VALIDATE_INT);
    if (!$eventId) {
        throw new Exception('Invalid event ID');
    }

    // Get event details
    $eventStmt = $conn->prepare("
        SELECT title, start_datetime, end_datetime 
        FROM events 
        WHERE id = ?
    ");
    $eventStmt->execute([$eventId]);
    $event = $eventStmt->fetch(PDO::FETCH_ASSOC);

    if (!$event) {
        throw new Exception('Event not found');
    }

    // Get all active members and their current attendance status
    $stmt = $conn->prepare("
        SELECT 
            m.id,
            m.first_name,
            m.last_name,
            COALESCE(ea.attendance_status, 'no_record') as attendance_status
        FROM members m
        LEFT JOIN event_attendance ea ON 
            ea.member_id = m.id 
            AND ea.event_id = ?
            AND DATE(ea.attendance_date) = CURRENT_DATE
        WHERE m.status = 'active'
        ORDER BY m.first_name, m.last_name
    ");
    $stmt->execute([$eventId]);
    $members = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'event' => $event,
        'members' => $members
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}