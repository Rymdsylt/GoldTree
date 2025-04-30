<?php
require_once '_credentials.php';
require_once '../db/connection.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

function sendEventNotification($eventId) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT * FROM events WHERE id = ?");
    $stmt->execute([$eventId]);
    $event = $stmt->fetch();
    
    if (!$event) {
        throw new Exception('Event not found');
    }

    // Get all users emails
    $stmt = $conn->query("SELECT DISTINCT email FROM users WHERE email IS NOT NULL");
    $users = $stmt->fetchAll();

    // Get all members emails that are not in users table
    $stmt = $conn->query("
        SELECT DISTINCT email 
        FROM members 
        WHERE status = 'active' 
        AND email IS NOT NULL 
        AND email NOT IN (SELECT email FROM users WHERE email IS NOT NULL)
    ");
    $members = $stmt->fetchAll();
    
    // Combine all recipients
    $allRecipients = array_merge($users, $members);
    
    if (empty($allRecipients)) {
        throw new Exception('No recipients found to notify');
    }
    
    $mail = new PHPMailer(true);
    
    try {
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = SMTP_SECURE;
        $mail->Port = SMTP_PORT;
        
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->isHTML(true);
        $mail->Subject = 'New Event: ' . $event['title'];
        
        $startDate = date('F j, Y g:i A', strtotime($event['start_datetime']));
        $endDate = date('F j, Y g:i A', strtotime($event['end_datetime']));
        
        $emailBody = "
        <h2>New Event: {$event['title']}</h2>
        <p><strong>Date:</strong> {$startDate} - {$endDate}</p>
        <p><strong>Location:</strong> {$event['location']}</p>
        <p><strong>Type:</strong> " . ucfirst($event['event_type']) . "</p>
        <p><strong>Description:</strong><br>{$event['description']}</p>";
        
        if ($event['max_attendees']) {
            $emailBody .= "<p><strong>Maximum Attendees:</strong> {$event['max_attendees']}</p>";
        }
        
        if ($event['registration_deadline']) {
            $deadline = date('F j, Y g:i A', strtotime($event['registration_deadline']));
            $emailBody .= "<p><strong>Registration Deadline:</strong> {$deadline}</p>";
        }
        
        $mail->Body = $emailBody;

        $successCount = 0;
        foreach ($allRecipients as $recipient) {
            if (!empty($recipient['email'])) {
                try {
                    $mail->clearAddresses();
                    $mail->addAddress($recipient['email']);
                    $mail->send();
                    $successCount++;
                } catch (Exception $e) {
                    error_log("Failed to send email to {$recipient['email']}: " . $e->getMessage());
                }
            }
        }
        
        if ($successCount === 0) {
            throw new Exception('Failed to send any email notifications');
        }
        
        return true;
        
    } catch (Exception $e) {
        throw new Exception('Error sending email notifications: ' . $mail->ErrorInfo);
    }
}