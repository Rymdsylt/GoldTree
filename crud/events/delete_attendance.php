<?php
require_once '../../db/connection.php';

header('Content-Type: application/json');

if (!isset($_POST['event_id']) || !isset($_POST['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Event ID and User ID are required']);
    exit;
}

$eventId = $_POST['event_id'];
$userId = $_POST['user_id'];

try {
    $conn->beginTransaction();

 
    $memberStmt = $conn->prepare("SELECT id FROM members WHERE user_id = ?");
    $memberStmt->execute([$userId]);
    $members = $memberStmt->fetchAll(PDO::FETCH_COLUMN);

    if (!empty($members)) {
    
        $attendanceStmt = $conn->prepare("
            DELETE FROM event_attendance 
            WHERE event_id = ? AND member_id IN (" . str_repeat('?,', count($members) - 1) . "?)
        ");
        $params = array_merge([$eventId], $members);
        $attendanceStmt->execute($params);
    }


    $assignmentStmt = $conn->prepare("
        DELETE FROM event_assignments 
        WHERE event_id = ? AND user_id = ?
    ");
    $assignmentStmt->execute([$eventId, $userId]);

    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Staff member and associated attendance records removed successfully'
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