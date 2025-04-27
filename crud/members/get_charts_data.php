<?php
require_once '../../db/connection.php';
header('Content-Type: application/json');

try {
    $growthQuery = "
        SELECT 
            DATE_FORMAT(membership_date, '%Y-%m') as month,
            COUNT(*) as count
        FROM members
        WHERE membership_date >= DATE_SUB(CURRENT_DATE, INTERVAL 12 MONTH)
        GROUP BY DATE_FORMAT(membership_date, '%Y-%m')
        ORDER BY month ASC
    ";
    $growthStmt = $conn->query($growthQuery);
    $growthData = $growthStmt->fetchAll(PDO::FETCH_ASSOC);

    $labels = [];
    $values = [];
    

    $currentDate = new DateTime();
    for ($i = 11; $i >= 0; $i--) {
        $date = clone $currentDate;
        $date->modify("-$i months");
        $monthKey = $date->format('Y-m');
        $labels[] = $date->format('M Y');

        $count = 0;
        foreach ($growthData as $data) {
            if ($data['month'] === $monthKey) {
                $count = $data['count'];
                break;
            }
        }
        $values[] = $count;
    }


    $categoriesQuery = "
        SELECT 
            category,
            COUNT(*) as count
        FROM members
        GROUP BY category
    ";
    $categoriesStmt = $conn->query($categoriesQuery);
    $categoriesData = $categoriesStmt->fetchAll(PDO::FETCH_ASSOC);

 
    $categoryValues = [0, 0, 0, 0]; 
    $categoryMap = ['regular' => 0, 'youth' => 1, 'senior' => 2, 'visitor' => 3];
    
    foreach ($categoriesData as $data) {
        if (isset($categoryMap[$data['category']])) {
            $categoryValues[$categoryMap[$data['category']]] = (int)$data['count'];
        }
    }

    echo json_encode([
        'success' => true,
        'growth' => [
            'labels' => $labels,
            'values' => $values
        ],
        'categories' => [
            'values' => $categoryValues
        ]
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching chart data: ' . $e->getMessage()
    ]);
}