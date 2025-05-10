<?php
require_once '../../db/connection.php';

header('Content-Type: application/json');

if (!isset($_GET['event_id'])) {
    echo json_encode(['success' => false, 'message' => 'Event ID is required']);
    exit;
}

$eventId = $_GET['event_id'];

try {

    $eventStmt = $conn->prepare("SELECT id FROM events WHERE id = ?");
    $eventStmt->execute([$eventId]);
    
    if (!$eventStmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Event not found']);
        exit;
    }

    $staffStmt = $conn->prepare("
        SELECT DISTINCT u.id, u.username, u.email
        FROM users u
        INNER JOIN event_assignments ea ON u.id = ea.user_id
        WHERE ea.event_id = ?
        ORDER BY u.username
    ");
    
    $staffStmt->execute([$eventId]);
    $staff = $staffStmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'staff' => $staff
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>