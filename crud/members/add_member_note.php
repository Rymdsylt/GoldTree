<?php
require_once '../../db/connection.php';
session_start();

header('Content-Type: application/json');

if (!isset($_POST['member_id']) || !isset($_POST['note_text']) || !isset($_POST['note_type'])) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit;
}

try {
    $stmt = $conn->prepare("
        INSERT INTO member_notes (member_id, note_text, note_type, created_by)
        VALUES (?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $_POST['member_id'],
        $_POST['note_text'],
        $_POST['note_type'],
        $_SESSION['user_id'] ?? null
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Note added successfully'
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error adding note: ' . $e->getMessage()
    ]);
}
?>