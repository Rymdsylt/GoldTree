<?php
require_once '../../db/connection.php';
require_once '../../auth/check_admin.php';

header('Content-Type: application/json');

if (!isset($_POST['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'ID is required']);
    exit;
}

try {
    $id = intval($_POST['id']);
    
    $conn->beginTransaction();
    

    $deleteSponsors = "DELETE FROM baptismal_sponsors WHERE baptismal_record_id = ?";
    $stmt = $conn->prepare($deleteSponsors);
    $stmt->execute([$id]);

    $deleteRecord = "DELETE FROM baptismal_records WHERE id = ?";
    $stmt = $conn->prepare($deleteRecord);
    $stmt->execute([$id]);
    
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Record deleted successfully'
    ]);
} catch(Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to delete record: ' . $e->getMessage()
    ]);
}
?>