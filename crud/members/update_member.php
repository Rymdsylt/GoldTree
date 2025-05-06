<?php
require_once '../../db/connection.php';
header('Content-Type: application/json');

try {
    $id = $_POST['id'] ?? null;
    if (!$id) {
        throw new Exception('Member ID is required');
    }

    $first_name = $_POST['first_name'] ?? '';
    $last_name = $_POST['last_name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';
    $date_of_birth = $_POST['date_of_birth'] ?? null;
    $gender = $_POST['gender'] ?? null;
    $category = $_POST['category'] ?? null;
    $status = $_POST['status'] ?? 'active';

    // Build update fields excluding email and user_id
    $updateFields = [
        'first_name = ?',
        'last_name = ?',
        'phone = ?',
        'address = ?',
        'birthdate = ?',
        'gender = ?',
        'category = ?',
        'status = ?'
    ];
    
    $params = [
        $first_name,
        $last_name,
        $phone,
        $address,
        $date_of_birth,
        $gender,
        $category,
        $status
    ];

    // Handle profile image upload if provided
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['profile_image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $profile_image = file_get_contents($_FILES['profile_image']['tmp_name']);
            $updateFields[] = 'profile_image = ?';
            $params[] = $profile_image;
        }
    }

    // Add member ID to params
    $params[] = $id;

    // Build and execute update query
    $query = "UPDATE members SET " . implode(', ', $updateFields) . " WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute($params);

    echo json_encode([
        'success' => true,
        'message' => 'Member updated successfully'
    ]);
} catch (Exception $e) {
    error_log('Error in update_member.php: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}