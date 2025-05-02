<?php
require_once '../../db/connection.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

try {
    $id = $_POST['id'];
    $title = $_POST['title'];
    $description = !empty($_POST['description']) ? $_POST['description'] : null;
    $start_datetime = $_POST['start_datetime'];
    $end_datetime = $_POST['end_datetime'];
    $event_type = $_POST['event_type'];
    $location = $_POST['location'];
    $max_attendees = !empty($_POST['max_attendees']) ? $_POST['max_attendees'] : null;

    $conn->beginTransaction();

    $updateFields = [
        'title' => $title,
        'description' => $description,
        'start_datetime' => $start_datetime,
        'end_datetime' => $end_datetime,
        'event_type' => $event_type,
        'location' => $location,
        'max_attendees' => $max_attendees
    ];

    if (isset($_FILES['event_image']) && $_FILES['event_image']['error'] === UPLOAD_ERR_OK) {
        $updateFields['image'] = file_get_contents($_FILES['event_image']['tmp_name']);
    }

    $sql = "UPDATE events SET ";
    $params = [];
    foreach ($updateFields as $field => $value) {
        if ($value !== null) {
            $sql .= "$field = ?, ";
            $params[] = $value;
        } else {
            $sql .= "$field = NULL, ";
        }
    }
    $sql = rtrim($sql, ', ') . " WHERE id = ?";
    $params[] = $id;

    $stmt = $conn->prepare($sql);
    $result = $stmt->execute($params);

    if (!$result) {
        throw new Exception('Failed to update event: ' . implode(', ', $stmt->errorInfo()));
    }

    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Event updated successfully'
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