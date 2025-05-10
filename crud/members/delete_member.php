<?php
require_once '../../db/connection.php';
header('Content-Type: application/json');

try {
    $id = $_POST['id'] ?? null;
    if (!$id) {
        throw new Exception('Member ID is required');
    }


    $checkStmt = $conn->prepare("SELECT id FROM members WHERE id = ?");
    $checkStmt->execute([$id]);
    if (!$checkStmt->fetch()) {
        throw new Exception('Member not found');
    }

    $conn->beginTransaction();

    $conn->prepare("UPDATE users SET member_id = NULL WHERE member_id = ?")->execute([$id]);


    $conn->prepare("DELETE FROM event_attendance WHERE member_id = ?")->execute([$id]);
    $conn->prepare("UPDATE donations SET member_id = NULL WHERE member_id = ?")->execute([$id]);
  
    $stmt = $conn->prepare("DELETE FROM members WHERE id = ?");
    $stmt->execute([$id]);

    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Member deleted successfully'
    ]);

} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}