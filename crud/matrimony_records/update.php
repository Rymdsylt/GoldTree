<?php
header('Content-Type: application/json');
require_once '../../db/connection.php';

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data || !isset($data['id'])) {
        throw new Exception('Invalid request data');
    }

  
    $conn->beginTransaction();

   
    $stmt = $conn->prepare("
        UPDATE matrimony_records 
        SET matrimony_date = ?,
            church = ?,
            minister = ?
        WHERE id = ?
    ");
    $stmt->execute([
        $data['matrimony_date'],
        $data['church'],
        $data['minister'],
        $data['id']
    ]);

    $stmt = $conn->prepare("
        UPDATE matrimony_couples 
        SET name = ?,
            birth_date = ?,
            birth_place = ?,
            parent1_name = ?,
            parent1_origin = ?,
            parent2_name = ?,
            parent2_origin = ?,
            baptism_date = ?,
            baptism_church = ?,
            confirmation_date = ?,
            confirmation_church = ?
        WHERE id = ? AND type = 'bride'
    ");
    $stmt->execute([
        $data['bride']['name'],
        $data['bride']['birth_date'],
        $data['bride']['birth_place'],
        $data['bride']['parent1_name'],
        $data['bride']['parent1_origin'],
        $data['bride']['parent2_name'],
        $data['bride']['parent2_origin'],
        $data['bride']['baptism_date'],
        $data['bride']['baptism_church'],
        $data['bride']['confirmation_date'],
        $data['bride']['confirmation_church'],
        $data['bride']['id']
    ]);

   
    $stmt = $conn->prepare("
        UPDATE matrimony_couples 
        SET name = ?,
            birth_date = ?,
            birth_place = ?,
            parent1_name = ?,
            parent1_origin = ?,
            parent2_name = ?,
            parent2_origin = ?,
            baptism_date = ?,
            baptism_church = ?,
            confirmation_date = ?,
            confirmation_church = ?
        WHERE id = ? AND type = 'groom'
    ");
    $stmt->execute([
        $data['groom']['name'],
        $data['groom']['birth_date'],
        $data['groom']['birth_place'],
        $data['groom']['parent1_name'],
        $data['groom']['parent1_origin'],
        $data['groom']['parent2_name'],
        $data['groom']['parent2_origin'],
        $data['groom']['baptism_date'],
        $data['groom']['baptism_church'],
        $data['groom']['confirmation_date'],
        $data['groom']['confirmation_church'],
        $data['groom']['id']
    ]);


    $stmt = $conn->prepare("DELETE FROM matrimony_sponsors WHERE matrimony_record_id = ?");
    $stmt->execute([$data['id']]);


    if (!empty($data['sponsors'])) {
        $stmt = $conn->prepare("
            INSERT INTO matrimony_sponsors (
                matrimony_record_id,
                sponsor_name
            ) VALUES (?, ?)
        ");
        foreach ($data['sponsors'] as $sponsorName) {
            if (!empty($sponsorName)) {
                $stmt->execute([$data['id'], $sponsorName]);
            }
        }
    }

    $conn->commit();
    echo json_encode(['status' => 'success', 'message' => 'Record updated successfully']);

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