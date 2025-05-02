<?php
require_once '../../db/connection.php';

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Event ID is required']);
    exit;
}

$eventId = intval($_GET['id']);

try {
    $stmt = $conn->prepare("
        SELECT id, title, description, start_datetime, end_datetime, 
               event_type, location, max_attendees, 
               image, status, created_at
        FROM events 
        WHERE id = ?
    ");
    
    $stmt->execute([$eventId]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($event) {
        // Convert image BLOB to base64 if it exists
        if ($event['image']) {
            $event['image'] = base64_encode($event['image']);
        }
        
        $event['success'] = true;
        echo json_encode($event);
    } else {
        echo json_encode(['success' => false, 'message' => 'Event not found']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>