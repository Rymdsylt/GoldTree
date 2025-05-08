<?php
require_once '../../db/connection.php';
require_once '../../auth/login_status.php';
header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

    if (!$id) {
        throw new Exception('Invalid event ID');
    }

    $conn->beginTransaction();

    // Delete event assignments first (foreign key constraint)
    $stmt = $conn->prepare("DELETE FROM event_assignments WHERE event_id = ?");
    $stmt->execute([$id]);

    // Delete event attendance records
    $stmt = $conn->prepare("DELETE FROM event_attendance WHERE event_id = ?");
    $stmt->execute([$id]);

    // Delete the event
    $stmt = $conn->prepare("DELETE FROM events WHERE id = ?");
    $stmt->execute([$id]);

    if ($stmt->rowCount() === 0) {
        throw new Exception('Event not found');
    }

    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Event deleted successfully'
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