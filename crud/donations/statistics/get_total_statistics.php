<?php
require_once '../../../db/connection.php';
header('Content-Type: application/json');

try {
    $stmt = $conn->prepare("SELECT 
        COALESCE(SUM(amount), 0) as all_time_total,
        COUNT(*) as total_count
        FROM donations");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'totalDonations' => (float)$result['all_time_total'],
        'donationCount' => (int)$result['total_count']
    ]);

} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
