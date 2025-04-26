<?php
require_once '../../db/connection.php';
session_start();

header('Content-Type: application/json');


if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

try {

    $required_fields = ['title', 'start_datetime', 'end_datetime', 'event_type', 'location'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("$field is required");
        }
    }

    $title = $_POST['title'];
    $description = !empty($_POST['description']) ? $_POST['description'] : null;
    $start_datetime = $_POST['start_datetime'];
    $end_datetime = $_POST['end_datetime'];
    $event_type = $_POST['event_type'];
    $location = $_POST['location'];
    $max_attendees = !empty($_POST['max_attendees']) ? $_POST['max_attendees'] : null;
    $registration_deadline = !empty($_POST['registration_deadline']) ? $_POST['registration_deadline'] : null;
    $created_by = $_SESSION['user_id'];

    $image = null;
    if (isset($_FILES['event_image']) && $_FILES['event_image']['error'] === UPLOAD_ERR_OK) {
        $image = file_get_contents($_FILES['event_image']['tmp_name']);
    }

    $conn->beginTransaction();

    $stmt = $conn->prepare("INSERT INTO events (title, description, start_datetime, end_datetime, event_type, location, max_attendees, registration_deadline, image, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $result = $stmt->execute([
        $title,
        $description,
        $start_datetime,
        $end_datetime,
        $event_type,
        $location,
        $max_attendees,
        $registration_deadline,
        $image,
        $created_by
    ]);

    if (!$result) {
        throw new Exception('Failed to insert event: ' . implode(', ', $stmt->errorInfo()));
    }

    $event_id = $conn->lastInsertId();
    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Event created successfully',
        'event_id' => $event_id
    ]);

} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    error_log('Event creation error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error creating event: ' . $e->getMessage()
    ]);
}
?>