<?php
require_once '../db/connection.php';

try {
    $now = date('Y-m-d H:i:s');
    
    // Mark events as upcoming if they haven't started yet
    $stmt = $conn->prepare("
        UPDATE events 
        SET status = 'upcoming' 
        WHERE start_datetime > ? 
        AND status != 'cancelled'
        AND status != 'upcoming'
    ");
    $stmt->execute([$now]);
    
    // Mark events as ongoing if they've started but haven't ended
    $stmt = $conn->prepare("
        UPDATE events 
        SET status = 'ongoing' 
        WHERE start_datetime <= ? 
        AND end_datetime >= ? 
        AND status != 'cancelled'
        AND status != 'ongoing'
    ");
    $stmt->execute([$now, $now]);
    
    // Mark events as completed if they've ended
    $stmt = $conn->prepare("
        UPDATE events 
        SET status = 'completed' 
        WHERE end_datetime < ? 
        AND status != 'cancelled'
        AND status != 'completed'
    ");
    $stmt->execute([$now]);
    
    // Log the update
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