<?php
session_start();
require_once '../../db/connection.php';
header('Content-Type: application/json');

try {
    if (!isset($_GET['id'])) {
        throw new Exception('Member ID is required');
    }
      $query = "SELECT m.*, u.id as user_id, u.username as associated_username 
              FROM members m 
              LEFT JOIN users u ON m.user_id = u.id 
              WHERE m.id = ?";
              
    $stmt = $conn->prepare($query);
    $stmt->execute([$_GET['id']]);
    $member = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$member) {
        throw new Exception('Member not found');
    }
    
    // Check if the member being viewed is associated with the root user
    $isRootMember = $member['associated_username'] === 'root';
    
    if ($member['profile_image'] !== null) {
        $member['profile_image'] = base64_encode($member['profile_image']);
    }

    echo json_encode([
        'success' => true,
        'data' => $member,
        'canEdit' => !$isRootMember  // Only allow editing if it's not the root member
    ]);
} catch(PDOException $e) {
    error_log("Database error in read_single_member.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
} catch(Exception $e) {
    error_log("Error in read_single_member.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
