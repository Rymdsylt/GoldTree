<?php
header('Content-Type: application/json');
require_once '../../db/connection.php';

try {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data || !isset($data['id'])) {
        throw new Exception('Record ID is required');
    }

    $id = $data['id'];

 
    $conn->beginTransaction();


  
    $stmt = $conn->prepare("DELETE FROM matrimony_records WHERE id = ?");
    $stmt->execute([$id]);

    if ($stmt->rowCount() === 0) {
        throw new Exception('Record not found');
    }

    $conn->commit();
    echo json_encode(['status' => 'success', 'message' => 'Record deleted successfully']);

} catch (PDOException $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}