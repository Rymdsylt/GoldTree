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
    $admin_status = $_POST['admin_status'];
    
    // Check if database is PostgreSQL
    $isPostgres = (getenv('DATABASE_URL') !== false);
    
    // Convert admin_status to proper boolean/integer value
    $admin_status_value = $isPostgres ? (($admin_status == 1 || $admin_status === '1' || $admin_status === true) ? true : false) : (($admin_status == 1 || $admin_status === '1' || $admin_status === true) ? 1 : 0);
    
    $stmt = $conn->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
    $stmt->execute([$username, $email, $id]);
    if ($stmt->rowCount() > 0) {
        $response['message'] = 'Username or email already exists';
        echo json_encode($response);
        exit;
    }

    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, password = ?, admin_status = ? WHERE id = ?");
        $stmt->execute([$username, $email, $password, $admin_status_value, $id]);
    } else {
        $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, admin_status = ? WHERE id = ?");
        $stmt->execute([$username, $email, $admin_status_value, $id]);
    }

    $response['success'] = true;
    $response['message'] = 'User updated successfully';
    
} catch(PDOException $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>