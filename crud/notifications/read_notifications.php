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
        SELECT 
            n.*,
            COUNT(DISTINCT nr.member_id) as recipient_count,
            COUNT(DISTINCT CASE WHEN nr.email_sent = 1 THEN nr.member_id END) as emails_sent
        FROM notifications n
        LEFT JOIN notification_recipients nr ON n.id = nr.notification_id
        GROUP BY n.id
        ORDER BY n.created_at DESC
        LIMIT 50
    ");
    
    $stmt->execute();
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($notifications);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>