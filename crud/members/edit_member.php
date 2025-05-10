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
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';
    $date_of_birth = $_POST['date_of_birth'] ?? null;
    $gender = $_POST['gender'] ?? null;
    $category = $_POST['category'] ?? 'regular';
    $status = $_POST['status'] ?? 'active';

 
    $updateFields = [
        'first_name = ?',
        'last_name = ?',
        'email = ?',
        'phone = ?',
        'address = ?',
        'birthdate = ?',
        'gender = ?',
        'category = ?',
        'status = ?'
    ];
    $params = [
        $first_name, $last_name, $email, $phone, $address,
        $date_of_birth, $gender, $category, $status
    ];


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


    $params[] = $id;


    $query = "UPDATE members SET " . implode(', ', $updateFields) . " WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute($params);

    if ($stmt->rowCount() === 0) {
        throw new Exception('Member not found or no changes made');
    }

    echo json_encode([
        'success' => true,
        'message' => 'Member updated successfully'
    ]);

} catch (PDOException $e) {
    error_log('Database error in edit_member.php: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log('Error in edit_member.php: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}