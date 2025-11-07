<?php
require_once '../../db/connection.php';

// Check if database is PostgreSQL
$isPostgres = (getenv('DATABASE_URL') !== false);

try {
    // Use database-specific string concatenation
    if ($isPostgres) {
        $stmt = $conn->prepare("
            SELECT u.*, 
                   m.first_name || ' ' || m.last_name as member_name
            FROM users u 
            LEFT JOIN members m ON u.member_id = m.id 
            ORDER BY u.id DESC
        ");
    } else {
        $stmt = $conn->prepare("
            SELECT u.*, 
                   CONCAT(m.first_name, ' ', m.last_name) as member_name
            FROM users u 
            LEFT JOIN members m ON u.member_id = m.id 
            ORDER BY u.id DESC
        ");
    }
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($users);
} catch(PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>