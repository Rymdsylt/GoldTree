<?php
session_start();
require_once '../../db/connection.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

try {
    // Get pagination parameters
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = 10; // Items per page
    $offset = ($page - 1) * $limit;

    // First get the notification IDs for the current user from notification_recipients
    $baseQuery = "
        SELECT 
            n.id,
            n.subject,
            n.message,
            n.notification_type,
            n.created_at,
            n.status,
            nr.is_read,
            nr.email_sent,
            nr.notification_id
        FROM notification_recipients nr
        INNER JOIN notifications n ON n.id = nr.notification_id
        WHERE nr.user_id = :user_id
    ";
    
    $countQuery = "
        SELECT COUNT(*) 
        FROM notification_recipients nr
        INNER JOIN notifications n ON n.id = nr.notification_id
        WHERE nr.user_id = :user_id
    ";

    $params = [':user_id' => $_SESSION['user_id']];
    $where = [];

    // Add search filter
    if (!empty($_GET['search'])) {
        $where[] = "(n.subject LIKE :search OR n.message LIKE :search)";
        $params[':search'] = "%{$_GET['search']}%";
    }

    // Add status filter
    if (!empty($_GET['status'])) {
        switch($_GET['status']) {
            case 'active':
                $where[] = "nr.is_read = 0";
                break;
            case 'expired':
                $where[] = "nr.is_read = 1";
                break;
        }
    }

    // Combine WHERE conditions
    if (!empty($where)) {
        $whereClause = " AND " . implode(" AND ", $where);
        $baseQuery .= $whereClause;
        $countQuery .= $whereClause;
    }

    // Add sorting - show newest notifications first
    $baseQuery .= " ORDER BY n.created_at DESC";

    // Add pagination
    $baseQuery .= " LIMIT :limit OFFSET :offset";

    // Debug - log the query and parameters
    error_log("Query: " . $baseQuery);
    error_log("Params: " . print_r($params, true));

    // Get total count
    $stmt = $conn->prepare($countQuery);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    $total = $stmt->fetchColumn();

    // Execute main query
    $stmt = $conn->prepare($baseQuery);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Debug - log the results
    error_log("Results: " . print_r($notifications, true));

    echo json_encode([
        'success' => true,
        'announcements' => $notifications,
        'currentPage' => $page,
        'totalPages' => ceil($total / $limit),
        'total' => $total,
        'showing' => count($notifications)
    ]);

} catch (Exception $e) {
    error_log("Error in read_announcements.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>