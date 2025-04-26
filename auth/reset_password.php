<?php
require_once '../db/connection.php';
session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    $verification_code = $_POST['verification_code'];
    $email = $_POST['email'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (!isset($_SESSION['reset_code']) || !isset($_SESSION['reset_email']) || !isset($_SESSION['reset_time'])) {
        throw new Exception('Please request a new verification code');
    }

    if (time() - $_SESSION['reset_time'] > 600) {
        unset($_SESSION['reset_code']);
        unset($_SESSION['reset_email']);
        unset($_SESSION['reset_time']);
        throw new Exception('Verification code has expired. Please request a new one');
    }

    if ($verification_code !== $_SESSION['reset_code'] || $email !== $_SESSION['reset_email']) {
        throw new Exception('Invalid verification code');
    }

    if ($new_password !== $confirm_password) {
        throw new Exception('Passwords do not match');
    }

    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
    $stmt->execute([$hashed_password, $email]);

    unset($_SESSION['reset_code']);
    unset($_SESSION['reset_email']);
    unset($_SESSION['reset_time']);

    echo json_encode(['success' => true, 'message' => 'Password has been reset successfully']);

} catch(Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>