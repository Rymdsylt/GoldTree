<?php
require_once '../../db/connection.php';
require_once '../../auth/check_admin.php';

header('Content-Type: application/json');

try {
 
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (!$data) {
        throw new Exception('Invalid request data');
    }


    $requiredFields = [
        'name', 'gender', 'address', 
        'birth_date', 'birth_place',
        'baptism_date', 'baptism_church',
        'church', 'confirmation_date', 'minister'
    ];

    foreach ($requiredFields as $field) {
        if (!isset($data[$field]) || trim($data[$field]) === '') {
            throw new Exception("Missing required field: $field");
        }
    }


    $conn->beginTransaction();

    try {
        // Check if database is PostgreSQL
        $isPostgres = (getenv('DATABASE_URL') !== false);
   
        // Use RETURNING for PostgreSQL, lastInsertId for MySQL
        if ($isPostgres) {
            $query = "INSERT INTO first_communion_records (
                name, gender, address, 
                birth_date, birth_place,
                parent1_name, parent1_origin,
                parent2_name, parent2_origin,
                baptism_date, baptism_church,
                church, confirmation_date, minister
            ) VALUES (
                :name, :gender, :address,
                :birth_date, :birth_place,
                :parent1_name, :parent1_origin,
                :parent2_name, :parent2_origin,
                :baptism_date, :baptism_church,
                :church, :confirmation_date, :minister
            ) RETURNING id";
        } else {
            $query = "INSERT INTO first_communion_records (
                name, gender, address, 
                birth_date, birth_place,
                parent1_name, parent1_origin,
                parent2_name, parent2_origin,
                baptism_date, baptism_church,
                church, confirmation_date, minister
            ) VALUES (
                :name, :gender, :address,
                :birth_date, :birth_place,
                :parent1_name, :parent1_origin,
                :parent2_name, :parent2_origin,
                :baptism_date, :baptism_church,
                :church, :confirmation_date, :minister
            )";
        }

        $stmt = $conn->prepare($query);
        $stmt->execute([
            ':name' => $data['name'],
            ':gender' => $data['gender'],
            ':address' => $data['address'],
            ':birth_date' => $data['birth_date'],
            ':birth_place' => $data['birth_place'],
            ':parent1_name' => $data['parent1_name'] ?? 'N/A',
            ':parent1_origin' => $data['parent1_origin'] ?? 'N/A',
            ':parent2_name' => $data['parent2_name'] ?? 'N/A',
            ':parent2_origin' => $data['parent2_origin'] ?? 'N/A',
            ':baptism_date' => $data['baptism_date'],
            ':baptism_church' => $data['baptism_church'],
            ':church' => $data['church'],
            ':confirmation_date' => $data['confirmation_date'],
            ':minister' => $data['minister']
        ]);

        if ($isPostgres) {
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $recordId = (int)$result['id'];
        } else {
            $recordId = (int)$conn->lastInsertId();
        }


        $conn->commit();

        echo json_encode([
            'status' => 'success',
            'message' => 'First Communion record saved successfully',
            'id' => $recordId
        ]);

    } catch (Exception $e) {
   
        $conn->rollBack();
        throw $e;
    }

} catch (Exception $e) {
   
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
