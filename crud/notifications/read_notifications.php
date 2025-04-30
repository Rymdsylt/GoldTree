<?php
session_start();
require_once '../../db/connection.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

try {
    // Get pagination parameters from POST data
    $data = json_decode(file_get_contents('php://input'), true);
    $page = isset($data['page']) ? (int)$data['page'] : 1;
    $limit = 10; // Items per page
    $offset = ($page - 1) * $limit;

    // Build the query with filters
    $where = [];
    $params = [];

    if (!empty($data['type'])) {
        $where[] = "n.notification_type = :type";
        $params[':type'] = $data['type'];
    }

    if (!empty($data['status'])) {
        $where[] = "nr.is_read = :status";
        $params[':status'] = ($data['status'] === 'read') ? 1 : 0;
    }

    if (!empty($data['fromDate'])) {
        $where[] = "n.created_at >= :fromDate";
        $params[':fromDate'] = $data['fromDate'] . ' 00:00:00';
    }

    if (!empty($data['toDate'])) {
        $where[] = "n.created_at <= :toDate";
        $params[':toDate'] = $data['toDate'] . ' 23:59:59';
    }

    $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

    // Get total count for pagination
    $countQuery = "SELECT COUNT(DISTINCT n.id) 
                  FROM notifications n 
                  LEFT JOIN notification_recipients nr ON n.id = nr.notification_id 
                  $whereClause";
    
    $stmt = $conn->prepare($countQuery);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    $total = $stmt->fetchColumn();
    
    // Main query with pagination
    $query = "SELECT 
                n.*,
                COUNT(DISTINCT nr.user_id) as recipient_count,
                COUNT(DISTINCT CASE WHEN nr.email_sent = 1 THEN nr.user_id END) as emails_sent
            FROM notifications n
            LEFT JOIN notification_recipients nr ON n.id = nr.notification_id
            $whereClause
            GROUP BY n.id
            ORDER BY n.created_at DESC
            LIMIT :limit OFFSET :offset";
    
    $stmt = $conn->prepare($query);
    
    // Bind all parameters
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    
    $stmt->execute();
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true, 
        'notifications' => $notifications,
        'currentPage' => $page,
        'totalPages' => ceil($total / $limit),
        'total' => $total,
        'showing' => count($notifications)
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>