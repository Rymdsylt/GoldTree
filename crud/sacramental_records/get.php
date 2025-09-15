<?php
require_once '../../auth/login_status.php';
require_once '../../db/connection.php';

header('Content-Type: application/json');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => true, 'message' => 'Invalid record ID']);
    exit;
}

try {
    $stmt = $conn->prepare("SELECT * FROM sacramental_records WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $record = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$record) {
        http_response_code(404);
        echo json_encode(['error' => true, 'message' => 'Record not found']);
        exit;
    }

    echo json_encode($record);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => true, 'message' => 'Database error']);
    error_log($e->getMessage());
}
