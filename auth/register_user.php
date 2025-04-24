<?php
require_once '../db/connection.php';
session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

try {

    $first_name = filter_input(INPUT_POST, 'first_name', FILTER_SANITIZE_STRING);
    $last_name = filter_input(INPUT_POST, 'last_name', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
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

    echo json_encode(['status' => 'success', 'message' => 'Registration successful! You can now login.']);

} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}