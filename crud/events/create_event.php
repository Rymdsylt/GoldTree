<?php
require_once '../../db/connection.php';
require_once '../../vendor/autoload.php';
require_once '../../mailer/_credentials.php';
require_once '../../auth/login_status.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    exit;
}

try {

    $required_fields = ['title', 'start_datetime', 'end_datetime', 'event_type', 'location'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("$field is required");
        }
    }

    $title = $_POST['title'];
    $description = !empty($_POST['description']) ? $_POST['description'] : null;
    $start_datetime = $_POST['start_datetime'];
    $end_datetime = $_POST['end_datetime'];
    $event_type = $_POST['event_type'];
    $location = $_POST['location'];
    $max_attendees = !empty($_POST['max_attendees']) ? $_POST['max_attendees'] : null;
    $send_notifications = isset($_POST['send_notifications']) && $_POST['send_notifications'] === 'on';
    $send_all_emails = isset($_POST['send_all_emails']) && $_POST['send_all_emails'] === 'on';
    $created_by = $_SESSION['user_id'];

    $image = null;
    if (isset($_FILES['event_image']) && $_FILES['event_image']['error'] === UPLOAD_ERR_OK) {
        $image = file_get_contents($_FILES['event_image']['tmp_name']);
    }

    $conn->beginTransaction();
    
    // Check if database is PostgreSQL
    $isPostgres = (getenv('DATABASE_URL') !== false);

    // Use RETURNING for PostgreSQL, lastInsertId for MySQL
    if ($isPostgres) {
        $stmt = $conn->prepare("
            INSERT INTO events (
                title, description, start_datetime, end_datetime, 
                event_type, location, max_attendees,
                image, created_by
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?) RETURNING id
        ");
        $result = $stmt->execute([
            $title,
            $description,
            $start_datetime,
            $end_datetime,
            $event_type,
            $location,
            $max_attendees,
            $image,
            $created_by
        ]);
        if (!$result) {
            throw new Exception('Failed to insert event: ' . implode(', ', $stmt->errorInfo()));
        }
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $event_id = (int)$result['id'];
    } else {
        $stmt = $conn->prepare("
            INSERT INTO events (
                title, description, start_datetime, end_datetime, 
                event_type, location, max_attendees,
                image, created_by
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $result = $stmt->execute([
            $title,
            $description,
            $start_datetime,
            $end_datetime,
            $event_type,
            $location,
            $max_attendees,
            $image,
            $created_by
        ]);
        if (!$result) {
            throw new Exception('Failed to insert event: ' . implode(', ', $stmt->errorInfo()));
        }
        $event_id = (int)$conn->lastInsertId();
    }

    // I cant even understand my own code xd
    if (!empty($_POST['assigned_staff'])) {
        $staffIds = explode(',', $_POST['assigned_staff']);
        $assignStmt = $conn->prepare("INSERT INTO event_assignments (event_id, user_id) VALUES (?, ?)");
        foreach ($staffIds as $userId) {
            $assignStmt->execute([$event_id, $userId]);
        }
    }

    if ($send_notifications || $send_all_emails) {
     
        $notif_stmt = $conn->prepare("
            INSERT INTO notifications (
                notification_type, subject, message, send_email, created_by, status
            ) VALUES (
                'event', ?, ?, ?, ?, 'pending'
            )
        ");

        $subject = "New Event: $title";
        $message = "A new event has been created:\n\n" .
                  "Title: $title\n" .
                  "Date: " . date('F j, Y g:i A', strtotime($start_datetime)) . "\n" .
                  "Location: $location\n" .
                  "Description: $description";

        // Use database-specific boolean value
        $send_email_value = $isPostgres ? ($send_all_emails ? true : false) : ($send_all_emails ? 1 : 0);
        
        // Use RETURNING for PostgreSQL
        if ($isPostgres) {
            $notif_stmt = $conn->prepare("
                INSERT INTO notifications (
                    notification_type, subject, message, send_email, created_by, status
                ) VALUES (
                    'event', ?, ?, ?, ?, 'pending'
                ) RETURNING id
            ");
            $notif_stmt->execute([$subject, $message, $send_email_value, $_SESSION['user_id']]);
            $result = $notif_stmt->fetch(PDO::FETCH_ASSOC);
            $notification_id = (int)$result['id'];
        } else {
            $notif_stmt->execute([$subject, $message, $send_email_value, $_SESSION['user_id']]);
            $notification_id = (int)$conn->lastInsertId();
        }

     
        $email_recipients = array();
        
      
        $user_stmt = $conn->query("SELECT id, email FROM users WHERE email IS NOT NULL");
        while ($user = $user_stmt->fetch()) {
            if (!empty($user['email'])) {
                $email_recipients[$user['email']] = [
                    'id' => $user['id'],
                    'type' => 'user'
                ];
            }
        }

 
        if ($send_all_emails) {
            $member_stmt = $conn->query("
                SELECT m.id, m.email 
                FROM members m 
                LEFT JOIN users u ON m.email = u.email 
                WHERE m.email IS NOT NULL 
                AND u.email IS NULL
            ");
            while ($member = $member_stmt->fetch()) {
                if (!empty($member['email'])) {
                    $email_recipients[$member['email']] = [
                        'id' => $member['id'],
                        'type' => 'member'
                    ];
                }
            }
        }

        if (!empty($email_recipients)) {
            $recipients_stmt = $conn->prepare("
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
                    <h2 style='color: #6a1b9a;'>{$title}</h2>
                    <p style='color: #666;'><strong>New Event Announcement</strong></p>
                    <div style='background: #f9f9f9; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                        <p><strong>Date:</strong> " . date('F j, Y g:i A', strtotime($start_datetime)) . "</p>
                        <p><strong>Location:</strong> {$location}</p>
                        <p><strong>Description:</strong><br>" . nl2br(htmlspecialchars($description)) . "</p>
                        " . ($max_attendees ? "<p><strong>Maximum Attendees:</strong> {$max_attendees}</p>" : "") . "
                    </div>
                    <hr>
                    <p style='color: #888; font-size: 12px;'>
                        This is an automated message from Mater Dolorosa Church. Please do not reply to this email.
                    </p>
                </div>
            ";
            $mail->Body = $emailBody;
            $mail->AltBody = strip_tags($message);

            foreach ($email_recipients as $email => $recipient) {
          
                if ($recipient['type'] === 'user') {
                    $recipients_stmt->execute([$notification_id, $recipient['id'], $email]);
                }

         
                if ($send_all_emails) {
                    try {
                        $mail->clearAddresses();
                        $mail->addAddress($email);
                        $mail->send();

               
                        if ($recipient['type'] === 'user') {
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
                        error_log("Failed to send email to {$email}: " . $e->getMessage());
                    }
                }
            }
        }
    }

    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Event created successfully',
        'event_id' => $event_id
    ]);

} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    error_log('Event creation error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error creating event: ' . $e->getMessage()
    ]);
}
?>