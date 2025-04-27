<?php
require_once '../../db/connection.php';

$startDate = $_GET['start'] ?? date('Y-m-d', strtotime('-30 days'));
$endDate = $_GET['end'] ?? date('Y-m-d');

try {
    $stmt = $conn->prepare("
        SELECT 
            DATE(e.start_datetime) as event_date,
            COUNT(CASE WHEN ea.attendance_status IN ('present', 'late') THEN 1 END) * 100.0 / COUNT(*) as attendance_rate
        FROM events e
        LEFT JOIN event_attendance ea ON e.id = ea.event_id
        WHERE e.start_datetime BETWEEN ? AND ?
        GROUP BY DATE(e.start_datetime)
        ORDER BY event_date ASC
    ");
    $stmt->execute([$startDate, $endDate]);
    
    $labels = [];
    $values = [];
    
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $labels[] = date('M d', strtotime($row['event_date']));
        $values[] = round(floatval($row['attendance_rate']), 1);
    }
    
    echo json_encode([
        'labels' => $labels,
        'values' => $values
    ]);

} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}