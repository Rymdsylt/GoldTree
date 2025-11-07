<?php
require_once dirname(dirname(dirname(__FILE__))) . '/config.php';
// Error reporting handled by config.php

$basePath = dirname(dirname(dirname(__FILE__)));
require_once $basePath . '/db/connection.php';
require_once $basePath . '/auth/check_admin.php';

header('Content-Type: application/json');


$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
$offset = ($page - 1) * $perPage;


if (!isset($conn) || !$conn) {
    error_log("Database connection failed in first_communion_records/get_all.php");
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

try {
  
    $countQuery = "SELECT COUNT(*) as total FROM first_communion_records WHERE 1=1";
    

    $query = "SELECT * FROM first_communion_records WHERE 1=1";
    $params = [];


    if (!empty($_GET['name'])) {
        $query .= " AND name LIKE ?";
        $params[] = '%' . $_GET['name'] . '%';
    }

   
    if (!empty($_GET['parent'])) {
        $query .= " AND (parent1_name LIKE ? OR parent2_name LIKE ?)";
        $params[] = '%' . $_GET['parent'] . '%';
        $params[] = '%' . $_GET['parent'] . '%';
    }

   
    if (!empty($_GET['minister'])) {
        $query .= " AND minister LIKE ?";
        $params[] = '%' . $_GET['minister'] . '%';
    }


    if (!empty($_GET['dateFrom'])) {
        $query .= " AND communion_date >= ?";
        $params[] = $_GET['dateFrom'];
    }

    if (!empty($_GET['dateTo'])) {
        $query .= " AND communion_date <= ?";
        $params[] = $_GET['dateTo'];
    }

 
    $countStmt = $conn->prepare($countQuery);
    $countStmt->execute($params);
    $totalRecords = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

 
    $query .= " ORDER BY communion_date DESC LIMIT :limit OFFSET :offset";
    
    $stmt = $conn->prepare($query);
    
  
    $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    

    foreach ($params as $key => $value) {
        $stmt->bindValue($key + 1, $value); 
    }
    
    $stmt->execute();


    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
   
    echo json_encode([
        'status' => 'success',
        'data' => $records,
        'pagination' => [
            'total' => $totalRecords,
            'per_page' => $perPage,
            'current_page' => $page,
            'total_pages' => ceil($totalRecords / $perPage)
        ]
    ]);

} catch (Exception $e) {
  
    error_log("First Communion Records Error: " . $e->getMessage());
    error_log("SQL Query: " . $query);
    error_log("Parameters: " . print_r($params, true));
    

    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to fetch First Communion records: ' . $e->getMessage(),
        'query' => $query,
        'params' => $params
    ]);
}
?>