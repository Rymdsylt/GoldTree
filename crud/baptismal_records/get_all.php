<?php
require_once '../../db/connection.php';
require_once '../../auth/check_admin.php';

header('Content-Type: application/json');

try {

    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $offset = ($page - 1) * $limit;

    $dateFrom = isset($_GET['dateFrom']) ? $_GET['dateFrom'] : null;
    $dateTo = isset($_GET['dateTo']) ? $_GET['dateTo'] : null;
    $name = isset($_GET['name']) ? $_GET['name'] : null;
    $parent = isset($_GET['parent']) ? $_GET['parent'] : null;
    $minister = isset($_GET['minister']) ? $_GET['minister'] : null;

 
    $whereConditions = [];
    $params = [];

    if ($dateFrom) {
        $whereConditions[] = "baptism_date >= :dateFrom";
        $params[':dateFrom'] = $dateFrom;
    }
    if ($dateTo) {
        $whereConditions[] = "baptism_date <= :dateTo";
        $params[':dateTo'] = $dateTo;
    }
    if ($name) {
        $whereConditions[] = "name LIKE :name";
        $params[':name'] = "%$name%";
    }
    if ($parent) {
        $whereConditions[] = "(parent1_name LIKE :parent OR parent2_name LIKE :parent)";
        $params[':parent'] = "%$parent%";
    }
    if ($minister) {
        $whereConditions[] = "minister LIKE :minister";
        $params[':minister'] = "%$minister%";
    }

    $whereClause = '';
    if (!empty($whereConditions)) {
        $whereClause = "WHERE " . implode(" AND ", $whereConditions);
    }


    $countQuery = "SELECT COUNT(*) as total FROM baptismal_records $whereClause";
    $countStmt = $conn->prepare($countQuery);
    foreach ($params as $key => $value) {
        $countStmt->bindValue($key, $value);
    }
    $countStmt->execute();
    $totalRecords = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
    $totalPages = ceil($totalRecords / $limit);

    $query = "SELECT * FROM baptismal_records $whereClause ORDER BY baptism_date DESC LIMIT :limit OFFSET :offset";
    $stmt = $conn->prepare($query);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'records' => $records,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => $totalRecords,
            'totalPages' => $totalPages
        ]
    ]);
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>