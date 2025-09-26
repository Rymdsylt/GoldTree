<?php
header('Content-Type: application/json');
require_once '../../db/connection.php';

try {

    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
    $offset = ($page - 1) * $perPage;


    $countQuery = "SELECT COUNT(DISTINCT mr.id) as total FROM matrimony_records mr";
    

    $query = "SELECT DISTINCT mr.* FROM matrimony_records mr";
    $params = [];
    $conditions = [];


    if (!empty($_GET['brideName']) || !empty($_GET['groomName'])) {
        $query .= " LEFT JOIN matrimony_couples mc ON mr.id = mc.matrimony_record_id";
        
        if (!empty($_GET['brideName'])) {
            $conditions[] = "(mc.type = 'bride' AND mc.name LIKE ?)";
            $params[] = '%' . $_GET['brideName'] . '%';
        }
        
        if (!empty($_GET['groomName'])) {
            $conditions[] = "(mc.type = 'groom' AND mc.name LIKE ?)";
            $params[] = '%' . $_GET['groomName'] . '%';
        }
    }

    if (!empty($_GET['dateFrom'])) {
        $conditions[] = "mr.matrimony_date >= ?";
        $params[] = $_GET['dateFrom'];
    }
    
    if (!empty($_GET['dateTo'])) {
        $conditions[] = "mr.matrimony_date <= ?";
        $params[] = $_GET['dateTo'];
    }

   
    if (!empty($_GET['minister'])) {
        $conditions[] = "mr.minister LIKE ?";
        $params[] = '%' . $_GET['minister'] . '%';
    }

    if (!empty($conditions)) {
        $query .= " WHERE " . implode(" AND ", $conditions);
    }


    $countQuery .= (!empty($conditions)) ? " WHERE " . implode(" AND ", $conditions) : "";
    $countStmt = $conn->prepare($countQuery);
    $countStmt->execute($params);
    $totalRecords = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];


    $query .= " ORDER BY mr.matrimony_date DESC LIMIT :limit OFFSET :offset";

    $stmt = $conn->prepare($query);

    $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
 
    foreach ($params as $key => $value) {
        $stmt->bindValue($key + 1, $value);
    }
    $stmt->execute();
    $records = [];
    
    while ($record = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $record_id = $record['id'];
        

        $couples_query = "
            SELECT * FROM matrimony_couples 
            WHERE matrimony_record_id = ? 
            ORDER BY type DESC"; 
        $couples_stmt = $conn->prepare($couples_query);
        $couples_stmt->execute([$record_id]);
        $couples = $couples_stmt->fetchAll(PDO::FETCH_ASSOC);
        
       
        $sponsors_query = "
            SELECT * FROM matrimony_sponsors 
            WHERE matrimony_record_id = ?
            ORDER BY id";
        $sponsors_stmt = $conn->prepare($sponsors_query);
        $sponsors_stmt->execute([$record_id]);
        $sponsors = $sponsors_stmt->fetchAll(PDO::FETCH_ASSOC);
        

        $record['couples'] = $couples;
        $record['sponsors'] = $sponsors;
        $records[] = $record;
    }

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
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}