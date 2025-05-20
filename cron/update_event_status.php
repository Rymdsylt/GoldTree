<?php
require_once '../db/connection.php';

try {
    date_default_timezone_set('Asia/Manila'); 
    $now = date('Y-m-d H:i:s');
    

    $stmt = $conn->prepare("
        UPDATE events 
        SET status = 'upcoming' 
        WHERE start_datetime > ? 
        AND status != 'cancelled'
        AND status != 'upcoming'
    ");
    $stmt->execute([$now]);
    

    $stmt = $conn->prepare("
        UPDATE events 
        SET status = 'ongoing' 
        WHERE start_datetime <= ? 
        AND end_datetime >= ? 
        AND status != 'cancelled'
        AND status != 'ongoing'
    ");
    $stmt->execute([$now, $now]);
    

    $stmt = $conn->prepare("
        UPDATE events 
        SET status = 'completed' 
        WHERE end_datetime < ? 
        AND status != 'cancelled'
        AND status != 'completed'
    ");
    $stmt->execute([$now]);
    

    error_log("Event statuses updated successfully at " . $now);
    echo json_encode([
        'success' => true,
        'message' => "Successfully updated event statuses at " . $now,
        'timestamp' => $now
    ]);
} catch (Exception $e) {
    error_log("Error updating event statuses: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => "Error: " . $e->getMessage(),
        'timestamp' => $now
    ]);
}