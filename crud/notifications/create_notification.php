<?php
session_start();
require_once '../../db/connection.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

error_log("POST data received: " . print_r($_POST, true));

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

try {
    
    $required_fields = ['notification_type', 'subject', 'message', 'recipients'];
    $missing_fields = [];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            $missing_fields[] = $field;
        }
    }
    
    if (!empty($missing_fields)) {
        throw new Exception("Missing required fields: " . implode(', ', $missing_fields));
    }


    $notification_type = $_POST['notification_type'];
    $subject = $_POST['subject'];
    $message = $_POST['message'];
    $send_email = isset($_POST['send_email']) ? 1 : 0;
    $recipients = !empty($_POST['recipients']) ? explode(',', $_POST['recipients']) : [];
    
    error_log("Processing data - Type: $notification_type, Recipients: " . print_r($recipients, true));

    $conn->beginTransaction();


    $stmt = $conn->prepare("
        INSERT INTO notifications (
            notification_type, subject, message, send_email, created_by, status
        ) VALUES (?, ?, ?, ?, ?, 'pending')
    ");
    
    if (!$stmt->execute([$notification_type, $subject, $message, $send_email, $_SESSION['user_id']])) {
        throw new Exception("Error inserting notification: " . implode(", ", $stmt->errorInfo()));
    }
    
    $notification_id = $conn->lastInsertId();
    error_log("Notification created with ID: $notification_id");


    $stmt = $conn->prepare("
        INSERT INTO notification_recipients (notification_id, user_id) 
        VALUES (?, ?)
    ");

    foreach ($recipients as $user_id) {
        if (!empty($user_id)) {
            if (!$stmt->execute([$notification_id, $user_id])) {
                throw new Exception("Error adding recipient $user_id: " . implode(", ", $stmt->errorInfo()));
            }
        }
    }

 
    if ($send_email) {
        require_once '../../vendor/autoload.php';
        require_once '../../mailer/_credentials.php';


        $placeholders = str_repeat('?,', count($recipients) - 1) . '?';
        $stmt = $conn->prepare("
            SELECT id, username, email 
            FROM users 
            WHERE id IN ($placeholders) AND email IS NOT NULL
        ");
        $stmt->execute($recipients);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($users)) {
            try {
                $mail = new PHPMailer\PHPMailer\PHPMailer(true);
                $mail->isSMTP();
                $mail->Host = SMTP_HOST;
                $mail->SMTPAuth = true;
                $mail->Username = SMTP_USERNAME;
                $mail->Password = SMTP_PASSWORD;
                $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = SMTP_PORT;
                
                $mail->setFrom(SMTP_FROM_EMAIL, 'Mater Dolorosa Church');
                $mail->isHTML(true);
                $mail->Subject = $subject;

                $emailBody = "
                    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                        <h2 style='color: #6a1b9a;'>" . htmlspecialchars($subject) . "</h2>
                        <p style='color: #666;'><strong>Type:</strong> " . ucfirst($notification_type) . "</p>
                        <div style='background: #f9f9f9; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                            " . nl2br(htmlspecialchars($message)) . "
                        </div>
                        <hr>
                        <p style='color: #888; font-size: 12px;'>
                            This is an automated message from Mater Dolorosa Church. Please do not reply to this email.
                        </p>
                    </div>
                ";
                $mail->Body = $emailBody;
                $mail->AltBody = strip_tags($message);

                foreach ($users as $user) {
                    if (!empty($user['email'])) {
                        try {
                            $mail->clearAddresses();
                            $mail->addAddress($user['email'], $user['username']);
                            $mail->send();

                            $stmt = $conn->prepare("
                                UPDATE notification_recipients 
                                SET email_sent = 1 
                                WHERE notification_id = ? AND user_id = ?
                            ");
                            $stmt->execute([$notification_id, $user['id']]);
                        } catch (Exception $e) {
                            error_log("Failed to send email to {$user['email']}: " . $e->getMessage());
                        }
                    }
                }
            } catch (Exception $e) {
                error_log("Email setup error: " . $e->getMessage());
            }
        }
    }
    $stmt = $conn->prepare("
        UPDATE notifications 
        SET status = 'sent' 
        WHERE id = ?
    ");
    $stmt->execute([$notification_id]);

    $conn->commit();
    error_log("Transaction committed successfully");

    if ($send_email) {
        $stmt = $conn->prepare("
            SELECT COUNT(*) as email_count 
            FROM notification_recipients 
            WHERE notification_id = ? AND email_sent = 1
        ");
        $stmt->execute([$notification_id]);
        $email_count = $stmt->fetch(PDO::FETCH_ASSOC)['email_count'];
    }

    echo json_encode([
        'success' => true, 
        'message' => 'Notification sent successfully' . 
            ($send_email ? " ($email_count emails sent)" : ''),
        'notification_id' => $notification_id,
        'recipient_count' => count($recipients),
        'email_count' => $send_email ? $email_count : 0
    ]);

} catch (Exception $e) {
    error_log("Error in create_notification.php: " . $e->getMessage());
    if ($conn && $conn->inTransaction()) {
        $conn->rollBack();
        error_log("Transaction rolled back");
    }
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage(),
        'debug' => [
            'POST' => $_POST,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]
    ]);
}
?>