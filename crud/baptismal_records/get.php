<?php
require_once '../../db/connection.php';
require_once '../../auth/check_admin.php';

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'ID is required']);
    exit;
}

try {
    $id = intval($_GET['id']);
    
   
    $query = "SELECT * FROM baptismal_records WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$id]);
    
    $record = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$record) {
        http_response_code(404);
        echo json_encode(['error' => 'Record not found']);
        exit;
    }

  
    $query = "SELECT id, sponsor_name FROM baptismal_sponsors WHERE baptismal_record_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$id]);
    
    $sponsors = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $record['sponsors'] = $sponsors;
    
    echo json_encode($record);
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>