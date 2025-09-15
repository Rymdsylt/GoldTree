<?php
require_once '../../auth/login_status.php';
require_once '../../db/connection.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id']) || !is_numeric($data['id'])) {
    http_response_code(400);
    echo json_encode(['error' => true, 'message' => 'Invalid record ID']);
    exit;
}

$requiredFields = ['name', 'age', 'address', 'sacrament_type', 'date', 'priest_presiding'];
foreach ($requiredFields as $field) {
    if (!isset($data[$field]) || empty(trim($data[$field]))) {
        http_response_code(400);
        echo json_encode(['error' => true, 'message' => ucfirst($field) . ' is required']);
        exit;
    }
}

try {
    $stmt = $conn->prepare("UPDATE sacramental_records 
        SET name = ?, age = ?, address = ?, sacrament_type = ?, 
            date = ?, priest_presiding = ?
        WHERE id = ?");
    
    $result = $stmt->execute([
        $data['name'],
        $data['age'],
        $data['address'],
        $data['sacrament_type'],
        $data['date'],
        $data['priest_presiding'],
        $data['id']
    ]);

    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Record updated successfully']);
    } else {
        http_response_code(404);
        echo json_encode(['error' => true, 'message' => 'Record not found']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => true, 'message' => 'Database error']);
    error_log($e->getMessage());
}