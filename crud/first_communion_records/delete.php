<?php
header('Content-Type: application/json');
require_once '../../db/connection.php';

if (!isset($_POST['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Record ID is required']);
    exit();
}

try {
    $query = "DELETE FROM first_communion_records WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$_POST['id']]);

    if ($stmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(['error' => 'Record not found']);
        exit();
    }

    echo json_encode([
        'success' => true,
        'message' => 'Record deleted successfully'
    ]);
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    exit();
}
?>