<?php
require_once '../../db/connection.php';

$type = $_GET['type'] ?? 'complete';
$start_date = $_GET['start'] ?? date('Y-m-d', strtotime('-1 month'));
$end_date = $_GET['end'] ?? date('Y-m-d');

try {    switch($type) {
        case 'members':
            exportMembers($conn);
            break;
        case 'events':
            exportEvents($conn, $start_date, $end_date);
            break;
        case 'complete':
            exportComplete($conn, $start_date, $end_date);
            break;
    }
} catch(Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => $e->getMessage()]);
}

function outputCSV($filename, $headers, $data) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $output = fopen('php://output', 'w');
    

    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    

    fputcsv($output, $headers);
    

    foreach ($data as $row) {
        fputcsv($output, $row);
    }
    
    fclose($output);
    exit();
}



function exportMembers($conn) {
    $headers = ['Name', 'Email', 'Phone', 'Status', 'Category', 'Join Date'];
    
    $stmt = $conn->prepare("
        SELECT 
            CONCAT(first_name, ' ', last_name) as name,
            email,
            phone,
            status,
            category,
            membership_date
        FROM members
        ORDER BY membership_date DESC
    ");
    $stmt->execute();
    
    $data = [];
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $data[] = [
            $row['name'],
            $row['email'],
            $row['phone'],
            ucfirst($row['status']),
            $row['category'],
            $row['membership_date']
        ];
    }
    

    $stmt = $conn->prepare("
        SELECT 
            COUNT(*) as total,
            COUNT(CASE WHEN status = 'active' THEN 1 END) as active,
            COUNT(CASE WHEN status = 'inactive' THEN 1 END) as inactive
        FROM members
    ");
    $stmt->execute();
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $data[] = [''];  
    $data[] = ['Summary Statistics'];
    $data[] = ['Total Members:', $stats['total']];
    $data[] = ['Active Members:', $stats['active']];
    $data[] = ['Inactive Members:', $stats['inactive']];
    
    outputCSV('members_report.csv', $headers, $data);
}

function exportEvents($conn, $start_date, $end_date) {
    $headers = ['Event', 'Date', 'Type', 'Location', 'Attendees', 'Status'];
    
    $stmt = $conn->prepare("
        SELECT 
            e.title,
            e.start_datetime,
            e.event_type,
            e.location,
            e.status,
            COUNT(ea.id) as attendees
        FROM events e
        LEFT JOIN event_attendance ea ON e.id = ea.event_id AND ea.attendance_status = 'present'
        WHERE e.start_datetime BETWEEN ? AND ?
        GROUP BY e.id
        ORDER BY e.start_datetime DESC
    ");
    $stmt->execute([$start_date, $end_date]);
    
    $data = [];
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $data[] = [
            $row['title'],
            $row['start_datetime'],
            ucfirst($row['event_type']),
            $row['location'],
            $row['attendees'],
            ucfirst($row['status'])
        ];
    }
    

    $stmt = $conn->prepare("
        SELECT 
            COUNT(*) as total_events,
            AVG(
                (SELECT COUNT(*) 
                FROM event_attendance ea 
                WHERE ea.event_id = e.id AND ea.attendance_status = 'present')
            ) as avg_attendance
        FROM events e
        WHERE start_datetime BETWEEN ? AND ?
    ");
    $stmt->execute([$start_date, $end_date]);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $data[] = [''];  
    $data[] = ['Event Statistics'];
    $data[] = ['Total Events:', $stats['total_events']];
    $data[] = ['Average Attendance:', round($stats['avg_attendance'], 1)];
    
    outputCSV('events_report.csv', $headers, $data);
}

function exportComplete($conn, $start_date, $end_date) {
  
    $data = [];
    
    $data[] = ['MEMBERS REPORT'];
    $data[] = ['Name', 'Email', 'Phone', 'Status', 'Category', 'Join Date'];
    
    $stmt = $conn->prepare("
        SELECT 
            CONCAT(first_name, ' ', last_name) as name,
            email,
            phone,
            status,
            category,
            membership_date
        FROM members
        ORDER BY membership_date DESC
    ");
    $stmt->execute();
    
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $data[] = [
            $row['name'],
            $row['email'],
            $row['phone'],
            ucfirst($row['status']),
            $row['category'],
            $row['membership_date']
        ];
    }
    
    $data[] = [''];  
    

    $data[] = ['EVENTS REPORT'];
    $data[] = ['Event', 'Date', 'Type', 'Location', 'Attendees', 'Status'];
    
    $stmt = $conn->prepare("
        SELECT 
            e.title,
            e.start_datetime,
            e.event_type,
            e.location,
            e.status,
            COUNT(ea.id) as attendees
        FROM events e
        LEFT JOIN event_attendance ea ON e.id = ea.event_id AND ea.attendance_status = 'present'
        WHERE e.start_datetime BETWEEN ? AND ?
        GROUP BY e.id
        ORDER BY e.start_datetime DESC
    ");
    $stmt->execute([$start_date, $end_date]);
    
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $data[] = [
            $row['title'],
            $row['start_datetime'],
            ucfirst($row['event_type']),
            $row['location'],
            $row['attendees'],
            ucfirst($row['status'])
        ];
    }
    
    outputCSV('complete_report.csv', ['Complete Report'], $data);
}