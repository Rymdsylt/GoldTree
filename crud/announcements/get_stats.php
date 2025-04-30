<?php
session_start();
require_once '../../db/connection.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

try {
    $stmt = $conn->prepare("
        SELECT COUNT(*) as total,
               SUM(CASE WHEN nr.is_read = 0 THEN 1 ELSE 0 END) as unread,
               COUNT(CASE WHEN nr.is_read = 1 THEN 1 END) * 100.0 / COUNT(*) as read_rate
        FROM notifications n
        INNER JOIN notification_recipients nr ON n.id = nr.notification_id
        WHERE nr.user_id = ?
    ");
    
    $stmt->execute([$_SESSION['user_id']]);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'total' => (int)$stats['total'],
        'active' => (int)$stats['unread'],
        'readRate' => round($stats['read_rate'] ?? 0, 1)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>