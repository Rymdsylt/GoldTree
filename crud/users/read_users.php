<?php
require_once '../../db/connection.php';

try {
    $stmt = $conn->prepare("SELECT * FROM users ORDER BY id DESC");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($users);
} catch(PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>