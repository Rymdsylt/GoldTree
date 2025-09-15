<?php
require_once '../../auth/login_status.php';
require_once '../../db/connection.php';


$stmt = $conn->prepare("SELECT admin_status FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user || $user['admin_status'] != 1) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

try {
   
    $data = json_decode(file_get_contents('php://input'), true);
    
    $where_conditions = [];
    $params = [];

  
    if (!empty($data['sacramentType'])) {
        $where_conditions[] = "sacrament_type = ?";
        $params[] = $data['sacramentType'];
    }

    
    if (!empty($data['dateFrom'])) {
        $where_conditions[] = "date >= ?";
        $params[] = $data['dateFrom'];
    }
    if (!empty($data['dateTo'])) {
        $where_conditions[] = "date <= ?";
        $params[] = $data['dateTo'];
    }

   
    if (!empty($data['search'])) {
        $where_conditions[] = "name LIKE ?";
        $params[] = "%{$data['search']}%";
    }

 
    $sql = "SELECT * FROM sacramental_records";
    if (!empty($where_conditions)) {
        $sql .= " WHERE " . implode(" AND ", $where_conditions);
    }
    $sql .= " ORDER BY date DESC";

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $html = '';
    foreach ($records as $record) {
        $html .= "<tr>";
        $html .= "<td>" . htmlspecialchars($record['name']) . "</td>";
        $html .= "<td>" . htmlspecialchars($record['age']) . "</td>";
        $html .= "<td>" . htmlspecialchars($record['address']) . "</td>";
        $html .= "<td>" . htmlspecialchars($record['sacrament_type']) . "</td>";
        $html .= "<td>" . date('M d, Y', strtotime($record['date'])) . "</td>";
        $html .= "<td>" . htmlspecialchars($record['priest_presiding']) . "</td>";
        $html .= "<td class='text-end'>";
        $html .= "<button class='btn btn-sm btn-primary me-2' onclick='viewRecord(" . $record['id'] . ")'><i class='bi bi-eye'></i></button>";
        $html .= "<button class='btn btn-sm btn-warning me-2' onclick='editRecord(" . $record['id'] . ")'><i class='bi bi-pencil'></i></button>";
        $html .= "<button class='btn btn-sm btn-danger' onclick='deleteRecord(" . $record['id'] . ")'><i class='bi bi-trash'></i></button>";
        $html .= "</td>";
        $html .= "</tr>";
    }

    echo json_encode([
        'success' => true,
        'html' => $html,
        'count' => count($records)
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
