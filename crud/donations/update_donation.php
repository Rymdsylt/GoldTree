<?php
session_start();
require_once '../../db/connection.php';
require_once '../../auth/login_status.php';

header('Content-Type: application/json');
$response = ['success' => false, 'message' => ''];

try {

    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Unauthorized access');
    }

    $stmt = $conn->prepare("SELECT admin_status FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if (!$user || $user['admin_status'] != 1) {
        throw new Exception('Unauthorized access');
    }

 
    $donation_id = $_POST['donation_id'] ?? null;
    $donor_type = $_POST['donor_type'] ?? 'member';
    $member_id = $donor_type === 'member' ? ($_POST['member_id'] ?? null) : null;
    $donor_name = $donor_type === 'non-member' ? ($_POST['donor_name'] ?? null) : null;
    $amount = $_POST['amount'] ?? null;
    $donation_type = $_POST['donation_type'] ?? null;
    $donation_date = $_POST['donation_date'] ?? null;
    $notes = $_POST['notes'] ?? '';

    if (!$donation_id) {
        throw new Exception('Donation ID is required');
    }

    if ($donor_type === 'member' && empty($member_id)) {
        throw new Exception('Member selection is required for member donations');
    }

    if (empty($amount) || empty($donation_type) || empty($donation_date)) {
        throw new Exception('Amount, type, and date are required fields');
    }

    $stmt = $conn->prepare("UPDATE donations SET 
        member_id = ?,
        donor_name = ?,
        amount = ?,
        donation_type = ?,
        donation_date = ?,
        notes = ?
        WHERE id = ?");
        
    $stmt->execute([$member_id, $donor_name, $amount, $donation_type, $donation_date, $notes, $donation_id]);
    
    if ($stmt->rowCount() === 0) {
        throw new Exception('No donation found with the given ID');
    }

    if (isset($_POST['send_notification']) && $_POST['send_notification'] === 'on') {
        $notificationTitle = "Donation Updated";
        $notificationMessage = sprintf(
            "A %s donation of ₱%s has been updated%s.", 
            $donation_type, 
            number_format($amount, 2),
            $donor_type === 'member' ? " from a member" : ""
        );
        
        // Check if database is PostgreSQL
        $isPostgres = (getenv('DATABASE_URL') !== false);
        
        // Use database-specific timestamp function and RETURNING
        if ($isPostgres) {
            $stmt = $conn->prepare("INSERT INTO notifications (notification_type, subject, message, created_at) VALUES (?, ?, ?, CURRENT_TIMESTAMP) RETURNING id");
            $stmt->execute(['donation', $notificationTitle, $notificationMessage]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $notification_id = (int)$result['id'];
        } else {
            $stmt = $conn->prepare("INSERT INTO notifications (notification_type, subject, message, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->execute(['donation', $notificationTitle, $notificationMessage]);
            $notification_id = (int)$conn->lastInsertId();
        }
        

        $stmt = $conn->prepare("INSERT INTO notification_recipients (notification_id, user_id)
            SELECT ?, id FROM users WHERE admin_status = 1");
        $stmt->execute([$notification_id]);
    }

    $response['success'] = true;
    $response['message'] = 'Donation updated successfully';

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);