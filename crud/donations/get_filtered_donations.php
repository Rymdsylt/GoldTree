<?php
require_once '../../db/connection.php';

$data = json_decode(file_get_contents('php://input'), true);
$type = $data['type'] ?? '';
$fromDate = $data['fromDate'] ?? '';
$toDate = $data['toDate'] ?? '';

try {
    $sql = "SELECT d.*, CONCAT(m.first_name, ' ', m.last_name) as member_name 
            FROM donations d 
            LEFT JOIN members m ON d.member_id = m.id 
            WHERE 1=1";
    $params = [];

    if (!empty($type)) {
        $sql .= " AND d.donation_type = ?";
        $params[] = $type;
    }
    if (!empty($fromDate)) {
        $sql .= " AND d.donation_date >= ?";
        $params[] = $fromDate;
    }
    if (!empty($toDate)) {
        $sql .= " AND d.donation_date <= ?";
        $params[] = $toDate;
    }

    $sql .= " ORDER BY d.donation_date DESC";

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $donations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: application/json');
    echo json_encode($donations);
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
