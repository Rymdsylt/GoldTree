<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../db/connection.php';
require_once '../../auth/login_status.php';

header('Content-Type: application/json');

try {
    if (!isset($_GET['event_id'])) {
        throw new Exception('Event ID is required');
    }

    $eventId = filter_input(INPUT_GET, 'event_id', FILTER_VALIDATE_INT);
    if (!$eventId) {
        throw new Exception('Invalid event ID');
    }

 
    $eventStmt = $conn->prepare("SELECT id, title, description, start_datetime, end_datetime, location, event_type, status, max_attendees FROM events WHERE id = ?");
    $eventStmt->execute([$eventId]);
    $event = $eventStmt->fetch(PDO::FETCH_ASSOC);

    if (!$event) {
        throw new Exception('Event not found');
    }


    $userStmt = $conn->prepare("SELECT id, member_id FROM users WHERE id = ?");
    $userStmt->execute([$_SESSION['user_id'] ?? 0]);
    $user = $userStmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        throw new Exception('User not logged in');
    }

    if (!$user['member_id']) {

        $startDate = new DateTime($event['start_datetime']);
        $endDate = new DateTime($event['end_datetime']);
        $interval = new DateInterval('P1D');
        $dateRange = new DatePeriod($startDate, $interval, $endDate->modify('+1 day'));

        $attendance_dates = [];
        foreach ($dateRange as $date) {
            $attendance_dates[] = [
                'date' => $date->format('Y-m-d'),
                'status' => $date > new DateTime('now') ? 'upcoming' : 'not_member'
            ];
        }

        echo json_encode([
            'success' => true,
            'event' => $event,
            'attendance_dates' => $attendance_dates,
            'message' => 'Please complete your membership registration to mark attendance'
        ]);
        exit;
    }

    $memberStmt = $conn->prepare("SELECT id FROM members WHERE id = ?");
    $memberStmt->execute([$user['member_id']]);
    $memberId = $memberStmt->fetchColumn();


    $attendanceStmt = $conn->prepare("
        SELECT 
            DATE(attendance_date) as date,
            attendance_status as status
        FROM event_attendance 
        WHERE event_id = ? AND member_id = ?
    ");
    $attendanceStmt->execute([$eventId, $memberId]);
    $attendanceRecords = $attendanceStmt->fetchAll(PDO::FETCH_ASSOC);


    $attendanceLookup = [];
    foreach ($attendanceRecords as $record) {
        $attendanceLookup[$record['date']] = $record['status'];
    }


    $startDate = new DateTime($event['start_datetime']);
    $endDate = new DateTime($event['end_datetime']);
    $interval = new DateInterval('P1D');
    $dateRange = new DatePeriod($startDate, $interval, $endDate->modify('+1 day'));


    $attendance_dates = [];
    foreach ($dateRange as $date) {
        $dateStr = $date->format('Y-m-d');
        $attendance_dates[] = [
            'date' => $dateStr,            'status' => $attendanceLookup[$dateStr] ?? 
                       ($date > new DateTime('now') ? 'upcoming' : 'no_record')
        ];
    }

    echo json_encode([
        'success' => true,
        'event' => $event,
        'attendance_dates' => $attendance_dates
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    exit;
}