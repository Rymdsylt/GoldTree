<?php
require_once '../../db/connection.php';
header('Content-Type: application/json');

$startDate = $_GET['start'] ?? date('Y-m-d', strtotime('-30 days'));
$endDate = $_GET['end'] ?? date('Y-m-d');

try {

    if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $startDate) || !preg_match("/^\d{4}-\d{2}-\d{2}$/", $endDate)) {
        throw new Exception('Invalid date format. Use YYYY-MM-DD');
    }

    
    $start = new DateTime($startDate);
    $end = new DateTime($endDate);
    $today = new DateTime();
    
    if ($start > $end) {
        throw new Exception('Start date cannot be after end date');
    }
    
    if ($end > $today) {
        throw new Exception('End date cannot be in the future');
    }

 
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM members WHERE status = 'active'");
    $stmt->execute();
    $activeMembers = $stmt->fetch(PDO::FETCH_ASSOC)['count'];


    $stmt = $conn->prepare("SELECT 
        COUNT(CASE WHEN attendance_status IN ('present', 'late') THEN 1 END) as present_count,
        COUNT(CASE WHEN attendance_status = 'absent' THEN 1 END) as absent_count,
        COUNT(CASE WHEN attendance_status != 'no_record' THEN 1 END) as total_count
        FROM event_attendance 
        WHERE attendance_date BETWEEN ? AND ?");
    $stmt->execute([$startDate, $endDate]);

    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $totalCount = (int)$result['total_count'];
    $presentCount = (int)$result['present_count'];
    $absentCount = (int)$result['absent_count'];
    
    $avgAttendance = $totalCount > 0 ? round(($presentCount / $totalCount) * 100) : 0;
    $avgAbsence = $totalCount > 0 ? round(($absentCount / $totalCount) * 100) : 0;

    echo json_encode([
        'success' => true,
        'activeMembers' => $activeMembers,
        'avgAttendance' => $avgAttendance,
        'avgAbsence' => $avgAbsence
    ]);

} catch(Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
