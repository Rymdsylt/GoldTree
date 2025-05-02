<?php
require_once '../db/connection.php';

header('Content-Type: application/json');

if (isset($_GET['all']) && $_GET['all'] === 'true') {

    $stmt = $conn->prepare("SELECT id, username, email, admin_status FROM users ORDER BY username");
    $stmt->execute();
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

$search = $_GET['search'] ?? '';

if (empty($search)) {
    echo json_encode([]);
    exit;
}

$stmt = $conn->prepare("
    SELECT id, username, email, admin_status 
    FROM users 
    WHERE username LIKE ? OR email LIKE ? OR id = ?
    ORDER BY username
    LIMIT 10
");

$searchTerm = "%$search%";
$stmt->execute([$searchTerm, $searchTerm, $search]);

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
?>