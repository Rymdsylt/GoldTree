<?php
require_once '../../db/connection.php';
session_start();

header('Content-Type: application/json');

if (!isset($_POST['id'])) {
    echo json_encode(['success' => false, 'message' => 'Note ID is required']);
    exit;
}

try {
    $stmt = $conn->prepare("
        DELETE FROM member_notes 
        WHERE id = ? AND (created_by = ? OR ? IS NULL)
    ");
    
    $stmt->execute([
        $_POST['id'],
        $_SESSION['user_id'] ?? null,
        $_SESSION['user_id'] ?? null
    ]);

    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Note deleted successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Note not found or unauthorized to delete this note'
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error deleting note: ' . $e->getMessage()
    ]);
}
?>
