<?php
require_once '../../db/connection.php';

// Check if database is PostgreSQL
$isPostgres = (getenv('DATABASE_URL') !== false);

// Use database-specific date formatting
if ($isPostgres) {
    $growthQuery = "SELECT TO_CHAR(created_at, 'YYYY-MM') as month, COUNT(*) as count 
                    FROM members 
                    GROUP BY month 
                    ORDER BY month ASC 
                    LIMIT 12";
} else {
    $growthQuery = "SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count 
                    FROM members 
                    GROUP BY month 
                    ORDER BY month ASC 
                    LIMIT 12";
}
$growthStmt = $conn->prepare($growthQuery);
$growthStmt->execute();
$growthData = $growthStmt->fetchAll(PDO::FETCH_ASSOC);

$labels = [];
$values = [];
foreach($growthData as $data) {
    $labels[] = date('M Y', strtotime($data['month']));
    $values[] = intval($data['count']);
}


$categoriesQuery = "SELECT category, COUNT(*) as count 
                   FROM members 
                   WHERE category IS NOT NULL AND category != '' 
                   GROUP BY category 
                   ORDER BY count DESC";
$categoriesStmt = $conn->prepare($categoriesQuery);
$categoriesStmt->execute();
$categoriesData = $categoriesStmt->fetchAll(PDO::FETCH_ASSOC);

$categoryLabels = [];
$categoryValues = [];
foreach($categoriesData as $data) {
    $categoryLabels[] = $data['category'];
    $categoryValues[] = intval($data['count']);
}

$otherQuery = "SELECT COUNT(*) as count 
               FROM members 
               WHERE category IS NULL OR category = ''";
$otherStmt = $conn->prepare($otherQuery);
$otherStmt->execute();
$otherCount = $otherStmt->fetch(PDO::FETCH_ASSOC)['count'];

if($otherCount > 0) {
    $categoryLabels[] = 'Other';
    $categoryValues[] = intval($otherCount);
}

$response = [
    'success' => true,
    'growth' => [
        'labels' => $labels,
        'values' => $values
    ],
    'categories' => [
        'labels' => $categoryLabels,
        'values' => $categoryValues
    ]
];

header('Content-Type: application/json');
echo json_encode($response);