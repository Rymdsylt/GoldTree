<?php
require_once '../../db/connection.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

try {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (!$current_password || !$new_password || !$confirm_password) {
        throw new Exception('All password fields are required');
    }

    if ($new_password !== $confirm_password) {
        throw new Exception('New passwords do not match');
    }

    $stmt = $conn->prepare("SELECT password, reset_password FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if (!$user) {
        throw new Exception('User not found');
    }

    $isValid = false;
    if (password_verify($current_password, $user['password'])) {
        $isValid = true;
    } elseif ($user['reset_password'] !== null && $current_password === $user['reset_password']) {
        $isValid = true;
    }

    if (!$isValid) {
        throw new Exception('Current password is incorrect');
    }

    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("UPDATE users SET password = ?, reset_password = NULL WHERE id = ?");
    $stmt->execute([$hashed_password, $_SESSION['user_id']]);

    echo json_encode(['success' => true, 'message' => 'Password changed successfully']);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}