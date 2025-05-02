<?php
require_once '../../db/connection.php';
header('Content-Type: application/json');

$startDate = $_GET['start'] ?? date('Y-m-d', strtotime('-30 days'));
$endDate = $_GET['end'] ?? date('Y-m-d');

try {

    $stmt = $conn->prepare("SELECT COALESCE(SUM(amount), 0) as total FROM donations 
                           WHERE donation_date BETWEEN ? AND ?");
    $stmt->execute([$startDate, $endDate]);
    $totalDonations = $stmt->fetch(PDO::FETCH_ASSOC)['total'];


    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM members WHERE status = 'active'");
    $stmt->execute();
    $activeMembers = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    $stmt = $conn->prepare("SELECT 
        (COUNT(CASE WHEN attendance_status IN ('present', 'late') THEN 1 END) * 100.0 / COUNT(*)) as avg_rate
        FROM event_attendance 
        INNER JOIN events ON event_attendance.event_id = events.id
        WHERE events.start_datetime BETWEEN ? AND ?");
    $stmt->execute([$startDate, $endDate]);
    $avgAttendance = round($stmt->fetch(PDO::FETCH_ASSOC)['avg_rate'] ?? 0, 1);

    echo json_encode([
        'totalDonations' => $totalDonations,
        'activeMembers' => $activeMembers,
        'avgAttendance' => $avgAttendance
    ]);

} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}