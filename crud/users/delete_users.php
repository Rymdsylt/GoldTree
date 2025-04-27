<?php
require_once '../../db/connection.php';

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Invalid request method';
    echo json_encode($response);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id'])) {
    $response['message'] = 'User ID not provided';
    echo json_encode($response);
    exit;
}

try {

    $stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
    $stmt->execute([$data['id']]);
    $user = $stmt->fetch();
    
    if ($user && $user['username'] === 'root') {
        $response['message'] = 'Cannot delete root admin user';
        echo json_encode($response);
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$data['id']]);
    
    if ($stmt->rowCount() > 0) {
        $response['success'] = true;
        $response['message'] = 'User deleted successfully';
    } else {
        $response['message'] = 'User not found';
    }
} catch(PDOException $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>