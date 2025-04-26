<?php
require_once '../../db/connection.php';
header('Content-Type: application/json');

try {
    $testConnection = $conn->query("SELECT 1")->fetch();
    
    $stmt = $conn->query("SELECT * FROM events");
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $countStmt = $conn->query("SELECT COUNT(*) as total FROM events");
    $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    echo json_encode([
        'connection_status' => 'Connected successfully',
        'total_events' => $total,
        'events' => $events,
        'php_version' => PHP_VERSION,
        'current_time' => date('Y-m-d H:i:s'),
        'timezone' => date_default_timezone_get()
    ]);
    
} catch(PDOException $e) {
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage(),
        'code' => $e->getCode()
    ]);
}
?>