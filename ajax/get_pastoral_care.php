<?php
require_once '../db/connection.php';

header('Content-Type: application/json');

$member_id = isset($_GET['member_id']) ? (int)$_GET['member_id'] : 0;

if (!$member_id) {
    echo json_encode([]);
    exit;
}

try {
    $stmt = $conn->prepare("
        SELECT * FROM pastoral_care 
        WHERE member_id = ? 
        ORDER BY care_date DESC
    ");
    $stmt->execute([$member_id]);
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($records);
} catch (PDOException $e) {
    echo json_encode([
        'error' => 'Error fetching pastoral care records: ' . $e->getMessage()
    ]);
}
?>
