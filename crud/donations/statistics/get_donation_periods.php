<?php
require_once '../../../db/connection.php';
header('Content-Type: application/json');

try {

    $stmt = $conn->prepare("SELECT COALESCE(SUM(amount), 0) as total 
        FROM donations 
        WHERE DATE(donation_date) = CURRENT_DATE");
    $stmt->execute();
    $todayDonations = $stmt->fetch(PDO::FETCH_ASSOC)['total'];


    $stmt = $conn->prepare("SELECT COALESCE(SUM(amount), 0) as total 
        FROM donations 
        WHERE YEARWEEK(donation_date, 1) = YEARWEEK(CURDATE(), 1)");
    $stmt->execute();
    $weeklyDonations = $stmt->fetch(PDO::FETCH_ASSOC)['total'];


    $stmt = $conn->prepare("SELECT COALESCE(SUM(amount), 0) as total 
        FROM donations 
        WHERE MONTH(donation_date) = MONTH(CURRENT_DATE) 
        AND YEAR(donation_date) = YEAR(CURRENT_DATE)");
    $stmt->execute();
    $monthlyDonations = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

 
    $stmt = $conn->prepare("SELECT COALESCE(SUM(amount), 0) as total 
        FROM donations 
        WHERE YEAR(donation_date) = YEAR(CURRENT_DATE)");
    $stmt->execute();
    $yearlyDonations = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    echo json_encode([
        'success' => true,
        'today' => (float)$todayDonations,
        'week' => (float)$weeklyDonations,
        'month' => (float)$monthlyDonations,
        'year' => (float)$yearlyDonations
    ]);

} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
