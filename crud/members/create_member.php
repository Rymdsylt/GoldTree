<?php
require_once '../../db/connection.php';
header('Content-Type: application/json');

try {
    $first_name = $_POST['first_name'] ?? '';
    $last_name = $_POST['last_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';
    $date_of_birth = $_POST['date_of_birth'] ?? null;
    $gender = $_POST['gender'] ?? null;
    $category = $_POST['category'] ?? 'regular';
    $status = $_POST['status'] ?? 'active';

    $checkStmt = $conn->prepare("SELECT id FROM members WHERE email = ?");
    $checkStmt->execute([$email]);
    if ($checkStmt->rowCount() > 0) {
        throw new Exception('A member with this email already exists');
    }

    $profile_image = null;
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['profile_image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $profile_image = file_get_contents($_FILES['profile_image']['tmp_name']);
        }
    }

    $stmt = $conn->prepare("
        INSERT INTO members (
            first_name, last_name, email, phone, address, 
            birthdate, gender, category, status, profile_image,
            membership_date
        ) VALUES (
            ?, ?, ?, ?, ?, 
            ?, ?, ?, ?, ?,
            CURRENT_DATE
        )
    ");

    $stmt->execute([
        $first_name, $last_name, $email, $phone, $address,
        $date_of_birth, $gender, $category, $status, $profile_image
    ]);

    $memberId = $conn->lastInsertId();

    echo json_encode([
        'success' => true,
        'message' => 'Member added successfully',
        'memberId' => $memberId
    ]);

} catch (PDOException $e) {
    error_log('Database error in create_member.php: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log('Error in create_member.php: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}