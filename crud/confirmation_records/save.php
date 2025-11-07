<?php
require_once '../../db/connection.php';
require_once '../../auth/check_admin.php';

header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data) {
        throw new Exception('Invalid request data');
    }

    $conn->beginTransaction();
    
    // Check if database is PostgreSQL
    $isPostgres = (getenv('DATABASE_URL') !== false);

    // Use RETURNING for PostgreSQL, lastInsertId for MySQL
    if ($isPostgres) {
        $query = "INSERT INTO confirmation_records (
            name, parent1_name, parent1_origin, parent2_name, parent2_origin,
            address, birth_date, birth_place, gender, baptism_date, minister
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) RETURNING id";
    } else {
        $query = "INSERT INTO confirmation_records (
            name, parent1_name, parent1_origin, parent2_name, parent2_origin,
            address, birth_date, birth_place, gender, baptism_date, minister
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    }
    
    $stmt = $conn->prepare($query);
    $stmt->execute([
        $data['name'],
        $data['parent1_name'] ?? null,
        $data['parent1_origin'] ?? null,
        $data['parent2_name'] ?? null,
        $data['parent2_origin'] ?? null,
        $data['address'],
        $data['birth_date'],
        $data['birth_place'],
        $data['gender'],
        $data['baptism_date'],
        $data['minister']
    ]);
    
    if ($isPostgres) {
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $recordId = (int)$result['id'];
    } else {
        $recordId = (int)$conn->lastInsertId();
    }


    if (!empty($data['sponsors'])) {
        $sponsorQuery = "INSERT INTO confirmation_sponsors (confirmation_record_id, sponsor_name) VALUES (?, ?)";
        $sponsorStmt = $conn->prepare($sponsorQuery);
        
        foreach ($data['sponsors'] as $sponsor) {
            $sponsorStmt->execute([$recordId, $sponsor]);
        }
    }

    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Record saved successfully',
        'id' => $recordId
    ]);
} catch(Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to save record: ' . $e->getMessage()
    ]);
}
?>