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

    // Get event details first, exclude BLOB image field to reduce response size
    $eventStmt = $conn->prepare("SELECT id, title, description, start_datetime, end_datetime, location, event_type, status, max_attendees FROM events WHERE id = ?");
    $eventStmt->execute([$eventId]);
    $event = $eventStmt->fetch(PDO::FETCH_ASSOC);

    if (!$event) {
        throw new Exception('Event not found');
    }

    // Check if user exists in users table first
    $userStmt = $conn->prepare("SELECT id, member_id FROM users WHERE id = ?");
    $userStmt->execute([$_SESSION['user_id'] ?? 0]);
    $user = $userStmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        throw new Exception('User not logged in');
    }

    // If user has no member_id, show dates with no attendance
    if (!$user['member_id']) {
        // Generate all dates between start and end date
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

    // If we get here, user has a member_id, proceed with attendance check
    $memberStmt = $conn->prepare("SELECT id FROM members WHERE id = ?");
    $memberStmt->execute([$user['member_id']]);
    $memberId = $memberStmt->fetchColumn();

    // Get all attendance records for this event and member
    $attendanceStmt = $conn->prepare("
        SELECT 
            DATE(attendance_date) as date,
            attendance_status as status
        FROM event_attendance 
        WHERE event_id = ? AND member_id = ?
    ");
    $attendanceStmt->execute([$eventId, $memberId]);
    $attendanceRecords = $attendanceStmt->fetchAll(PDO::FETCH_ASSOC);

    // Convert to associative array for easier lookup
    $attendanceLookup = [];
    foreach ($attendanceRecords as $record) {
        $attendanceLookup[$record['date']] = $record['status'];
    }

    // Generate all dates between start and end date
    $startDate = new DateTime($event['start_datetime']);
    $endDate = new DateTime($event['end_datetime']);
    $interval = new DateInterval('P1D');
    $dateRange = new DatePeriod($startDate, $interval, $endDate->modify('+1 day'));

    // Build the attendance dates array
    $attendance_dates = [];
    foreach ($dateRange as $date) {
        $dateStr = $date->format('Y-m-d');
        $attendance_dates[] = [
            'date' => $dateStr,
            'status' => $attendanceLookup[$dateStr] ?? 
                       ($date > new DateTime('now') ? 'upcoming' : 'absent')
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