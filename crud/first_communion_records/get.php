<?php
require_once '../../db/connection.php';
require_once '../../auth/check_admin.php';

header('Content-Type: application/json');

try {

    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        throw new Exception('Invalid record ID');
    }

    $id = (int)$_GET['id'];

  
    $query = "SELECT * FROM first_communion_records WHERE id = :id";
    $stmt = $conn->prepare($query);
    $stmt->execute([':id' => $id]);


    $record = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$record) {
        throw new Exception('Record not found');
    }

   
    echo json_encode($record);

} catch (Exception $e) {
    http_response_code(404);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
