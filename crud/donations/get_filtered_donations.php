<?php
require_once '../../db/connection.php';

header('Content-Type: application/json');

if (isset($_GET['stats'])) {
    try {
        // Get total donations
        $stmt = $conn->query("SELECT SUM(amount) as total FROM donations");
        $totalDonations = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

        // Get monthly donations
        $stmt = $conn->query("SELECT SUM(amount) as monthly 
                            FROM donations 
                            WHERE MONTH(donation_date) = MONTH(CURRENT_DATE) 
                            AND YEAR(donation_date) = YEAR(CURRENT_DATE)");
        $monthlyDonations = $stmt->fetch(PDO::FETCH_ASSOC)['monthly'] ?? 0;

        // Get total unique donors
        $stmt = $conn->query("SELECT COUNT(DISTINCT COALESCE(member_id, donor_name)) as total_donors 
                            FROM donations");
        $totalDonors = $stmt->fetch(PDO::FETCH_ASSOC)['total_donors'] ?? 0;

        // Get average donation
        $stmt = $conn->query("SELECT AVG(amount) as average FROM donations");
        $averageDonation = $stmt->fetch(PDO::FETCH_ASSOC)['average'] ?? 0;

        echo json_encode([
            'success' => true,
            'totalDonations' => (float)$totalDonations,
            'monthlyDonations' => (float)$monthlyDonations,
            'totalDonors' => (int)$totalDonors,
            'averageDonation' => (float)$averageDonation
        ]);
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error retrieving statistics: ' . $e->getMessage()
        ]);
    }
    exit;
}

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

    echo json_encode($donations);
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
