<?php
require_once '../../db/connection.php';


header('Content-Type: application/json');

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$search = isset($_GET['search']) ? $_GET['search'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'name';

$limit = 10; 
$offset = ($page - 1) * $limit;

try {
    $params = [];
    $where = [];
    
    if ($search) {
        $where[] = "(first_name LIKE :search OR last_name LIKE :search OR email LIKE :search OR phone LIKE :search)";
        $params[':search'] = "%$search%";
    }
    
    if ($status) {
        $where[] = "status = :status";
        $params[':status'] = $status;
    }
    
    $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
    
    $orderBy = match($sort) {
        'date' => 'membership_date DESC',
        'status' => 'status ASC',
        default => 'first_name ASC'
    };
 
    $countQuery = "SELECT COUNT(*) FROM members $whereClause";
    $stmt = $conn->prepare($countQuery);
    foreach($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    $total = $stmt->fetchColumn();

    $query = "SELECT id, first_name, last_name, email, phone, membership_date, status, profile_image 
             FROM members $whereClause 
             ORDER BY $orderBy 
             LIMIT :limit OFFSET :offset";
    
    $stmt = $conn->prepare($query);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    foreach($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    
    $members = [];
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

        if ($row['profile_image']) {
            $row['profile_image'] = base64_encode($row['profile_image']);
        }
        $members[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'members' => $members,
        'total' => $total,
        'showing' => count($members),
        'currentPage' => $page,
        'totalPages' => ceil($total / $limit)
    ]);

} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error loading members: ' . $e->getMessage()
    ]);
}
?>
