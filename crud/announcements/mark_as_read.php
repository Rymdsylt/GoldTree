<?php
session_start();
require_once '../../db/connection.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Check if database is PostgreSQL
$isPostgres = (getenv('DATABASE_URL') !== false);

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['id'])) {
        throw new Exception('Notification ID is required');
    }

    // Use database-specific boolean value
    $is_read_value = $isPostgres ? true : 1;
    
    $stmt = $conn->prepare("
        UPDATE notification_recipients 
        SET is_read = ? 
        WHERE notification_id = ? 
        AND user_id = ?
    ");
    
    $stmt->execute([$is_read_value, $data['id'], $_SESSION['user_id']]);
    
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>