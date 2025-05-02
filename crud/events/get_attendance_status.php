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

    $eventId = filter_input(INPUT_GET, 'event_id', FILTER_VALIDATE_INT);
    if (!$eventId) {
        throw new Exception('Invalid event ID');
    }

    // Get current user's member ID
    $userStmt = $conn->prepare("
        SELECT m.id as member_id 
        FROM users u 
        JOIN members m ON u.member_id = m.id 
        WHERE u.id = ?
    ");
    $userStmt->execute([$_SESSION['user_id']]);
    $user = $userStmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        throw new Exception('Member not found');
    }

    // Check if user is present today for this event
    $attendanceStmt = $conn->prepare("
        SELECT attendance_status 
        FROM event_attendance 
        WHERE event_id = ? 
        AND member_id = ? 
        AND DATE(attendance_date) = CURRENT_DATE
    ");
    $attendanceStmt->execute([$eventId, $user['member_id']]);
    $attendance = $attendanceStmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'status' => $attendance ? $attendance['attendance_status'] : null
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    exit;
}