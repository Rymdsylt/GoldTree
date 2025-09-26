<?php
header('Content-Type: application/json');
require_once '../../db/connection.php';

try {
    if (!isset($_GET['id'])) {
        throw new Exception('Record ID is required');
    }

    $id = $_GET['id'];

    
    $query = "SELECT * FROM matrimony_records WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$id]);
    $record = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$record) {
        throw new Exception('Record not found');
    }

    $couplesQuery = "SELECT * FROM matrimony_couples WHERE matrimony_record_id = ?";
    $couplesStmt = $conn->prepare($couplesQuery);
    $couplesStmt->execute([$id]);
    $couples = $couplesStmt->fetchAll(PDO::FETCH_ASSOC);


    foreach ($couples as &$couple) {
        if (isset($couple['birth_date'])) {
            $couple['birth_date'] = date('Y-m-d', strtotime($couple['birth_date']));
        }
        if (isset($couple['baptism_date'])) {
            $couple['baptism_date'] = date('Y-m-d', strtotime($couple['baptism_date']));
        }
        if (isset($couple['confirmation_date'])) {
            $couple['confirmation_date'] = date('Y-m-d', strtotime($couple['confirmation_date']));
        }
    }
    unset($couple);
    $record['couples'] = $couples;


    $sponsorsQuery = "SELECT * FROM matrimony_sponsors WHERE matrimony_record_id = ?";
    $sponsorsStmt = $conn->prepare($sponsorsQuery);
    $sponsorsStmt->execute([$id]);
    $sponsors = $sponsorsStmt->fetchAll(PDO::FETCH_ASSOC);
    $record['sponsors'] = $sponsors;


    if (isset($record['matrimony_date'])) {
        $record['matrimony_date'] = date('Y-m-d', strtotime($record['matrimony_date']));
    }


    echo json_encode([
        'status' => 'success',
        'data' => $record
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}