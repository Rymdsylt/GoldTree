<?php
require_once '../../auth/login_status.php';
require_once '../../db/connection.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$id = isset($input['id']) ? $input['id'] : (isset($_POST['id']) ? $_POST['id'] : null);

if (!$id || !is_numeric($id)) {
    http_response_code(400);
    echo json_encode(['error' => true, 'message' => 'Invalid record ID']);
    exit;
}

try {
    $stmt = $conn->prepare("DELETE FROM sacramental_records WHERE id = ?");
    $result = $stmt->execute([$id]);

    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Record deleted successfully']);
    } else {
        http_response_code(404);
        echo json_encode(['error' => true, 'message' => 'Record not found']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => true, 'message' => 'Database error']);
    error_log($e->getMessage());
}