<?php
require_once '../../db/connection.php';

// Check if database is PostgreSQL
$isPostgres = (getenv('DATABASE_URL') !== false);

try {
    // Use database-specific date functions
    if ($isPostgres) {
        $query = "SELECT 
            COUNT(CASE WHEN attendance_status = 'present' THEN 1 END) as present_count,
            COUNT(CASE WHEN attendance_status = 'absent' THEN 1 END) as absent_count
        FROM event_attendance 
        WHERE attendance_date >= CURRENT_DATE - INTERVAL '30 days'";
    } else {
        $query = "SELECT 
            COUNT(CASE WHEN attendance_status = 'present' THEN 1 END) as present_count,
            COUNT(CASE WHEN attendance_status = 'absent' THEN 1 END) as absent_count
        FROM event_attendance 
        WHERE attendance_date >= DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY)";
    }
    
    $stmt = $conn->query($query);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $presentCount = (int)$result['present_count'];
    $absentCount = (int)$result['absent_count'];
    $totalCount = $presentCount + $absentCount;
    
    if ($totalCount > 0) {
        $attendanceRate = round(($presentCount / $totalCount) * 100);
        $absenceRate = round(($absentCount / $totalCount) * 100);
    } else {
        $attendanceRate = 0;
        $absenceRate = 0;
    }
    
    echo json_encode([
        'success' => true,
        'attendance_rate' => $attendanceRate,
        'absence_rate' => $absenceRate
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
