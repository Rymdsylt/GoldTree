<?php
require_once '../../db/connection.php';
header('Content-Type: application/json');

try {
    $id = $_GET['id'] ?? null;
    if (!$id) {
        throw new Exception('Member ID is required');
    }
    $stmt = $conn->prepare("
        SELECT m.*, 
            u.email as user_email,
            (SELECT COUNT(*) FROM event_attendance ea 
             WHERE ea.member_id = m.id AND ea.attendance_status = 'present') as total_attendances,
            (SELECT COUNT(*) FROM donations d 
             WHERE d.member_id = m.id) as total_donations,
            (SELECT SUM(amount) FROM donations d 
             WHERE d.member_id = m.id) as total_contribution
        FROM members m 
        LEFT JOIN users u ON m.user_id = u.id
        WHERE m.id = ?
    ");
    $stmt->execute([$id]);
    $member = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$member) {
        throw new Exception('Member not found');
    }
    

    if (!empty($member['profile_image'])) {
        $member['profile_image'] = base64_encode($member['profile_image']);
    }

    $attendanceStmt = $conn->prepare("
        SELECT e.title, e.start_datetime, ea.attendance_status
        FROM event_attendance ea
        JOIN events e ON ea.event_id = e.id
        WHERE ea.member_id = ?
        ORDER BY e.start_datetime DESC
        LIMIT 5
    ");
    $attendanceStmt->execute([$id]);
    $recentAttendance = $attendanceStmt->fetchAll(PDO::FETCH_ASSOC);


    $donationsStmt = $conn->prepare("
        SELECT amount, donation_type, donation_date
        FROM donations
        WHERE member_id = ?
        ORDER BY donation_date DESC
        LIMIT 5
    ");
    $donationsStmt->execute([$id]);
    $recentDonations = $donationsStmt->fetchAll(PDO::FETCH_ASSOC);


    $attendanceRateStmt = $conn->prepare("
        SELECT 
            ROUND(
                (SELECT COUNT(*) 
                 FROM event_attendance ea
                 JOIN events e ON ea.event_id = e.id
                 WHERE ea.member_id = ? 
                 AND ea.attendance_status = 'present'
                 AND e.start_datetime >= DATE_SUB(CURRENT_DATE, INTERVAL 3 MONTH)
                ) / 
                (SELECT COUNT(*) 
                 FROM events 
                 WHERE start_datetime >= DATE_SUB(CURRENT_DATE, INTERVAL 3 MONTH)
                ) * 100
            ) as attendance_rate
    ");
    $attendanceRateStmt->execute([$id]);
    $attendanceRate = $attendanceRateStmt->fetchColumn() ?? 0;


    echo json_encode([
        'success' => true,
        'data' => [
            'member' => $member,
            'recentAttendance' => $recentAttendance,
            'recentDonations' => $recentDonations,
            'attendanceRate' => $attendanceRate
        ]
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}