<?php
require_once '../../db/connection.php';

function calculateAttendanceRate() {
    global $conn;
    
    try {
        $query = "SELECT 
                    COUNT(CASE WHEN attendance_status = 'present' THEN 1 END) as present_count,
                    COUNT(CASE WHEN attendance_status = 'absent' THEN 1 END) as absent_count
                 FROM event_attendance 
                 WHERE attendance_date >= DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY)";
                 
        $stmt = $conn->query($query);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $presentCount = $result['present_count'];
        $absentCount = $result['absent_count'];
        $totalCount = $presentCount + $absentCount;
        
        if ($totalCount > 0) {
            $attendanceRate = round(($presentCount / $totalCount) * 100);
            $absenceRate = round(($absentCount / $totalCount) * 100);
        } else {
            $attendanceRate = 0;
            $absenceRate = 0;
        }
        
        return [
            'success' => true,
            'attendance_rate' => $attendanceRate,
            'absence_rate' => $absenceRate
        ];
    } catch (PDOException $e) {
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

header('Content-Type: application/json');
echo json_encode(calculateAttendanceRate());
?>
