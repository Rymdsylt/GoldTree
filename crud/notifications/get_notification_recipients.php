<?php
session_start();
require_once '../../db/connection.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

try {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['notification_id'])) {
        echo json_encode(['success' => false, 'message' => 'Notification ID is required']);
        exit();
    }

    $stmt = $conn->prepare("
        SELECT u.id, u.username, u.email, nr.is_read, nr.email_sent
        FROM notification_recipients nr
        JOIN users u ON nr.user_id = u.id
        WHERE nr.notification_id = ?
    ");
    
    $stmt->execute([$data['notification_id']]);
    $recipients = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'recipients' => $recipients
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>