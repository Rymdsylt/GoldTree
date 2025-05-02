<?php
require_once '../../db/connection.php';

try {
    // Get users that are not already associated with members
    $query = "SELECT u.id, u.username, u.email 
              FROM users u 
              LEFT JOIN members m ON u.id = m.user_id 
              WHERE m.id IS NULL";
    
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'users' => $users
    ]);
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching available users: ' . $e->getMessage()
    ]);
}
?>