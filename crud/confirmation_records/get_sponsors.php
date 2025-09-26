<?php
require_once '../../db/connection.php';
require_once '../../auth/check_admin.php';

header('Content-Type: application/json');

if (!isset($_GET['record_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Record ID is required']);
    exit;
}

try {
    $record_id = intval($_GET['record_id']);
    
    $query = "SELECT id, sponsor_name FROM confirmation_sponsors WHERE confirmation_record_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$record_id]);
    
    $sponsors = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($sponsors);
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>