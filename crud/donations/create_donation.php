<?php
require_once '../../db/connection.php';
require_once '../../auth/login_status.php';

header('Content-Type: application/json');
$response = ['success' => false, 'message' => ''];

try {
    $donor_type = $_POST['donor_type'] ?? 'member';
    $member_id = $donor_type === 'member' ? ($_POST['member_id'] ?? null) : null;
    $donor_name = $donor_type === 'non-member' ? ($_POST['donor_name'] ?? null) : null;
    $amount = $_POST['amount'] ?? null;
    $type = $_POST['donation_type'] ?? null; // Changed from 'type' to 'donation_type'
    $donation_date = $_POST['donation_date'] ?? null;
    $notes = $_POST['notes'] ?? '';

    if ($donor_type === 'member' && empty($member_id)) {
        throw new Exception('Member selection is required for member donations');
    }

    if (empty($amount) || empty($type) || empty($donation_date)) {
        throw new Exception('Amount, type, and date are required fields');
    }

    $stmt = $conn->prepare("INSERT INTO donations (member_id, donor_name, amount, donation_type, donation_date, notes) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$member_id, $donor_name, $amount, $type, $donation_date, $notes]);

    $response['success'] = true;
    $response['message'] = 'Donation added successfully';

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);