<?php
require_once '../../db/connection.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');


$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$search = isset($_GET['search']) ? $_GET['search'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';
$date = isset($_GET['date']) ? $_GET['date'] : '';
$limit = 9; 
$offset = ($page - 1) * $limit;


error_log("Received parameters: " . json_encode([
    'page' => $page,
    'search' => $search,
    'status' => $status,
    'date' => $date
]));


if (isset($_GET['stats']) && $_GET['stats'] === 'true') {
    try {
        $now = date('Y-m-d H:i:s');
        $stmt = $conn->prepare("SELECT 
            SUM(CASE 
                WHEN start_datetime > ? AND status = 'upcoming' THEN 1 
                ELSE 0 
            END) as upcoming,
            SUM(CASE 
                WHEN start_datetime <= ? AND end_datetime >= ? AND status = 'ongoing' THEN 1 
                ELSE 0 
            END) as ongoing,
            COUNT(*) as total
            FROM events 
            WHERE status != 'cancelled'");
        $stmt->execute([$now, $now, $now]);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        error_log("Stats query result: " . json_encode($stats));
        
        echo json_encode([
            'success' => true,
            'upcoming' => (int)$stats['upcoming'],
            'ongoing' => (int)$stats['ongoing'],
            'total' => (int)$stats['total']
        ]);
        exit;
    } catch (PDOException $e) {
        error_log("Database error in stats: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        exit;
    }
}

try {

    $where = [];
    $params = [];
    
    if ($search) {
        $where[] = "(title LIKE :search1 OR description LIKE :search2 OR location LIKE :search3)";
        $params[':search1'] = "%$search%";
        $params[':search2'] = "%$search%";
        $params[':search3'] = "%$search%";
    }
    
    if ($status) {
        $where[] = "status = :status";
        $params[':status'] = $status;
    }
    
    if ($date) {
        $where[] = "DATE(start_datetime) = :date";
        $params[':date'] = $date;
    }
    
    $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

    error_log("Constructed WHERE clause: " . $whereClause);
    error_log("Query parameters: " . json_encode($params));
 
    $countQuery = "SELECT COUNT(*) FROM events $whereClause";
    $stmt = $conn->prepare($countQuery);
    $stmt->execute($params);
    $total = $stmt->fetchColumn();
    
    error_log("Total count: " . $total);
    
    $query = "SELECT * FROM events $whereClause ORDER BY start_datetime ASC LIMIT :limit OFFSET :offset";
    
    $stmt = $conn->prepare($query);

    // Bind all the search/filter parameters first
    foreach($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }

    // Then bind the pagination parameters
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
    
    $stmt->execute();
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    error_log("Number of events fetched: " . count($events));
  
    $now = new DateTime();
    foreach ($events as &$event) {
        if ($event['image']) {
            $event['image'] = base64_encode($event['image']);
        }
        
        $start = new DateTime($event['start_datetime']);
        $end = new DateTime($event['end_datetime']);
        

        if ($event['status'] === 'upcoming' && $now >= $start) {
            $updateStmt = $conn->prepare("UPDATE events SET status = 'ongoing' WHERE id = ?");
            $updateStmt->execute([$event['id']]);
            $event['status'] = 'ongoing';
        } elseif ($event['status'] === 'ongoing' && $now > $end) {
            $updateStmt = $conn->prepare("UPDATE events SET status = 'completed' WHERE id = ?");
            $updateStmt->execute([$event['id']]);
            $event['status'] = 'completed';
        }
    }
    
    $response = [
        'success' => true,
        'events' => $events,
        'total' => $total,
        'showing' => count($events),
        'currentPage' => $page,
        'totalPages' => ceil($total / $limit)
    ];
    
    error_log("Sending response: " . json_encode(['total' => $total, 'showing' => count($events)]));
    
    echo json_encode($response);
    
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>