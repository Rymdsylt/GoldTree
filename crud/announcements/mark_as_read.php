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
    
    if (!isset($data['id'])) {
        throw new Exception('Notification ID is required');
    }

    $stmt = $conn->prepare("
        UPDATE notification_recipients 
        SET is_read = 1 
        WHERE notification_id = ? 
        AND user_id = ?
    ");
    
    $stmt->execute([$data['id'], $_SESSION['user_id']]);
    
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>