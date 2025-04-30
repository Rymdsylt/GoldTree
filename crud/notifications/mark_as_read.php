<?php
session_start();
require_once '../../db/connection.php';
require_once '../../mailer/_credentials.php';
require_once '../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

try {
    $notificationId = $data['id'];
    $sendEmail = isset($data['sendEmail']) ? $data['sendEmail'] : false;

    // Mark notification as read
    $stmt = $conn->prepare("UPDATE notification_recipients SET is_read = true WHERE notification_id = ?");
    $stmt->execute([$notificationId]);

    if ($sendEmail) {
        // Get notification details
        $stmt = $conn->prepare("
            SELECT n.*, u.email as recipient_email 
            FROM notifications n 
            JOIN notification_recipients nr ON n.id = nr.notification_id 
            JOIN users u ON nr.user_id = u.id 
            WHERE n.id = ?
        ");
        $stmt->execute([$notificationId]);
        $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($notifications)) {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = SMTP_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = SMTP_USERNAME;
            $mail->Password = SMTP_PASSWORD;
            $mail->SMTPSecure = SMTP_SECURE;
            $mail->Port = SMTP_PORT;
            $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
            
            foreach ($notifications as $notification) {
                $mail->clearAddresses();
                $mail->addAddress($notification['recipient_email']);
                $mail->Subject = $notification['subject'];
                $mail->Body = $notification['message'];
                $mail->send();

                // Update email_sent status
                $stmt = $conn->prepare("
                    UPDATE notification_recipients 
                    SET email_sent = true 
                    WHERE notification_id = ? AND user_id = (
                        SELECT id FROM users WHERE email = ?
                    )
                ");
                $stmt->execute([$notificationId, $notification['recipient_email']]);
            }
        }
    }

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>