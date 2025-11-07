<?php
require_once '../../db/connection.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    $username = $_POST['username'];
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $admin_status = $_POST['admin_status'];

    if (!$username || !$email || !$password) {
        throw new Exception('Please fill in all required fields');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email format');
    }

    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);
    if ($stmt->rowCount() > 0) {
        throw new Exception('Username or email already exists');
    }

    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // Check if database is PostgreSQL
    $isPostgres = (getenv('DATABASE_URL') !== false);
    
    // Convert admin_status to proper boolean/integer value
    $admin_status_value = $isPostgres ? (($admin_status == 1 || $admin_status === '1' || $admin_status === true) ? true : false) : (($admin_status == 1 || $admin_status === '1' || $admin_status === true) ? 1 : 0);

    $stmt = $conn->prepare("INSERT INTO users (username, password, email, admin_status) VALUES (?, ?, ?, ?)");
    $stmt->execute([$username, $hashed_password, $email, $admin_status_value]);

    echo json_encode(['success' => true, 'message' => 'User created successfully']);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}