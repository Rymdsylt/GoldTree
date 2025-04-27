<?php
require_once '../../db/connection.php';

$startDate = $_GET['start'] ?? date('Y-m-d', strtotime('-30 days'));
$endDate = $_GET['end'] ?? date('Y-m-d');

try {
    $stmt = $conn->prepare("
        SELECT DATE(donation_date) as date, SUM(amount) as total
        FROM donations 
        WHERE donation_date BETWEEN ? AND ?
        GROUP BY DATE(donation_date)
        ORDER BY date ASC
    ");
    $stmt->execute([$startDate, $endDate]);
    
    $labels = [];
    $values = [];
    
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $labels[] = date('M d', strtotime($row['date']));
        $values[] = floatval($row['total']);
    }
    
    echo json_encode([
        'labels' => $labels,
        'values' => $values
    ]);

} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}