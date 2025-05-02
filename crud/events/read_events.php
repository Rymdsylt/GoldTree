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
            COUNT(*) as total,
            SUM(CASE WHEN status = 'upcoming' THEN 1 ELSE 0 END) as upcoming,
            SUM(CASE WHEN status = 'ongoing' THEN 1 ELSE 0 END) as ongoing,
            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed
            FROM events");
        $stmt->execute();
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'stats' => $stats]);
        exit;
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit;
    }
}

try {
    $whereClause = "WHERE 1=1";
    $params = [];
    
    if ($search) {
        $whereClause .= " AND (title LIKE ? OR description LIKE ? OR location LIKE ?)";
        $searchParam = "%{$search}%";
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
    }
    
    if ($status) {
        $whereClause .= " AND status = ?";
        $params[] = $status;
    }
    
    if ($date) {
        $whereClause .= " AND DATE(start_datetime) = ?";
        $params[] = $date;
    }

    $countQuery = "SELECT COUNT(*) as total FROM events " . $whereClause;
    $countStmt = $conn->prepare($countQuery);
    $countStmt->execute($params);
    $total = $countStmt->fetch()['total'];
    $totalPages = ceil($total / $limit);
    
    error_log("Total count: " . $total);
    
    $query = "SELECT e.*, 
              CASE WHEN ea.attendance_status IS NOT NULL THEN ea.attendance_status ELSE NULL END as attendance_status
              FROM events e 
              LEFT JOIN event_attendance ea ON e.id = ea.event_id 
              AND ea.member_id = (
                  SELECT m.id FROM members m 
                  INNER JOIN users u ON m.id = u.member_id 
                  WHERE u.id = :user_id
              )
              AND DATE(ea.attendance_date) = CURRENT_DATE
              $whereClause 
              ORDER BY e.start_datetime ASC LIMIT :limit OFFSET :offset";
    
    $stmt = $conn->prepare($query);
    
    // Add user_id to params
    $params[':user_id'] = $_SESSION['user_id'] ?? 0;

    // Bind all parameters
    foreach($params as $key => $value) {
        if ($key === ':user_id') {
            $stmt->bindValue($key, $value, PDO::PARAM_INT);
        } else {
            $stmt->bindValue($key, $value);
        }
    }
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
        'totalPages' => $totalPages
    ];
    
    error_log("Sending response: " . json_encode(['total' => $total, 'showing' => count($events)]));
    
    echo json_encode($response);
    
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>