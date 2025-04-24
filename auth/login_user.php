<?php
session_start();
require_once '../db/connection.php';

header('Content-Type: application/json');


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}


$login = filter_var($_POST['login'] ?? '', FILTER_SANITIZE_STRING);
$password = $_POST['password'] ?? '';


if (empty($login) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Please fill in all fields']);
    exit;
}

try {

    $isEmail = filter_var($login, FILTER_VALIDATE_EMAIL);
    $sql = $isEmail 
        ? "SELECT * FROM users WHERE email = ?" 
        : "SELECT * FROM users WHERE username = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([$login]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
        exit;
    }


    if (!password_verify($password, $user['password'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
        exit;
    }

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['email'] = $user['email'];


    $cookie_expiry = time() + (30 * 24 * 60 * 60);
    setcookie('user_id', $user['id'], $cookie_expiry, '/', '', true, true);
    setcookie('username', $user['username'], $cookie_expiry, '/', '', true, true);
    setcookie('logged_in', 'true', $cookie_expiry, '/', '', true, true); // Add this line

    $updateSql = "UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = ?";
    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->execute([$user['id']]);

    echo json_encode([
        'success' => true,
        'message' => 'Login successful',
        'user' => [
            'username' => $user['username'],
            'email' => $user['email']
        ]
    ]);

} catch (Exception $e) {
    error_log("Login error: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ]);
}
