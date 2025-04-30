<?php
require_once '../../db/connection.php';
require_once '../../vendor/autoload.php';
require_once '../../mailer/_credentials.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
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
    $registration_deadline = !empty($_POST['registration_deadline']) ? $_POST['registration_deadline'] : null;
    $send_notifications = isset($_POST['send_notifications']) && $_POST['send_notifications'] === 'on';
    $send_all_emails = isset($_POST['send_all_emails']) && $_POST['send_all_emails'] === 'on';
    $created_by = $_SESSION['user_id'];

    $image = null;
    if (isset($_FILES['event_image']) && $_FILES['event_image']['error'] === UPLOAD_ERR_OK) {
        $image = file_get_contents($_FILES['event_image']['tmp_name']);
    }

    $conn->beginTransaction();

    
    $stmt = $conn->prepare("
        INSERT INTO events (
            title, description, start_datetime, end_datetime, 
            event_type, location, max_attendees, registration_deadline,
            image, created_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $result = $stmt->execute([
        $title,
        $description,
        $start_datetime,
        $end_datetime,
        $event_type,
        $location,
        $max_attendees,
        $registration_deadline,
        $image,
        $created_by
    ]);

    if (!$result) {
        throw new Exception('Failed to insert event: ' . implode(', ', $stmt->errorInfo()));
    }

    $event_id = $conn->lastInsertId();

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

        $notif_stmt->execute([$subject, $message, $send_all_emails ? 1 : 0, $_SESSION['user_id']]);
        $notification_id = $conn->lastInsertId();

     
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
                        " . ($registration_deadline ? "<p><strong>Registration Deadline:</strong> " . date('F j, Y g:i A', strtotime($registration_deadline)) . "</p>" : "") . "
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
                            $stmt = $conn->prepare("
                                UPDATE notification_recipients 
                                SET email_sent = 1 
                                WHERE notification_id = ? AND user_id = ?
                            ");
                            $stmt->execute([$notification_id, $recipient['id']]);
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