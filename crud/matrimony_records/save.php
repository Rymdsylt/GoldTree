<?php
header('Content-Type: application/json');
require_once '../../db/connection.php';

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
        $stmt = $conn->prepare("
            INSERT INTO matrimony_records (
                matrimony_date, 
                church, 
                minister
            ) VALUES (?, ?, ?) RETURNING id
        ");
    } else {
        $stmt = $conn->prepare("
            INSERT INTO matrimony_records (
                matrimony_date, 
                church, 
                minister
            ) VALUES (?, ?, ?)
        ");
    }
    $stmt->execute([
        $data['matrimony_date'],
        $data['church'],
        $data['minister']
    ]);
    
    if ($isPostgres) {
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $matrimonyId = (int)$result['id'];
    } else {
        $matrimonyId = (int)$conn->lastInsertId();
    }


    foreach ($data['couples'] as $couple) {
        $stmt = $conn->prepare("
            INSERT INTO matrimony_couples (
                matrimony_record_id,
                type,
                name,
                parent1_name,
                parent1_origin,
                parent2_name,
                parent2_origin,
                birth_date,
                birth_place,
                gender,
                baptism_date,
                baptism_church,
                confirmation_date,
                confirmation_church
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $matrimonyId,
            $couple['type'],
            $couple['name'],
            $couple['parent1_name'],
            $couple['parent1_origin'],
            $couple['parent2_name'],
            $couple['parent2_origin'],
            $couple['birth_date'],
            $couple['birth_place'],
            $couple['gender'],
            $couple['baptism_date'],
            $couple['baptism_church'],
            $couple['confirmation_date'],
            $couple['confirmation_church']
        ]);
    }

    if (!empty($data['sponsors'])) {
        $stmt = $conn->prepare("
            INSERT INTO matrimony_sponsors (
                matrimony_record_id,
                sponsor_name
            ) VALUES (?, ?)
        ");
        foreach ($data['sponsors'] as $sponsorName) {
            $stmt->execute([$matrimonyId, $sponsorName]);
        }
    }

    $conn->commit();
    echo json_encode(['status' => 'success', 'message' => 'Record saved successfully', 'id' => $matrimonyId]);

} catch (PDOException $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}