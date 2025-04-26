<?php
require_once '../../db/connection.php';
require_once '../../auth/login_status.php';

$response = ['success' => false, 'message' => ''];

try {
    $amount = $_POST['amount'] ?? '';
    $donation_type = $_POST['donation_type'] ?? '';
    $donation_date = $_POST['donation_date'] ?? '';
    $notes = $_POST['notes'] ?? '';
    $member_id = $_POST['member_id'] ?? null;

    // Validate required fields
    if (empty($amount) || empty($donation_type) || empty($donation_date)) {
        throw new Exception('Please fill in all required fields');
    }

    // If member_id is empty string, set it to NULL for database
    if ($member_id === '') {
        $member_id = null;
    }

    $stmt = $conn->prepare("INSERT INTO donations (member_id, amount, donation_type, donation_date, notes) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$member_id, $amount, $donation_type, $donation_date, $notes]);

    $response['success'] = true;
    $response['message'] = 'Donation added successfully';

} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
}

header('Content-Type: application/json');
echo json_encode($response);