<?php
require_once '../../db/connection.php';

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Event ID is required']);
    exit;
}

$eventId = intval($_GET['id']);

// Check if database is PostgreSQL
$isPostgres = (getenv('DATABASE_URL') !== false);

try {
    // Build SQL with database-specific aggregate functions
    if ($isPostgres) {
        // PostgreSQL uses STRING_AGG instead of GROUP_CONCAT
        // COALESCE handles NULL case when no staff is assigned
        $sql = "
            SELECT e.*, 
                COALESCE(STRING_AGG(DISTINCT u.id::text, ', '), '') as assigned_staff_ids,
                COALESCE(STRING_AGG(DISTINCT m.first_name || ' ' || m.last_name, ', '), '') as assigned_staff_names
            FROM events e
            LEFT JOIN event_assignments ea ON e.id = ea.event_id
            LEFT JOIN users u ON ea.user_id = u.id
            LEFT JOIN members m ON u.member_id = m.id
            WHERE e.id = ?
            GROUP BY e.id
        ";
    } else {
        // MySQL uses GROUP_CONCAT
        $sql = "
            SELECT e.*, 
                GROUP_CONCAT(DISTINCT u.id) as assigned_staff_ids,
                GROUP_CONCAT(DISTINCT CONCAT(m.first_name, ' ', m.last_name)) as assigned_staff_names
            FROM events e
            LEFT JOIN event_assignments ea ON e.id = ea.event_id
            LEFT JOIN users u ON ea.user_id = u.id
            LEFT JOIN members m ON u.member_id = m.id
            WHERE e.id = ?
            GROUP BY e.id
        ";
    }
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([$eventId]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($event) {

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