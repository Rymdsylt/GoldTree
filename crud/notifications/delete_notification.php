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
    $notification_id = $data['id'];    $stmt = $conn->prepare("SELECT id FROM notifications WHERE id = ?");
    $stmt->execute([$notification_id]);
    $unique_id = $stmt->fetch(PDO::FETCH_COLUMN);

    if (!$unique_id) {
        throw new Exception('Notification not found');
    }

    $conn->beginTransaction();    $stmt = $conn->prepare("DELETE FROM notification_recipients WHERE notification_id = ?");
    $stmt->execute([$notification_id]);

    $stmt = $conn->prepare("DELETE FROM notifications WHERE id = ?");
    $stmt->execute([$notification_id]);

    $conn->commit();
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>