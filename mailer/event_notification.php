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
    

    $stmt = $conn->prepare("SELECT first_name, last_name, email FROM members WHERE status = 'active' AND email IS NOT NULL");
    $stmt->execute();
    $members = $stmt->fetchAll();
    
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
        

        foreach ($members as $member) {
            $mail->clearAddresses();
            $mail->addAddress($member['email'], $member['first_name'] . ' ' . $member['last_name']);
            $mail->send();
        }
        
        return true;
    } catch (Exception $e) {
        throw new Exception('Error sending email notifications: ' . $mail->ErrorInfo);
    }
}