<?php
require_once '../../db/connection.php';

try {
    $stmt = $conn->prepare("
        SELECT 
            CASE 
                WHEN age < 25 THEN '18-24'
                WHEN age < 35 THEN '25-34'
                WHEN age < 45 THEN '35-44'
                WHEN age < 55 THEN '45-54'
                ELSE '55+'
            END as age_group,
            COUNT(*) as count
        FROM (
            SELECT TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) as age
            FROM members
            WHERE status = 'active'
        ) age_calc
        GROUP BY age_group
        ORDER BY age_group
    ");
    $stmt->execute();
    
    $values = array_fill_keys(['18-24', '25-34', '35-44', '45-54', '55+'], 0);
    
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $values[$row['age_group']] = intval($row['count']);
    }
    
    echo json_encode([
        'values' => array_values($values) 
    ]);

} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}