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
    // Use database-specific boolean value
    $is_read_value = $isPostgres ? true : 1;
    
    $stmt = $conn->prepare("
        UPDATE notification_recipients 
        SET is_read = ? 
        WHERE user_id = ?
    ");
    
    $stmt->execute([$is_read_value, $_SESSION['user_id']]);
    
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>