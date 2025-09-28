<?php
header('Content-Type: application/json');
require_once '../../db/connection.php';

try {
    if (!isset($_GET['record_id'])) {
        throw new Exception('Record ID is required');
    }

    $record_id = $_GET['record_id'];

    $query = "SELECT id, sponsor_name FROM matrimony_sponsors WHERE matrimony_record_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$record_id]);
    
    $sponsors = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['status' => 'success', 'data' => $sponsors]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}