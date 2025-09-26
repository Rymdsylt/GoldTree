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


    $requiredFields = ['matrimony_date', 'church', 'minister'];
    foreach ($requiredFields as $field) {
        if (!isset($data[$field]) || trim($data[$field]) === '') {
            throw new Exception("Missing required field: $field");
        }
    }

 
    if (!isset($data['bride']) || !isset($data['groom'])) {
        throw new Exception("Both bride and groom information are required");
    }

    foreach (['bride', 'groom'] as $person) {
        $requiredCoupleFields = [
            'name', 'birth_date', 'birth_place', 'gender',
            'baptism_date', 'baptism_church',
            'confirmation_date', 'confirmation_church'
        ];
        foreach ($requiredCoupleFields as $field) {
            if (!isset($data[$person][$field]) || trim($data[$person][$field]) === '') {
                throw new Exception("Missing required field for {$person}: {$field}");
            }
        }
    }


    $conn->beginTransaction();

    try {
        
        $stmt = $conn->prepare("INSERT INTO matrimony_records (matrimony_date, church, minister) VALUES (?, ?, ?)");
        $stmt->execute([
            $data['matrimony_date'],
            $data['church'],
            $data['minister']
        ]);

        $matrimonyId = $conn->lastInsertId();

        $coupleStmt = $conn->prepare("
            INSERT INTO matrimony_couples (
                matrimony_record_id, type, name, 
                parent1_name, parent1_origin, 
                parent2_name, parent2_origin,
                birth_date, birth_place, gender,
                baptism_date, baptism_church,
                confirmation_date, confirmation_church
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

      
        $coupleStmt->execute([
            $matrimonyId, 'bride',
            $data['bride']['name'],
            $data['bride']['parent1_name'] ?? null,
            $data['bride']['parent1_origin'] ?? null,
            $data['bride']['parent2_name'] ?? null,
            $data['bride']['parent2_origin'] ?? null,
            $data['bride']['birth_date'],
            $data['bride']['birth_place'],
            'female',
            $data['bride']['baptism_date'],
            $data['bride']['baptism_church'],
            $data['bride']['confirmation_date'],
            $data['bride']['confirmation_church']
        ]);

   
        $coupleStmt->execute([
            $matrimonyId, 'groom',
            $data['groom']['name'],
            $data['groom']['parent1_name'] ?? null,
            $data['groom']['parent1_origin'] ?? null,
            $data['groom']['parent2_name'] ?? null,
            $data['groom']['parent2_origin'] ?? null,
            $data['groom']['birth_date'],
            $data['groom']['birth_place'],
            'male',
            $data['groom']['baptism_date'],
            $data['groom']['baptism_church'],
            $data['groom']['confirmation_date'],
            $data['groom']['confirmation_church']
        ]);

    
        if (!empty($data['sponsors'])) {
            $sponsorStmt = $conn->prepare("
                INSERT INTO matrimony_sponsors (matrimony_record_id, sponsor_name)
                VALUES (?, ?)
            ");

            foreach ($data['sponsors'] as $sponsor) {
                if (!empty(trim($sponsor))) {
                    $sponsorStmt->execute([$matrimonyId, trim($sponsor)]);
                }
            }
        }

   
        $conn->commit();

     
        echo json_encode([
            'status' => 'success',
            'message' => 'Marriage record created successfully',
            'id' => $matrimonyId
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
