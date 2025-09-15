<?php
require_once '../../auth/login_status.php';
require_once '../../db/connection.php';

$stmt = $conn->prepare("SELECT admin_status FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user || $user['admin_status'] != 1) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

try {

    $data = json_decode(file_get_contents('php://input'), true);


    $required_fields = ['name', 'age', 'address', 'sacrament_type', 'date', 'priest_presiding'];
    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
            throw new Exception("$field is required");
        }
    }

    
    $stmt = $conn->prepare("
        INSERT INTO sacramental_records (
            name, 
            age, 
            address, 
            sacrament_type, 
            date, 
            priest_presiding
        ) VALUES (?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $data['name'],
        $data['age'],
        $data['address'],
        $data['sacrament_type'],
        $data['date'],
        $data['priest_presiding']
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Record saved successfully',
        'id' => $conn->lastInsertId()
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
