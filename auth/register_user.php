<?php
require_once '../db/connection.php';
session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

try {
    $verification_code = $_POST['verification_code'];
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);

    if (!isset($_SESSION['verification_code']) || !isset($_SESSION['verification_email']) || !isset($_SESSION['verification_time'])) {
        throw new Exception('Please request a verification code first');
    }

    if (time() - $_SESSION['verification_time'] > 600) {
        unset($_SESSION['verification_code']);
        unset($_SESSION['verification_email']);
        unset($_SESSION['verification_time']);
        throw new Exception('Verification code has expired. Please request a new one');
    }

    if ($verification_code !== $_SESSION['verification_code'] || $email !== $_SESSION['verification_email']) {
        throw new Exception('Invalid verification code');
    }

    $first_name = filter_input(INPUT_POST, 'first_name', FILTER_SANITIZE_STRING);
    $last_name = filter_input(INPUT_POST, 'last_name', FILTER_SANITIZE_STRING);
    $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $address = filter_input(INPUT_POST, 'address', FILTER_SANITIZE_STRING);
    $birthdate = filter_input(INPUT_POST, 'birthdate', FILTER_SANITIZE_STRING);
    $membership_date = filter_input(INPUT_POST, 'membership_date', FILTER_SANITIZE_STRING);

    if (!$first_name || !$last_name || !$email || !$password || !$confirm_password) {
        throw new Exception('Please fill in all required fields');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email format');
    }

    if ($password !== $confirm_password) {
        throw new Exception('Passwords do not match');
    }

    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->rowCount() > 0) {
        throw new Exception('Email already registered');
    }

    $conn->beginTransaction();

    $username = explode('@', $email)[0];
    
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    $stmt = $conn->prepare("INSERT INTO users (username, password, email) VALUES (?, ?, ?)");
    $stmt->execute([$username, $hashed_password, $email]);
    $user_id = $conn->lastInsertId();

    $stmt = $conn->prepare("INSERT INTO members (first_name, last_name, email, phone, address, birthdate, membership_date) 
                           VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$first_name, $last_name, $email, $phone, $address, $birthdate, $membership_date]);
    $member_id = $conn->lastInsertId();

    $stmt = $conn->prepare("UPDATE users SET member_id = ? WHERE id = ?");
    $stmt->execute([$member_id, $user_id]);

    $conn->commit();

    unset($_SESSION['verification_code']);
    unset($_SESSION['verification_email']);
    unset($_SESSION['verification_time']);

    echo json_encode(['status' => 'success', 'message' => 'Registration successful! You can now login.']);

} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}