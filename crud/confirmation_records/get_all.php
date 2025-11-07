<?php
require_once '../../db/connection.php';
require_once '../../auth/check_admin.php';

header('Content-Type: application/json');

// Check if database is PostgreSQL
$isPostgres = (getenv('DATABASE_URL') !== false);

try {
    $params = [];
    // Use database-specific count query
    $countSql = "SELECT COUNT(*) FROM confirmation_records WHERE 1=1";
    $sql = "SELECT * FROM confirmation_records WHERE 1=1";


    if (isset($_GET['name']) && !empty($_GET['name'])) {
        $sql .= " AND name LIKE ?";
        $countSql .= " AND name LIKE ?";
        $params[] = "%" . $_GET['name'] . "%";
    }


    if (isset($_GET['parent']) && !empty($_GET['parent'])) {
        $sql .= " AND (parent1_name LIKE ? OR parent2_name LIKE ?)";
        $countSql .= " AND (parent1_name LIKE ? OR parent2_name LIKE ?)";
        $params[] = "%" . $_GET['parent'] . "%";
        $params[] = "%" . $_GET['parent'] . "%";
    }


    if (isset($_GET['minister']) && !empty($_GET['minister'])) {
        $sql .= " AND minister LIKE ?";
        $countSql .= " AND minister LIKE ?";
        $params[] = "%" . $_GET['minister'] . "%";
    }

  
    if (isset($_GET['dateFrom']) && !empty($_GET['dateFrom'])) {
        $sql .= " AND baptism_date >= ?";
        $countSql .= " AND baptism_date >= ?";
        $params[] = $_GET['dateFrom'];
    }
    if (isset($_GET['dateTo']) && !empty($_GET['dateTo'])) {
        $sql .= " AND baptism_date <= ?";
        $countSql .= " AND baptism_date <= ?";
        $params[] = $_GET['dateTo'];
    }

    $sql .= " ORDER BY baptism_date DESC";


    $page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) && is_numeric($_GET['limit']) && $_GET['limit'] > 0 ? (int)$_GET['limit'] : 10;
    $offset = ($page - 1) * $limit;
    $sql .= " LIMIT :limit OFFSET :offset";

    // Get total count
    $countStmt = $conn->prepare($countSql);
    $countStmt->execute($params);
    $total = $countStmt->fetchColumn();

    // Get records
    $stmt = $conn->prepare($sql);
    foreach ($params as $key => $value) {
        if (is_int($key)) {
            $stmt->bindValue($key + 1, $value);
        } else {
            $stmt->bindValue($key, $value);
        }
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

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