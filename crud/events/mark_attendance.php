<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../db/connection.php';
require_once '../../auth/login_status.php';

header('Content-Type: application/json');

if (!isset($_POST['event_id']) || !isset($_POST['member_id']) || !isset($_POST['status'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

$eventId = $_POST['event_id'];
$memberId = $_POST['member_id'];
$status = $_POST['status'];


if (!in_array($status, ['present', 'absent'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid attendance status']);
    exit;
}

try {
    $eventStmt = $conn->prepare("SELECT id FROM events WHERE id = ? AND status = 'ongoing'");
    $eventStmt->execute([$eventId]);
    
    if (!$eventStmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Event not found or not ongoing']);
        exit;
    }

    $memberStmt = $conn->prepare("
        SELECT m.id 
        FROM members m
        INNER JOIN users u ON m.user_id = u.id
        INNER JOIN event_assignments ea ON u.id = ea.user_id
        WHERE m.id = ? AND ea.event_id = ?
    ");
    $memberStmt->execute([$memberId, $eventId]);
    
    if (!$memberStmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Member not found or not assigned to this event']);
        exit;
    }


    $conn->beginTransaction();


    $checkStmt = $conn->prepare("
        SELECT id 
        FROM event_attendance 
        WHERE event_id = ? AND member_id = ? AND DATE(attendance_date) = CURRENT_DATE
    ");
    $checkStmt->execute([$eventId, $memberId]);
    $existingRecord = $checkStmt->fetch();

    if ($existingRecord) {

        $updateStmt = $conn->prepare("
            UPDATE event_attendance 
            SET attendance_status = ?, attendance_date = CURRENT_DATE 
            WHERE id = ?
        ");
        $updateStmt->execute([$status, $existingRecord['id']]);
    } else {
 
        $insertStmt = $conn->prepare("
            INSERT INTO event_attendance (event_id, member_id, attendance_status, attendance_date) 
            VALUES (?, ?, ?, CURRENT_DATE)
        ");
        $insertStmt->execute([$eventId, $memberId, $status]);
    }

    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Attendance marked successfully'
    ]);

} catch (PDOException $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>