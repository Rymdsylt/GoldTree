<?php
require_once '../../db/connection.php';

header('Content-Type: application/json');

// Check if database is PostgreSQL
$isPostgres = (getenv('DATABASE_URL') !== false);

// If a specific donation ID is requested
if (isset($_GET['id'])) {
    // Use database-specific string concatenation
    if ($isPostgres) {
        $sql = "
            SELECT d.*, COALESCE(m.first_name || ' ' || m.last_name, d.donor_name) as donor_name
            FROM donations d
            LEFT JOIN members m ON d.member_id = m.id
            WHERE d.id = ?
        ";
    } else {
        $sql = "
            SELECT d.*, COALESCE(CONCAT(m.first_name, ' ', m.last_name), d.donor_name) as donor_name
            FROM donations d
            LEFT JOIN members m ON d.member_id = m.id
            WHERE d.id = ?
        ";
    }
    $stmt = $conn->prepare($sql);
    $stmt->execute([$_GET['id']]);
    $donation = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'donations' => $donation ? [$donation] : []
    ]);
    exit;
}

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$where_clauses = [];
$params = [];

if (!empty($_GET['search'])) {
    $search = '%' . $_GET['search'] . '%';
    $where_clauses[] = "(m.first_name LIKE ? OR m.last_name LIKE ? OR d.donor_name LIKE ?)";
    $params = array_merge($params, [$search, $search, $search]);
}

if (!empty($_GET['type'])) {
    $where_clauses[] = "d.donation_type = ?";
    $params[] = $_GET['type'];
}

if (!empty($_GET['start_date']) || !empty($_GET['start'])) {
    $where_clauses[] = "d.donation_date >= ?";
    $params[] = $_GET['start_date'] ?? $_GET['start'];
}
if (!empty($_GET['end_date']) || !empty($_GET['end'])) {
    $where_clauses[] = "d.donation_date <= ?";
    $params[] = ($_GET['end_date'] ?? $_GET['end']) . ' 23:59:59';
}

$where_sql = !empty($where_clauses) ? 'WHERE ' . implode(' AND ', $where_clauses) : '';


if (isset($_GET['stats'])) {
    // Build monthly donations subquery with database-specific date functions
    if ($isPostgres) {
        $monthlyCondition = "EXTRACT(MONTH FROM d2.donation_date) = EXTRACT(MONTH FROM CURRENT_DATE)
            AND EXTRACT(YEAR FROM d2.donation_date) = EXTRACT(YEAR FROM CURRENT_DATE)";
    } else {
        $monthlyCondition = "MONTH(d2.donation_date) = MONTH(CURRENT_DATE)
            AND YEAR(d2.donation_date) = YEAR(CURRENT_DATE)";
    }
    
    $stats_sql = "SELECT 
        COALESCE(SUM(d.amount), 0) as totalDonations,
        COUNT(DISTINCT COALESCE(d.member_id, d.donor_name)) as totalDonors,
        COALESCE(AVG(d.amount), 0) as averageDonation,
        (
            SELECT COALESCE(SUM(amount), 0)
            FROM donations d2
            WHERE $monthlyCondition
            " . ($where_sql ? ' AND ' . str_replace('d.', 'd2.', implode(' AND ', $where_clauses)) : '') . "
        ) as monthlyDonations
        FROM donations d
        LEFT JOIN members m ON d.member_id = m.id
        $where_sql";
    
    $stmt = $conn->prepare($stats_sql);
    $stmt->execute($params);
    echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
    exit;
}

$count_sql = "SELECT COUNT(*) FROM donations d 
    LEFT JOIN members m ON d.member_id = m.id 
    $where_sql";
$stmt = $conn->prepare($count_sql);
$stmt->execute($params);
$total = $stmt->fetchColumn();

// Build SQL with database-specific string concatenation
if ($isPostgres) {
    $sql = "SELECT 
        d.*,
        COALESCE(m.first_name || ' ' || m.last_name, d.donor_name) as donor_name
        FROM donations d
        LEFT JOIN members m ON d.member_id = m.id
        $where_sql
        ORDER BY d.donation_date DESC
        LIMIT :limit OFFSET :offset";
} else {
    $sql = "SELECT 
        d.*,
        COALESCE(CONCAT(m.first_name, ' ', m.last_name), d.donor_name) as donor_name
        FROM donations d
        LEFT JOIN members m ON d.member_id = m.id
        $where_sql
        ORDER BY d.donation_date DESC
        LIMIT :limit OFFSET :offset";
}

$stmt = $conn->prepare($sql);
foreach ($params as $key => $value) {
    if (is_int($key)) {
        $stmt->bindValue($key + 1, $value);
    } else {
        $stmt->bindValue($key, $value);
    }
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$donations = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalPages = ceil($total / $limit);

echo json_encode([
    'success' => true,
    'donations' => $donations,
    'currentPage' => $page,
    'totalPages' => $totalPages,
    'showing' => count($donations),
    'total' => $total
]);