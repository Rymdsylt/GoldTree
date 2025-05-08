<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../db/connection.php';
require_once '../../auth/login_status.php';

header('Content-Type: application/json');

try {
    if (!isset($_GET['event_id'])) {
        throw new Exception('Event ID is required');
    }

    // Get event details
    $eventStmt = $conn->prepare("
        SELECT id, title, start_datetime, end_datetime, status
        FROM events 
        WHERE id = ?
    ");
    $eventStmt->execute([$_GET['event_id']]);
    $event = $eventStmt->fetch(PDO::FETCH_ASSOC);

    if (!$event) {
        throw new Exception('Event not found');
    }

    // Get all members and their attendance status for today
    $memberStmt = $conn->prepare("
        SELECT 
            m.id,
            m.first_name,
            m.last_name,
            ea.attendance_status
        FROM members m
        LEFT JOIN event_attendance ea ON 
            ea.member_id = m.id 
            AND ea.event_id = ?
            AND DATE(ea.attendance_date) = CURRENT_DATE
        WHERE m.status = 'active'
        ORDER BY m.first_name, m.last_name
    ");
    $memberStmt->execute([$_GET['event_id']]);
    $members = $memberStmt->fetchAll(PDO::FETCH_ASSOC);

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