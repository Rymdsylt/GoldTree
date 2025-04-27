<?php
require_once '../../db/connection.php';

header('Content-Type: application/json');
$response = ['success' => false, 'donations' => [], 'total' => 0, 'showing' => 0];

try {

    if (isset($_GET['id'])) {
        $stmt = $conn->prepare("SELECT d.*, 
            CASE 
                WHEN d.member_id IS NOT NULL THEN CONCAT(m.first_name, ' ', m.last_name)
                ELSE d.donor_name 
            END as donor_name
            FROM donations d 
            LEFT JOIN members m ON d.member_id = m.id 
            WHERE d.id = ?");
        $stmt->execute([$_GET['id']]);
        $donations = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $response['success'] = true;
        $response['donations'] = $donations;
        $response['total'] = count($donations);
        $response['showing'] = count($donations);
        echo json_encode($response);
        exit;
    }

    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $search = $_GET['search'] ?? '';
    $type = $_GET['type'] ?? '';
    $start = $_GET['start'] ?? '';
    $end = $_GET['end'] ?? '';
    $limit = 10;
    $offset = ($page - 1) * $limit;


    $where_conditions = [];
    $params = [];

    if (!empty($search)) {
        $where_conditions[] = "(m.first_name LIKE ? OR m.last_name LIKE ? OR d.donor_name LIKE ?)";
        $search_param = "%$search%";
        $params[] = $search_param;
        $params[] = $search_param;
        $params[] = $search_param;
    }

    if (!empty($type)) {
        $where_conditions[] = "d.donation_type = ?";
        $params[] = $type;
    }

    if (!empty($start)) {
        $where_conditions[] = "d.donation_date >= ?";
        $params[] = $start;
    }

    if (!empty($end)) {
        $where_conditions[] = "d.donation_date <= ?";
        $params[] = $end;
    }

    $where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";


    $count_sql = "SELECT COUNT(*) FROM donations d 
                  LEFT JOIN members m ON d.member_id = m.id 
                  $where_clause";
    
    if (!empty($params)) {
        $stmt = $conn->prepare($count_sql);
        $stmt->execute($params);
    } else {
        $stmt = $conn->query($count_sql);
    }
    $total = $stmt->fetchColumn();


    $query = "SELECT d.*, 
        CASE 
            WHEN d.member_id IS NOT NULL THEN CONCAT(m.first_name, ' ', m.last_name)
            ELSE d.donor_name 
        END as donor_name,
        d.donation_type as type
        FROM donations d 
        LEFT JOIN members m ON d.member_id = m.id 
        $where_clause
        ORDER BY d.donation_date DESC, d.created_at DESC 
        LIMIT :limit OFFSET :offset";

    $stmt = $conn->prepare($query);

    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    

    foreach ($params as $key => $value) {
        $stmt->bindValue($key + 1, $value);
    }
    
    $stmt->execute();
    $donations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $response['success'] = true;
    $response['donations'] = $donations;
    $response['total'] = $total;
    $response['showing'] = count($donations);
    $response['currentPage'] = $page;
    $response['totalPages'] = ceil($total / $limit);

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);