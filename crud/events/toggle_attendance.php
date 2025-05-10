<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../db/connection.php';
require_once '../../auth/login_status.php';

header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['event_id'])) {
        throw new Exception('Event ID is required');
    }


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


    $eventStmt = $conn->prepare("SELECT status FROM events WHERE id = ?");
    $eventStmt->execute([$data['event_id']]);
    $event = $eventStmt->fetch(PDO::FETCH_ASSOC);

    if (!$event) {
        throw new Exception('Event not found');
    }

    if ($event['status'] !== 'ongoing') {
        throw new Exception('Can only mark attendance for ongoing events');
    }

 
    $checkStmt = $conn->prepare("
        SELECT id, attendance_status 
        FROM event_attendance 
        WHERE event_id = ? 
        AND member_id = ? 
        AND DATE(attendance_date) = CURRENT_DATE
    ");
    $checkStmt->execute([$data['event_id'], $user['member_id']]);
    $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if ($existing) {

        $newStatus = $existing['attendance_status'] === 'present' ? 'absent' : 'present';
        $updateStmt = $conn->prepare("
            UPDATE event_attendance 
            SET attendance_status = ?, 
                attendance_date = CURRENT_TIMESTAMP
            WHERE id = ?
        ");
        $updateStmt->execute([$newStatus, $existing['id']]);
        $status = $newStatus;
    } else {
      
        $insertStmt = $conn->prepare("
            INSERT INTO event_attendance 
            (event_id, member_id, attendance_status, attendance_date) 
            VALUES (?, ?, 'present', CURRENT_TIMESTAMP)
        ");
        $insertStmt->execute([$data['event_id'], $user['member_id']]);
        $status = 'present';
    }

    echo json_encode([
        'success' => true,
        'status' => $status,
        'message' => $status === 'present' ? 'Attendance marked successfully' : 'Attendance removed'
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}