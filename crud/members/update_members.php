<?php
require_once '../../db/connection.php';
header('Content-Type: application/json');

try {
    $id = $_POST['id'] ?? '';
    $first_name = $_POST['first_name'] ?? '';
    $last_name = $_POST['last_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';
    $birthdate = $_POST['birthdate'] ?? null;
    $status = $_POST['status'] ?? 'active';

    $stmt = $conn->prepare("UPDATE members SET first_name=?, last_name=?, email=?, phone=?, address=?, birthdate=?, status=? WHERE id=?");
    $stmt->execute([$first_name, $last_name, $email, $phone, $address, $birthdate, $status, $id]);
    
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
