<?php
require_once '../../db/connection.php';
header('Content-Type: application/json');

$startDate = $_GET['start'] ?? date('Y-m-d', strtotime('-30 days'));
$endDate = $_GET['end'] ?? date('Y-m-d');

try {
    // Validate date format
    if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $startDate) || !preg_match("/^\d{4}-\d{2}-\d{2}$/", $endDate)) {
        throw new Exception('Invalid date format. Use YYYY-MM-DD');
    }

    // Validate date range
    $start = new DateTime($startDate);
    $end = new DateTime($endDate);
    $today = new DateTime();
    
    if ($start > $end) {
        throw new Exception('Start date cannot be after end date');
    }
    
    if ($end > $today) {
        throw new Exception('End date cannot be in the future');
    }

    // Get daily attendance counts within the date range
    $stmt = $conn->prepare("
        SELECT 
            DATE(attendance_date) as attendance_date,
            SUM(CASE WHEN attendance_status = 'present' THEN 1 ELSE 0 END) as present_count,
            SUM(CASE WHEN attendance_status = 'absent' THEN 1 ELSE 0 END) as absent_count
        FROM event_attendance
        WHERE DATE(attendance_date) BETWEEN ? AND ?
        GROUP BY DATE(attendance_date)
        ORDER BY attendance_date ASC
    ");
    
    $stmt->execute([$startDate, $endDate]);
    
    $labels = [];
    $presentValues = [];
    $absentValues = [];
    
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $labels[] = date('M d', strtotime($row['attendance_date']));
        $presentValues[] = (int)$row['present_count'];
        $absentValues[] = (int)$row['absent_count'];
    }
    
    echo json_encode([
        'success' => true,
        'labels' => $labels,
        'present' => $presentValues,
        'absent' => $absentValues
    ]);

} catch(Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}