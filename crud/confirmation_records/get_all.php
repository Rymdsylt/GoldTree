<?php
require_once '../../db/connection.php';
require_once '../../auth/check_admin.php';

header('Content-Type: application/json');

try {
    $params = [];
    $sql = "SELECT SQL_CALC_FOUND_ROWS * FROM confirmation_records WHERE 1=1";


    if (isset($_GET['name']) && !empty($_GET['name'])) {
        $sql .= " AND name LIKE ?";
        $params[] = "%" . $_GET['name'] . "%";
    }


    if (isset($_GET['parent']) && !empty($_GET['parent'])) {
        $sql .= " AND (parent1_name LIKE ? OR parent2_name LIKE ?)";
        $params[] = "%" . $_GET['parent'] . "%";
        $params[] = "%" . $_GET['parent'] . "%";
    }


    if (isset($_GET['minister']) && !empty($_GET['minister'])) {
        $sql .= " AND minister LIKE ?";
        $params[] = "%" . $_GET['minister'] . "%";
    }

  
    if (isset($_GET['dateFrom']) && !empty($_GET['dateFrom'])) {
        $sql .= " AND baptism_date >= ?";
        $params[] = $_GET['dateFrom'];
    }
    if (isset($_GET['dateTo']) && !empty($_GET['dateTo'])) {
        $sql .= " AND baptism_date <= ?";
        $params[] = $_GET['dateTo'];
    }

    $sql .= " ORDER BY baptism_date DESC";


    $page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) && is_numeric($_GET['limit']) && $_GET['limit'] > 0 ? (int)$_GET['limit'] : 10;
    $offset = ($page - 1) * $limit;
    $sql .= " LIMIT $limit OFFSET $offset";

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

   
    $total = $conn->query("SELECT FOUND_ROWS()")->fetchColumn();

    echo json_encode([
        'data' => $records,
        'total' => (int)$total,
        'page' => $page,
        'limit' => $limit
    ]);
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>