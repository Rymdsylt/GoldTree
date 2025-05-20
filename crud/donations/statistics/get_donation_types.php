<?php
require_once '../../../db/connection.php';

$startDate = $_GET['start'] ?? date('Y-m-d', strtotime('-30 days'));
$endDate = $_GET['end'] ?? date('Y-m-d');

try {
    $stmt = $conn->prepare("
        SELECT donation_type, SUM(amount) as total
        FROM donations 
        WHERE donation_date BETWEEN ? AND ?
        GROUP BY donation_type
        ORDER BY donation_type
    ");
    $stmt->execute([$startDate, $endDate]);
    
    $values = array_fill_keys(['tithe', 'offering', 'project', 'other'], 0);
    
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $values[$row['donation_type']] = floatval($row['total']);
    }
    
    echo json_encode([
        'values' => array_values($values) 
    ]);

} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}