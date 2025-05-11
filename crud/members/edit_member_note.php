<?php
require_once '../../db/connection.php';
session_start();

header('Content-Type: application/json');

if (!isset($_POST['id']) || !isset($_POST['note_text']) || !isset($_POST['note_type'])) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit;
}

try {
    $stmt = $conn->prepare("
        UPDATE member_notes 
        SET note_text = ?, note_type = ?
        WHERE id = ? AND (created_by = ? OR ? IS NULL)
    ");
    
    $stmt->execute([
        $_POST['note_text'],
        $_POST['note_type'],
        $_POST['id'],
        $_SESSION['user_id'] ?? null,
        $_SESSION['user_id'] ?? null
    ]);

    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Note updated successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No changes made or unauthorized to edit this note'
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error updating note: ' . $e->getMessage()
    ]);
}
?>
