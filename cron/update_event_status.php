<?php
require_once '../db/connection.php';

try {
    $now = date('Y-m-d H:i:s');
    
    // Mark events as ongoing if they've started but haven't ended
    $stmt = $conn->prepare("
        UPDATE events 
        SET status = 'ongoing' 
        WHERE start_datetime <= ? 
        AND end_datetime >= ? 
        AND status = 'upcoming'
    ");
    $stmt->execute([$now, $now]);
    
    // Mark events as completed if they've ended
    $stmt = $conn->prepare("
        UPDATE events 
        SET status = 'completed' 
        WHERE end_datetime < ? 
        AND status = 'ongoing'
    ");
    $stmt->execute([$now]);
    
    echo "Successfully updated event statuses at " . date('Y-m-d H:i:s');
} catch (Exception $e) {
    error_log("Error updating event statuses: " . $e->getMessage());
    echo "Error: " . $e->getMessage();
}