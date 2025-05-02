<?php
require_once '../../db/connection.php';

header('Content-Type: application/json');

if (!isset($_GET['member_id'])) {
    echo json_encode(['success' => false, 'message' => 'Member ID is required']);
    exit;
}

try {
    $stmt = $conn->prepare("
        SELECT n.*, u.username as created_by_name 
        FROM member_notes n
        LEFT JOIN users u ON n.created_by = u.id
        WHERE n.member_id = ?
        ORDER BY n.created_at DESC
    ");
    $stmt->execute([$_GET['member_id']]);
    $notes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'notes' => $notes
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching notes: ' . $e->getMessage()
    ]);
}
?>