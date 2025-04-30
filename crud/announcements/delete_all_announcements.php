<?php
session_start();
require_once '../../db/connection.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

try {
    $conn->beginTransaction();


    $stmt = $conn->prepare("DELETE FROM notification_recipients WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);


    $stmt = $conn->prepare("
        DELETE n FROM notifications n
        LEFT JOIN notification_recipients nr ON n.id = nr.notification_id
        WHERE nr.id IS NULL
    ");
    $stmt->execute();

    $conn->commit();
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>