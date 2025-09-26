<?php
require_once '../../db/connection.php';
require_once '../../auth/check_admin.php';

header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data || !isset($data['id'])) {
        throw new Exception('Invalid request data');
    }

    $conn->beginTransaction();

    $query = "UPDATE confirmation_records SET 
        name = ?, parent1_name = ?, parent1_origin = ?, parent2_name = ?, parent2_origin = ?,
        address = ?, birth_date = ?, birth_place = ?, gender = ?, baptism_date = ?, minister = ?
        WHERE id = ?";
    
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
        $data['minister'],
        $data['id']
    ]);


    if (isset($data['sponsors'])) {

        $deleteSponsors = "DELETE FROM confirmation_sponsors WHERE confirmation_record_id = ?";
        $stmt = $conn->prepare($deleteSponsors);
        $stmt->execute([$data['id']]);
        
    
        if (!empty($data['sponsors'])) {
            $sponsorQuery = "INSERT INTO confirmation_sponsors (confirmation_record_id, sponsor_name) VALUES (?, ?)";
            $sponsorStmt = $conn->prepare($sponsorQuery);
            
            foreach ($data['sponsors'] as $sponsor) {
                $sponsorStmt->execute([$data['id'], $sponsor]);
            }
        }
    }

    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Record updated successfully'
    ]);
} catch(Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to update record: ' . $e->getMessage()
    ]);
}
?>