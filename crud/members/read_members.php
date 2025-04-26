<?php
require_once '../../db/connection.php';
header('Content-Type: application/json');

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$search = isset($_GET['search']) ? $_GET['search'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'name';

$limit = 10;
$offset = ($page - 1) * $limit;

$query = "SELECT * FROM members WHERE 1=1";
$countQuery = "SELECT COUNT(*) as total FROM members WHERE 1=1";

if (!empty($search)) {
    $searchTerm = "%$search%";
    $query .= " AND (first_name LIKE :search OR last_name LIKE :search OR email LIKE :search OR phone LIKE :search)";
    $countQuery .= " AND (first_name LIKE :search OR last_name LIKE :search OR email LIKE :search OR phone LIKE :search)";
}

if (!empty($status)) {
    $query .= " AND status = :status";
    $countQuery .= " AND status = :status";
}

switch ($sort) {
    case 'name':
        $query .= " ORDER BY first_name, last_name";
        break;
    case 'date':
        $query .= " ORDER BY membership_date DESC";
        break;
    case 'status':
        $query .= " ORDER BY status, first_name, last_name";
        break;
}

$query .= " LIMIT :limit OFFSET :offset";

try {
    $countStmt = $conn->prepare($countQuery);
    if (!empty($search)) {
        $countStmt->bindValue(':search', $searchTerm, PDO::PARAM_STR);
    }
    if (!empty($status)) {
        $countStmt->bindValue(':status', $status, PDO::PARAM_STR);
    }
    $countStmt->execute();
    $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

    $stmt = $conn->prepare($query);
    if (!empty($search)) {
        $stmt->bindValue(':search', $searchTerm, PDO::PARAM_STR);
    }
    if (!empty($status)) {
        $stmt->bindValue(':status', $status, PDO::PARAM_STR);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $members = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $totalPages = ceil($total / $limit);
    $showing = count($members);

    echo json_encode([
        'success' => true,
        'members' => $members,
        'total' => $total,
        'showing' => $showing,
        'currentPage' => $page,
        'totalPages' => $totalPages
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching members: ' . $e->getMessage()
    ]);
}
?>
