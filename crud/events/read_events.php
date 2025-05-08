<?php
require_once '../../db/connection.php';
require_once '../../auth/login_status.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    exit();
}

// Get current date and time for status comparison
$now = date('Y-m-d H:i:s');

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 12;
$offset = ($page - 1) * $perPage;

$search = isset($_GET['search']) ? $_GET['search'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';
$date = isset($_GET['date']) ? $_GET['date'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'created_at';
$direction = isset($_GET['direction']) ? $_GET['direction'] : 'DESC';

// Validate sort column to prevent SQL injection
$allowedSortColumns = ['title', 'start_datetime', 'end_datetime', 'event_type', 'location', 'status', 'created_at'];
if (!in_array($sort, $allowedSortColumns)) {
    $sort = 'created_at';
}

// Validate sort direction
$direction = strtoupper($direction) === 'ASC' ? 'ASC' : 'DESC';

$whereConditions = [];
$params = [];

// Check if user is admin
$adminCheck = $conn->prepare("SELECT admin_status FROM users WHERE id = ?");
$adminCheck->execute([$_SESSION['user_id']]);
$isAdmin = $adminCheck->fetch()['admin_status'] == 1;

// If not admin, only show assigned events
if (!$isAdmin) {
    $whereConditions[] = "EXISTS (
        SELECT 1 FROM event_assignments 
        WHERE event_assignments.event_id = e.id 
        AND event_assignments.user_id = ?
    )";
    $params[] = $_SESSION['user_id'];
}

if ($search) {
    $whereConditions[] = "(e.title LIKE ? OR e.description LIKE ? OR e.location LIKE ?)";
    $searchTerm = "%$search%";
    $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
}

if ($status) {
    $whereConditions[] = "e.status = ?";
    $params[] = $status;
} else {
    // If no status filter, exclude cancelled events by default
    $whereConditions[] = "e.status != 'cancelled'";
}

if ($date) {
    $whereConditions[] = "DATE(e.start_datetime) = ?";
    $params[] = $date;
}

// Update event status based on current time
$updateStatusSQL = "
    UPDATE events SET status = 
    CASE 
        WHEN start_datetime > ? THEN 'upcoming'
        WHEN start_datetime <= ? AND end_datetime >= ? THEN 'ongoing'
        WHEN end_datetime < ? THEN 'completed'
        ELSE status 
    END
    WHERE status != 'cancelled'";

$conn->prepare($updateStatusSQL)->execute([$now, $now, $now, $now]);

$whereClause = $whereConditions ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

// Get total count for pagination
$countSql = "SELECT COUNT(*) as total FROM events e $whereClause";
$countStmt = $conn->prepare($countSql);
$countStmt->execute($params);
$total = $countStmt->fetch()['total'];

$totalPages = ceil($total / $perPage);
$currentPage = max(1, min($page, $totalPages));

// Base query for events with attendance percentage calculation
$sql = "
    SELECT 
        e.*,
        GROUP_CONCAT(DISTINCT CONCAT(m.first_name, ' ', m.last_name)) as assigned_staff,
        GROUP_CONCAT(DISTINCT u.id) as assigned_staff_ids,
        GROUP_CONCAT(DISTINCT CONCAT(m.first_name, ' ', m.last_name)) as assigned_staff_names,
        (
            SELECT COUNT(DISTINCT ea.member_id)
            FROM event_attendance ea
            WHERE ea.event_id = e.id
            AND ea.attendance_status = 'present'
            AND DATE(ea.attendance_date) = CURRENT_DATE
        ) as present_count,
        (
            SELECT COUNT(*)
            FROM members m
            WHERE m.status = 'active'
        ) as total_members
    FROM events e 
    LEFT JOIN event_assignments ea ON e.id = ea.event_id
    LEFT JOIN users u ON ea.user_id = u.id
    LEFT JOIN members m ON u.member_id = m.id
    $whereClause
    GROUP BY e.id
    ORDER BY e.$sort $direction 
    LIMIT ? OFFSET ?";

try {
    $stmt = $conn->prepare($sql);
    $paramNum = 1;
    foreach ($params as $param) {
        $stmt->bindValue($paramNum++, $param);
    }
    $stmt->bindValue($paramNum++, $perPage, PDO::PARAM_INT);
    $stmt->bindValue($paramNum, $offset, PDO::PARAM_INT);
    $stmt->execute();
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate attendance percentage for events
    foreach ($events as &$event) {
        if ($event['total_members'] > 0) {
            $event['attendance_percentage'] = round(($event['present_count'] / $event['total_members']) * 100);
        } else {
            $event['attendance_percentage'] = 0;
        }

        // Convert BLOB image to base64 for the response
        if (isset($event['image']) && $event['image'] !== null) {
            $event['image'] = base64_encode($event['image']);
        }

        // Remove sensitive counts from response
        unset($event['present_count']);
        unset($event['total_members']);
    }

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'events' => $events,
        'currentPage' => $page,
        'totalPages' => ceil($total / $perPage),
        'showing' => count($events),
        'total' => $total
    ]);
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>