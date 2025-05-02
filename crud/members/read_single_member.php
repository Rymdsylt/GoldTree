<?php
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

    echo json_encode([
        'success' => true,
        'data' => $member
    ]);
} catch(Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
