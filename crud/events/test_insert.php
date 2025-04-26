<?php
require_once '../../db/connection.php';

try {
    $stmt = $conn->prepare("INSERT INTO events (title, description, start_datetime, end_datetime, event_type, location, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->execute([
        'Test Event',
        'This is a test event description',
        '2025-05-01 10:00:00',
        '2025-05-01 12:00:00',
        'special',
        'Main Hall',
        'upcoming'
    ]);
    
    echo "Test event created successfully!";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>