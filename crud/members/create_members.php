<?php
require_once '../../db/connection.php';
header('Content-Type: application/json');

try {
    $first_name = $_POST['first_name'] ?? '';
    $last_name = $_POST['last_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';
    $birthdate = $_POST['birthdate'] ?? null;

    // Check if database is PostgreSQL
    $isPostgres = (getenv('DATABASE_URL') !== false);
    
    // Use database-specific timestamp function
    if ($isPostgres) {
        $stmt = $conn->prepare("INSERT INTO members (first_name, last_name, email, phone, address, birthdate, membership_date) VALUES (?, ?, ?, ?, ?, ?, CURRENT_DATE)");
    } else {
        $stmt = $conn->prepare("INSERT INTO members (first_name, last_name, email, phone, address, birthdate, membership_date) VALUES (?, ?, ?, ?, ?, ?, NOW())");
    }
    $stmt->execute([$first_name, $last_name, $email, $phone, $address, $birthdate]);
    
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
