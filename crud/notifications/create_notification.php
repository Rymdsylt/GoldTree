<?php
// Start output buffering to prevent any unexpected output
ob_start();

session_start();
require_once '../../db/connection.php';

// Disable error display (outputs before JSON)
ini_set('display_errors', 0);
error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
ini_set('log_errors', 1);

// Check if database is PostgreSQL
$isPostgres = (getenv('DATABASE_URL') !== false);

// Clear any output
ob_clean();

header('Content-Type: application/json');

error_log("POST data received: " . print_r($_POST, true));

if (!isset($_SESSION['user_id'])) {
    ob_clean();
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
    // Use database-specific boolean value
    $send_email = isset($_POST['send_email']) && $_POST['send_email'];
    $send_email_value = $isPostgres ? ($send_email ? true : false) : ($send_email ? 1 : 0);
    $recipients = !empty($_POST['recipients']) ? explode(',', $_POST['recipients']) : [];
    
    error_log("Processing data - Type: $notification_type, Recipients: " . print_r($recipients, true));

    $conn->beginTransaction();

    $stmt = $conn->prepare("
        INSERT INTO notifications (
            notification_type, subject, message, send_email, created_by, status
        ) VALUES (?, ?, ?, ?, ?, 'pending')
    ");
    
    if (!$stmt->execute([$notification_type, $subject, $message, $send_email_value, $_SESSION['user_id']])) {
        throw new Exception("Error inserting notification: " . implode(", ", $stmt->errorInfo()));
    }
    
    // Get last insert ID - PostgreSQL needs sequence name or lastval()
    if ($isPostgres) {
        // For PostgreSQL, use RETURNING id in INSERT or lastval()
        // Since we already inserted, use lastval()
        $stmt = $conn->query("SELECT lastval()");
        $notification_id = (int)$stmt->fetchColumn();
    } else {
        $notification_id = (int)$conn->lastInsertId();
    }
    
    if (!$notification_id) {
        throw new Exception("Failed to get notification ID after insert");
    }
    
    error_log("Notification created with ID: $notification_id");


    $placeholders = str_repeat('?,', count($recipients) - 1) . '?';
    $stmt = $conn->prepare("
        SELECT id, email 
        FROM users 
        WHERE id IN ($placeholders)
    ");
    $stmt->execute($recipients);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);


    $stmt = $conn->prepare("
        INSERT INTO notification_recipients (notification_id, user_id, user_email) 
        VALUES (?, ?, ?)
    ");

    foreach ($users as $user) {
        if (!$stmt->execute([$notification_id, $user['id'], $user['email']])) {
            throw new Exception("Error adding recipient {$user['id']}: " . implode(", ", $stmt->errorInfo()));
        }
    }

    if ($send_email) {
        require_once '../../vendor/autoload.php';
        require_once '../../mailer/_credentials.php';

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
                            $mail->addAddress($user['email']);
                            $mail->send();

                            // Use database-specific boolean value
                            $emailSentValue = $isPostgres ? true : 1;
                            $stmt = $conn->prepare("
                                UPDATE notification_recipients 
                                SET email_sent = ? 
                                WHERE notification_id = ? AND user_id = ?
                            ");
                            $stmt->execute([$emailSentValue, $notification_id, $user['id']]);
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
        // Use database-specific boolean check
        if ($isPostgres) {
            $stmt = $conn->prepare("
                SELECT COUNT(*) as email_count 
                FROM notification_recipients 
                WHERE notification_id = ? AND (email_sent = true OR email_sent = 1)
            ");
        } else {
            $stmt = $conn->prepare("
                SELECT COUNT(*) as email_count 
                FROM notification_recipients 
                WHERE notification_id = ? AND email_sent = 1
            ");
        }
        $stmt->execute([$notification_id]);
        $email_count = $stmt->fetch(PDO::FETCH_ASSOC)['email_count'] ?? 0;
    } else {
        $email_count = 0;
    }

    // Clear output buffer and send JSON
    ob_clean();
    echo json_encode([
        'success' => true, 
        'message' => 'Notification sent successfully' . 
            ($send_email ? " ($email_count emails sent)" : ''),
        'notification_id' => $notification_id,
        'recipient_count' => count($recipients),
        'email_count' => $send_email ? $email_count : 0
    ]);
    exit;

} catch (Exception $e) {
    error_log("Error in create_notification.php: " . $e->getMessage());
    if (isset($conn) && $conn && $conn->inTransaction()) {
        try {
            $conn->rollBack();
            error_log("Transaction rolled back");
        } catch (Exception $rollbackError) {
            error_log("Error rolling back transaction: " . $rollbackError->getMessage());
        }
    }
    
    // Clear output buffer and send error JSON
    ob_clean();
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
    exit;
} catch (PDOException $e) {
    error_log("PDO Error in create_notification.php: " . $e->getMessage());
    if (isset($conn) && $conn && $conn->inTransaction()) {
        try {
            $conn->rollBack();
        } catch (Exception $rollbackError) {
            error_log("Error rolling back transaction: " . $rollbackError->getMessage());
        }
    }
    
    // Clear output buffer and send error JSON
    ob_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Database error: ' . $e->getMessage()
    ]);
    exit;
}
?>