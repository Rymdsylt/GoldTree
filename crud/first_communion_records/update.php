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


    if (!isset($data['id']) || !is_numeric($data['id'])) {
        throw new Exception('Invalid record ID');
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
    
        $query = "UPDATE first_communion_records SET
            name = :name,
            gender = :gender,
            address = :address,
            birth_date = :birth_date,
            birth_place = :birth_place,
            parent1_name = :parent1_name,
            parent1_origin = :parent1_origin,
            parent2_name = :parent2_name,
            parent2_origin = :parent2_origin,
            baptism_date = :baptism_date,
            baptism_church = :baptism_church,
            church = :church,
            confirmation_date = :confirmation_date,
            minister = :minister,
            updated_at = CURRENT_TIMESTAMP
            WHERE id = :id";

        $stmt = $conn->prepare($query);
        $stmt->execute([
            ':id' => $data['id'],
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

        if ($stmt->rowCount() === 0) {
            throw new Exception('Record not found or no changes made');
        }

        $conn->commit();


        echo json_encode([
            'status' => 'success',
            'message' => 'First Communion record updated successfully'
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
