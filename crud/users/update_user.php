<?php
require_once '../../db/connection.php';

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Invalid request method';
    echo json_encode($response);
    exit;
}

try {
    $id = $_POST['id'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $member_id = !empty($_POST['member_id']) ? $_POST['member_id'] : null;
    $admin_status = $_POST['admin_status'];
    
    $stmt = $conn->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
    $stmt->execute([$username, $email, $id]);
    if ($stmt->rowCount() > 0) {
        $response['message'] = 'Username or email already exists';
        echo json_encode($response);
        exit;
    }


    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, password = ?, member_id = ?, admin_status = ? WHERE id = ?");
        $stmt->execute([$username, $email, $password, $member_id, $admin_status, $id]);
    } else {

        $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, member_id = ?, admin_status = ? WHERE id = ?");
        $stmt->execute([$username, $email, $member_id, $admin_status, $id]);
    }

    $response['success'] = true;
    $response['message'] = 'User updated successfully';
} catch(PDOException $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>