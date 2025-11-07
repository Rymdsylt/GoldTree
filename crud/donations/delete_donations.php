<?php
require_once '../../db/connection.php';
require_once '../../auth/login_status.php';
session_start();

header('Content-Type: application/json');


if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

$stmt = $conn->prepare("SELECT admin_status FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user || $user['admin_status'] != 1) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}


$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id']) || !is_numeric($data['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid donation ID']);
    exit();
}

try {
    $conn->beginTransaction();
    
  
    $stmt = $conn->prepare("SELECT id, amount, donation_type FROM donations WHERE id = ?");
    $stmt->execute([$data['id']]);
    $donation = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$donation) {
        throw new Exception('Donation not found');
    }

    
    $stmt = $conn->prepare("DELETE FROM donations WHERE id = ?");
    $stmt->execute([$data['id']]);

    $notificationTitle = "Donation Deleted";
    $notificationMessage = sprintf(
        "A %s donation of ₱%s has been deleted.", 
        $donation['donation_type'],
        number_format($donation['amount'], 2)
    );
    
    // Check if database is PostgreSQL
    $isPostgres = (getenv('DATABASE_URL') !== false);
    
    // Use database-specific timestamp function and RETURNING
    if ($isPostgres) {
        $stmt = $conn->prepare("INSERT INTO notifications (notification_type, subject, message, created_at) VALUES ('donation', ?, ?, CURRENT_TIMESTAMP) RETURNING id");
        $stmt->execute([$notificationTitle, $notificationMessage]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $notification_id = (int)$result['id'];
    } else {
        $stmt = $conn->prepare("INSERT INTO notifications (notification_type, subject, message, created_at) VALUES ('donation', ?, ?, NOW())");
        $stmt->execute([$notificationTitle, $notificationMessage]);
        $notification_id = (int)$conn->lastInsertId();
    }
    

    $stmt = $conn->prepare("INSERT INTO notification_recipients (notification_id, user_id)
        SELECT ?, id FROM users WHERE admin_status = 1");
    $stmt->execute([$notification_id]);

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Donation deleted successfully']);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}