<?php
require_once '../../../db/connection.php';
header('Content-Type: application/json');

$startDate = $_GET['start'] ?? date('Y-m-d', strtotime('-30 days'));
$endDate = $_GET['end'] ?? date('Y-m-d');

try {

    $stmt = $conn->prepare("SELECT COALESCE(SUM(amount), 0) as total 
        FROM donations 
        WHERE donation_date BETWEEN ? AND ?");
    $stmt->execute([$startDate, $endDate]);
    $totalDonations = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    echo json_encode([
        'totalDonations' => (float)$totalDonations
    ]);

} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}