<?php
session_start();
require_once '../db/connection.php';
require_once '../vendor/autoload.php';
require_once '_credentials.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['event_id'])) {
        throw new Exception('Event ID is required');
    }

    $event_id = $data['event_id'];

    $stmt = $conn->prepare("SELECT * FROM events WHERE id = ?");
    $stmt->execute([$event_id]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$event) {
        throw new Exception('Event not found');
    }


    $conn->beginTransaction();

    $stmt = $conn->query("SELECT DISTINCT id, email FROM users WHERE email IS NOT NULL");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $conn->query("
        SELECT DISTINCT m.id, m.email 
        FROM members m 
        LEFT JOIN users u ON m.email = u.email 
        WHERE m.email IS NOT NULL 
        AND u.email IS NULL
    ");
    $members = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $allRecipients = array_merge($users, $members);

    if (empty($allRecipients)) {
        throw new Exception('No recipients found');
    }

    // Check if database is PostgreSQL
    $isPostgres = (getenv('DATABASE_URL') !== false);
    
    // Use database-specific boolean value
    $send_email_value = $isPostgres ? true : 1;
    
    // Use RETURNING for PostgreSQL
    if ($isPostgres) {
        $stmt = $conn->prepare("
            INSERT INTO notifications (
                notification_type, subject, message, send_email, created_by, status
            ) VALUES (
                'event', ?, ?, ?, ?, 'pending'
            ) RETURNING id
        ");
    } else {
        $stmt = $conn->prepare("
            INSERT INTO notifications (
                notification_type, subject, message, send_email, created_by, status
            ) VALUES (
                'event', ?, ?, ?, ?, 'pending'
            )
        ");
    }

    $subject = "New Event: {$event['title']}";
    $message = "A new event has been created:\n\n" .
               "Title: {$event['title']}\n" .
               "Date: " . date('F j, Y g:i A', strtotime($event['start_datetime'])) . "\n" .
               "Location: {$event['location']}\n" .
               "Description: {$event['description']}";

    $stmt->execute([$subject, $message, $send_email_value, $_SESSION['user_id']]);
    
    if ($isPostgres) {
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $notification_id = (int)$result['id'];
    } else {
        $notification_id = (int)$conn->lastInsertId();
    }

    $stmt = $conn->prepare("
        INSERT INTO notification_recipients (
            notification_id, user_id, user_email
        ) VALUES (?, ?, ?)
    ");

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
            <h2 style='color: #6a1b9a;'>{$event['title']}</h2>
            <p style='color: #666;'><strong>New Event Announcement</strong></p>
            <div style='background: #f9f9f9; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                <p><strong>Date:</strong> " . date('F j, Y g:i A', strtotime($event['start_datetime'])) . "</p>
                <p><strong>Location:</strong> {$event['location']}</p>
                <p><strong>Description:</strong><br>" . nl2br(htmlspecialchars($event['description'])) . "</p>
                " . ($event['max_attendees'] ? "<p><strong>Maximum Attendees:</strong> {$event['max_attendees']}</p>" : "") . "
            </div>
            <hr>
            <p style='color: #888; font-size: 12px;'>
                This is an automated message from Mater Dolorosa Church. Please do not reply to this email.
            </p>
        </div>
    ";
    $mail->Body = $emailBody;
    $mail->AltBody = strip_tags($message);

    $successfulEmails = 0;
    foreach ($allRecipients as $recipient) {
        if (isset($recipient['id'])) {
            $stmt->execute([$notification_id, $recipient['id'], $recipient['email']]);
        }

        if (!empty($recipient['email'])) {
            try {
                $mail->clearAddresses();
                $mail->addAddress($recipient['email']);
                $mail->send();
                $successfulEmails++;

                if (isset($recipient['id'])) {
                    // Use database-specific boolean value
                    $email_sent_value = $isPostgres ? true : 1;
                    $stmt = $conn->prepare("
                        UPDATE notification_recipients 
                        SET email_sent = ? 
                        WHERE notification_id = ? AND user_id = ?
                    ");
                    $stmt->execute([$email_sent_value, $notification_id, $recipient['id']]);
                }
            } catch (Exception $e) {
                error_log("Failed to send email to {$recipient['email']}: " . $e->getMessage());
            }
        }
    }

    $stmt = $conn->prepare("UPDATE notifications SET status = 'sent' WHERE id = ?");
    $stmt->execute([$notification_id]);

    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => "Notification sent successfully. $successfulEmails emails sent.",
        'email_count' => $successfulEmails
    ]);

} catch (Exception $e) {
    if ($conn && $conn->inTransaction()) {
        $conn->rollBack();
    }
    error_log("Error in notify_all_users.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>