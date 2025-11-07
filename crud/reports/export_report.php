<?php
require_once '../../db/connection.php';

$type = $_GET['type'] ?? 'complete';
$start_date = $_GET['start'] ?? date('Y-m-d', strtotime('-1 month'));
$end_date = $_GET['end'] ?? date('Y-m-d');

try {
    switch($type) {
        case 'members':
            exportMembers($conn);
            break;
        case 'events':
            exportEvents($conn, $start_date, $end_date);
            break;
        case 'sacramental':
            exportSacramental($conn, $start_date, $end_date);
            break;
        case 'complete':
            exportComplete($conn);
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
    
    // Check if database is PostgreSQL
    $isPostgres = (getenv('DATABASE_URL') !== false);
    
    // Use database-specific string concatenation
    if ($isPostgres) {
        $stmt = $conn->prepare("
            SELECT 
                first_name || ' ' || last_name as name,
                email,
                phone,
                status,
                category,
                membership_date
            FROM members
            ORDER BY membership_date DESC
        ");
    } else {
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
    }
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

function exportSacramental($conn, $start_date, $end_date) {
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="sacramental_records_' . date('Y-m-d') . '.xls"');
    header('Cache-Control: max-age=0');

    $baptismQuery = "SELECT 
        name, parent1_name, parent1_origin, parent2_name, parent2_origin,
        address, birth_date, birth_place, gender, baptism_date, minister,
        DATE_FORMAT(created_at, '%Y-%m-%d') as record_date
        FROM baptismal_records 
        ORDER BY baptism_date DESC";

    $baptismSponsorsQuery = "SELECT br.name as baptized_name, 
        bs.sponsor_name, DATE_FORMAT(br.baptism_date, '%Y-%m-%d') as baptism_date
        FROM baptismal_records br
        JOIN baptismal_sponsors bs ON br.id = bs.baptismal_record_id
        ORDER BY br.baptism_date DESC";

    $confirmationQuery = "SELECT 
        name, parent1_name, parent1_origin, parent2_name, parent2_origin,
        address, birth_date, birth_place, gender, baptism_date, minister,
        DATE_FORMAT(created_at, '%Y-%m-%d') as record_date
        FROM confirmation_records 
        ORDER BY baptism_date DESC";

    $confirmationSponsorsQuery = "SELECT cr.name as confirmed_name, 
        cs.sponsor_name, DATE_FORMAT(cr.baptism_date, '%Y-%m-%d') as confirmation_date
        FROM confirmation_records cr
        JOIN confirmation_sponsors cs ON cr.id = cs.confirmation_record_id
        ORDER BY cr.baptism_date DESC";

    $communionQuery = "SELECT 
        name, parent1_name, parent1_origin, parent2_name, parent2_origin,
        address, birth_date, birth_place, gender, baptism_date, baptism_church, 
        church, communion_date, confirmation_date, minister,
        DATE_FORMAT(created_at, '%Y-%m-%d') as record_date
        FROM first_communion_records 
        ORDER BY communion_date DESC";

    $matrimonyQuery = "SELECT 
        m.matrimony_date, m.church, m.minister,
        mc.type, mc.name, mc.parent1_name, mc.parent1_origin,
        mc.parent2_name, mc.parent2_origin, mc.birth_date, mc.birth_place, 
        mc.gender, mc.baptism_date, mc.baptism_church, mc.confirmation_date,
        mc.confirmation_church, DATE_FORMAT(m.created_at, '%Y-%m-%d') as record_date
        FROM matrimony_records m
        JOIN matrimony_couples mc ON m.id = mc.matrimony_record_id 
        ORDER BY m.matrimony_date DESC";
        
    $matrimonySponsorsQuery = "SELECT 
        CONCAT(
            MAX(CASE WHEN mc.type = 'groom' THEN mc.name END),
            ' & ',
            MAX(CASE WHEN mc.type = 'bride' THEN mc.name END)
        ) as couple_names,
        ms.sponsor_name,
        DATE_FORMAT(m.matrimony_date, '%Y-%m-%d') as marriage_date
        FROM matrimony_records m
        JOIN matrimony_couples mc ON m.id = mc.matrimony_record_id
        JOIN matrimony_sponsors ms ON m.id = ms.matrimony_record_id
        GROUP BY m.id, ms.sponsor_name, m.matrimony_date
        ORDER BY m.matrimony_date DESC";

    $queries = [
        'BAPTISM RECORDS' => ['query' => $baptismQuery],
        'BAPTISMAL SPONSORS' => ['query' => $baptismSponsorsQuery],
        'CONFIRMATION RECORDS' => ['query' => $confirmationQuery],
        'CONFIRMATION SPONSORS' => ['query' => $confirmationSponsorsQuery],
        'FIRST COMMUNION RECORDS' => ['query' => $communionQuery],
        'MATRIMONY RECORDS' => ['query' => $matrimonyQuery],
        'MATRIMONY SPONSORS' => ['query' => $matrimonySponsorsQuery]
    ];

    echo "COMPLETE SACRAMENTAL RECORDS REPORT\n";
    echo "Generated on: " . date('F j, Y') . "\n\n";

    foreach ($queries as $title => $data) {
        echo "$title\n";
        
        $stmt = $conn->query($data['query']);
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($records)) {
            echo implode("\t", array_keys($records[0])) . "\n";
            
            foreach ($records as $record) {
                echo implode("\t", array_map(function($value) {
                    return str_replace(["\r", "\n", "\t"], ' ', $value ?? '');
                }, $record)) . "\n";
            }
        } else {
            echo "No records found for this period\n";
        }
        echo "\n\n";
    }
}

function exportComplete($conn) {
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="complete_parish_records_' . date('Y-m-d_His') . '.xls"');
    header('Cache-Control: max-age=0');
    
    // Get all tables except users
    $tables_query = "SHOW TABLES FROM " . DB_NAME . " WHERE Tables_in_" . DB_NAME . " != 'users'";
    $tables_stmt = $conn->query($tables_query);
    $tables = $tables_stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "COMPLETE PARISH DATABASE EXPORT\n";
    echo "Generated on: " . date('F j, Y, g:i a') . "\n\n";
    
    foreach ($tables as $table) {
        // Get table structure
        $cols_query = "SHOW COLUMNS FROM `$table`";
        $cols_stmt = $conn->query($cols_query);
        $columns = $cols_stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo strtoupper(str_replace('_', ' ', $table)) . "\n";
        
        // Print column headers
        $headers = array_column($columns, 'Field');
        echo implode("\t", $headers) . "\n";
        
        // Get data
        $data_query = "SELECT * FROM `$table`";
        
        // Add specific ordering for different tables
        switch ($table) {
            case 'members':
                $data_query .= " ORDER BY membership_date DESC, last_name ASC";
                break;
            case 'events':
                $data_query .= " ORDER BY start_datetime DESC";
                break;
            case 'baptismal_records':
            case 'confirmation_records':
                $data_query .= " ORDER BY baptism_date DESC";
                break;
            case 'first_communion_records':
                $data_query .= " ORDER BY communion_date DESC";
                break;
            case 'matrimony_records':
                $data_query .= " ORDER BY matrimony_date DESC";
                break;
            case 'donations':
                $data_query .= " ORDER BY donation_date DESC";
                break;
            default:
                $data_query .= " ORDER BY id DESC";
        }
        
        $data_stmt = $conn->query($data_query);
        
        // Print data rows
        while ($row = $data_stmt->fetch(PDO::FETCH_ASSOC)) {
            echo implode("\t", array_map(function($value) {
                return str_replace(["\r", "\n", "\t"], ' ', $value ?? '');
            }, $row)) . "\n";
        }
        
        // Add extra newlines between tables
        echo "\n\n";
    }
    
    // Add summary statistics
    echo "SUMMARY STATISTICS\n";
    
    // Members statistics
    $stmt = $conn->query("
        SELECT 
            COUNT(*) as total,
            COUNT(CASE WHEN status = 'active' THEN 1 END) as active,
            COUNT(CASE WHEN status = 'inactive' THEN 1 END) as inactive
        FROM members
    ");
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Total Members:\t{$stats['total']}\n";
    echo "Active Members:\t{$stats['active']}\n";
    echo "Inactive Members:\t{$stats['inactive']}\n\n";
    
    // Sacramental records statistics
    foreach (['baptismal_records', 'confirmation_records', 'first_communion_records', 'matrimony_records'] as $table) {
        $stmt = $conn->query("SELECT COUNT(*) as count FROM $table");
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        echo "Total " . ucwords(str_replace('_', ' ', $table)) . ":\t$count\n";
    }
    
    exit();
}