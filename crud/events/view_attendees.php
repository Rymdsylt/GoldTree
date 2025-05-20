<?php
require_once '../../db/connection.php';

if (!isset($_GET['event_id'])) {
    echo json_encode(['success' => false, 'message' => 'Event ID is required']);
    exit();
}

$event_id = $_GET['event_id'];

try {
  
    $eventStmt = $conn->prepare("SELECT start_datetime, end_datetime, status FROM events WHERE id = ?");
    $eventStmt->execute([$event_id]);
    $event = $eventStmt->fetch(PDO::FETCH_ASSOC);

    if (!$event) {
        echo json_encode(['success' => false, 'message' => 'Event not found']);
        exit();
    }


    $membersStmt = $conn->prepare("
        SELECT id, CONCAT(first_name, ' ', last_name) as full_name
        FROM members 
        WHERE status = 'active'
        ORDER BY first_name, last_name
    ");
    $membersStmt->execute();
    $allMembers = $membersStmt->fetchAll(PDO::FETCH_ASSOC);

    $start = new DateTime($event['start_datetime']);
    $end = new DateTime($event['end_datetime']);
    $interval = new DateInterval('P1D');
    $dateRange = new DatePeriod($start, $interval, $end->modify('+1 day'));
    $today = new DateTime();

    $attendance_data = [];

    foreach ($dateRange as $date) {
        $currentDate = $date->format('Y-m-d');
        $dateObj = new DateTime($currentDate);
        

        $attendees = [];
        $absentees = [];


        if ($dateObj <= $today) {

            $attendeesStmt = $conn->prepare("
                SELECT DISTINCT m.id, CONCAT(m.first_name, ' ', m.last_name) as full_name
                FROM members m
                JOIN event_attendance ea ON m.id = ea.member_id
                WHERE ea.event_id = ? 
                AND DATE(ea.attendance_date) = ?
                AND ea.attendance_status = 'present'
                ORDER BY m.first_name, m.last_name
            ");
            $attendeesStmt->execute([$event_id, $currentDate]);
            $presentMembers = $attendeesStmt->fetchAll(PDO::FETCH_ASSOC);

            $attendees = array_column($presentMembers, 'full_name');
            

            $presentMemberIds = array_column($presentMembers, 'id');
           
            $absentStmt = $conn->prepare("
                SELECT DISTINCT m.id, CONCAT(m.first_name, ' ', m.last_name) as full_name
                FROM members m
                JOIN event_attendance ea ON m.id = ea.member_id
                WHERE ea.event_id = ? 
                AND DATE(ea.attendance_date) = ?
                AND ea.attendance_status = 'absent'
                ORDER BY m.first_name, m.last_name
            ");
            $absentStmt->execute([$event_id, $currentDate]);
            $absentees = array_column($absentStmt->fetchAll(PDO::FETCH_ASSOC), 'full_name');
        }


        $attendance_data[] = [
            'date' => $currentDate,
            'attendees' => $attendees,
            'absentees' => $absentees
        ];
    }

    echo json_encode([
        'success' => true,
        'data' => $attendance_data
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching attendance data: ' . $e->getMessage()
    ]);
}