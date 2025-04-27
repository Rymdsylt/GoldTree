<?php
require_once '../../db/connection.php';

if (!isset($_GET['id'])) {
    echo json_encode(['error' => 'User ID not provided']);
    exit;
}

try {
    $stmt = $conn->prepare("SELECT id, username, email, member_id, admin_status FROM users WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo json_encode($user);
    } else {
        echo json_encode(['error' => 'User not found']);
    }
} catch(PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>