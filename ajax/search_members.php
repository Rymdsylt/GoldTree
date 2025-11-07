<?php
require_once '../db/connection.php';

header('Content-Type: application/json');

if (!isset($_GET['search'])) {
    echo json_encode([]);
    exit();
}

$search = $_GET['search'];

// Check if database is PostgreSQL
$isPostgres = (getenv('DATABASE_URL') !== false);

try {
    // Use database-specific string concatenation
    if ($isPostgres) {
        $stmt = $conn->prepare("
            SELECT id, first_name, last_name, email, status, category 
            FROM members 
            WHERE (first_name || ' ' || last_name) LIKE :search 
            OR email LIKE :search
            LIMIT 5
        ");
    } else {
        $stmt = $conn->prepare("
            SELECT id, first_name, last_name, email, status, category 
            FROM members 
            WHERE CONCAT(first_name, ' ', last_name) LIKE :search 
            OR email LIKE :search
            LIMIT 5
        ");
    }
    
    $stmt->execute(['search' => "%$search%"]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($results);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
