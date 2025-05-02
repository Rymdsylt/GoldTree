<?php
require_once '../../db/connection.php';
require_once '../../auth/login_status.php';

header('Content-Type: application/json');
$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn->beginTransaction();

        $donor_type = $_POST['donor_type'] ?? 'member';
        $member_id = $donor_type === 'member' ? ($_POST['member_id'] ?? null) : null;
        $donor_name = $donor_type === 'non-member' ? ($_POST['donor_name'] ?? 'Anonymous') : null;
        $amount = $_POST['amount'] ?? null;
        $type = $_POST['donation_type'] ?? null; 
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

        // Handle notification if checkbox was checked
        if (isset($_POST['send_notification']) && $_POST['send_notification'] === 'on') {
            // Get donor name for notification
            $display_name = 'Anonymous';
            if ($donor_type === 'member' && $member_id) {
                // Get member's full name
                $member_stmt = $conn->prepare("SELECT CONCAT(first_name, ' ', last_name) as full_name FROM members WHERE id = ?");
                $member_stmt->execute([$member_id]);
                $member = $member_stmt->fetch(PDO::FETCH_ASSOC);
                if ($member) {
                    $display_name = $member['full_name'];
                }
            } else if ($donor_type === 'non-member' && !empty($_POST['donor_name'])) {
                $display_name = $_POST['donor_name'];
            }

            // Create notification
            $notification_stmt = $conn->prepare("INSERT INTO notifications (notification_type, subject, message, send_email, created_by) VALUES (?, ?, ?, ?, ?)");
            
            $subject = "New Donation Received";
            $message = "A new " . $type . " donation of ₱" . number_format($amount, 2) . " has been received from " . $display_name;
            $message .= " on " . date('F j, Y', strtotime($donation_date));
            
            $notification_stmt->execute(['donation', $subject, $message, true, $_SESSION['user_id'] ?? null]);
            $notification_id = $conn->lastInsertId();
            
            // Add all users as recipients
            $user_stmt = $conn->query("SELECT id, email FROM users");
            $recipient_stmt = $conn->prepare("INSERT INTO notification_recipients (notification_id, user_id, user_email) VALUES (?, ?, ?)");
            
            while ($user = $user_stmt->fetch(PDO::FETCH_ASSOC)) {
                $recipient_stmt->execute([$notification_id, $user['id'], $user['email']]);
            }
        }

        $conn->commit();
        $response['success'] = true;
        $response['message'] = 'Donation added successfully';
    } catch (Exception $e) {
        $conn->rollBack();
        $response['message'] = $e->getMessage();
    }
} else {
    $response['message'] = 'Invalid request method';
}

echo json_encode($response);
?>