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
    $category = $_POST['category'] ?? '';
    $status = $_POST['status'] ?? 'active';
    $associated_user = $_POST['associated_user'] ?? null;

    // Require associated user
    if (empty($associated_user)) {
        throw new Exception('An associated user is required for each member');
    }

    // Check if selected user is already associated with another member
    $checkQuery = "SELECT id FROM members WHERE user_id = ?";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->execute([$associated_user]);
    
    if ($checkStmt->fetch()) {
        throw new Exception('This user is already associated with another member');
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

    $conn->beginTransaction();

    $stmt = $conn->prepare("
        INSERT INTO members (
            first_name, last_name, email, phone, address, 
            birthdate, gender, category, status, profile_image,
            membership_date, user_id
        ) VALUES (
            ?, ?, ?, ?, ?, 
            ?, ?, ?, ?, ?,
            CURRENT_DATE, ?
        )
    ");

    $stmt->execute([
        $first_name, $last_name, $email, $phone, $address,
        $date_of_birth, $gender, $category, $status, $profile_image,
        $associated_user
    ]);

    $memberId = $conn->lastInsertId();

    // Update the users table with the member ID
    $updateUserQuery = "UPDATE users SET member_id = ? WHERE id = ?";
    $updateUserStmt = $conn->prepare($updateUserQuery);
    $updateUserStmt->execute([$memberId, $associated_user]);

    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Member added successfully',
        'memberId' => $memberId
    ]);

} catch (PDOException $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
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
?>