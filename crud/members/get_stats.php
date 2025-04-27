<?php
require_once '../../db/connection.php';

header('Content-Type: application/json');

try {

    $totalQuery = "SELECT COUNT(*) as total FROM members";
    $totalStmt = $conn->prepare($totalQuery);
    $totalStmt->execute();
    $total = $totalStmt->fetch(PDO::FETCH_ASSOC)['total'];


    $activeQuery = "SELECT COUNT(*) as active FROM members WHERE status = 'active'";
    $activeStmt = $conn->prepare($activeQuery);
    $activeStmt->execute();
    $active = $activeStmt->fetch(PDO::FETCH_ASSOC)['active'];


    $newQuery = "SELECT COUNT(*) as new FROM members WHERE MONTH(membership_date) = MONTH(CURRENT_DATE()) AND YEAR(membership_date) = YEAR(CURRENT_DATE())";
    $newStmt = $conn->prepare($newQuery);
    $newStmt->execute();
    $new = $newStmt->fetch(PDO::FETCH_ASSOC)['new'];


    $attendanceQuery = "
        WITH EventCount AS (
            SELECT COUNT(*) as total_events
            FROM events
            WHERE start_datetime >= DATE_SUB(NOW(), INTERVAL 3 MONTH)
        ),
        MemberAttendance AS (
            SELECT m.id,
                   COUNT(ea.event_id) as attended_events
            FROM members m
            LEFT JOIN event_attendance ea ON m.id = ea.member_id
            LEFT JOIN events e ON ea.event_id = e.id
            WHERE e.start_datetime >= DATE_SUB(NOW(), INTERVAL 3 MONTH)
            AND ea.attendance_status = 'present'
            GROUP BY m.id
        )
        SELECT ROUND(
            (COUNT(CASE WHEN ma.attended_events >= (ec.total_events * 0.75) THEN 1 END) * 100.0) / 
            COUNT(m.id)
        ) as regular_attendance
        FROM members m
        CROSS JOIN EventCount ec
        LEFT JOIN MemberAttendance ma ON m.id = ma.id
        WHERE m.status = 'active'";
    
    $attendanceStmt = $conn->prepare($attendanceQuery);
    $attendanceStmt->execute();
    $regularAttendance = $attendanceStmt->fetch(PDO::FETCH_ASSOC)['regular_attendance'];

    echo json_encode([
        'success' => true,
        'total' => $total,
        'active' => $active,
        'new' => $new,
        'regularAttendance' => $regularAttendance ?? 0
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}