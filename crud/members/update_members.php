<?php
require_once '../../db/connection.php';
header('Content-Type: application/json');

try {
    $data = $_POST;
    
    if (empty($data['id'])) {
        throw new Exception('Member ID is required');
    }

 
    if (empty($data['associated_user'])) {
        throw new Exception('An associated user is required for each member');
    }

    $checkQuery = "SELECT id FROM members WHERE user_id = ? AND id != ?";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->execute([$data['associated_user'], $data['id']]);
    
    if ($checkStmt->fetch()) {
        echo json_encode([
            'success' => false,
            'message' => 'This user is already associated with another member'
        ]);
        exit;
    }

    $conn->beginTransaction();

    
    $clearOldUserQuery = "UPDATE users SET member_id = NULL WHERE member_id = ?";
    $clearStmt = $conn->prepare($clearOldUserQuery);
    $clearStmt->execute([$data['id']]);

 
    $query = "UPDATE members SET 
              first_name = ?,
              last_name = ?,
              email = ?,
              phone = ?,
              address = ?,
              date_of_birth = ?,
              gender = ?,
              category = ?,
              status = ?,
              user_id = ?
              WHERE id = ?";
              
    $stmt = $conn->prepare($query);
    $stmt->execute([
        $data['first_name'],
        $data['last_name'],
        $data['email'],
        $data['phone'] ?? null,
        $data['address'] ?? null,
        $data['date_of_birth'] ?? null,
        $data['gender'] ?? null,
        $data['category'] ?? null,
        $data['status'] ?? 'active',
        $data['associated_user'],
        $data['id']
    ]);

   
    $updateUserQuery = "UPDATE users SET member_id = ? WHERE id = ?";
    $updateUserStmt = $conn->prepare($updateUserQuery);
    $updateUserStmt->execute([$data['id'], $data['associated_user']]);

    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Member updated successfully'
    ]);

} catch (PDOException $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    error_log('Database error in update_members.php: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    error_log('Error in update_members.php: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
