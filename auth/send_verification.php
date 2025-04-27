<?php
require_once '../db/connection.php';
require_once '../mailer/_credentials.php';
require_once '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    
    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email address');
    }


    $verification_code = sprintf('%06d', mt_rand(0, 999999));

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }


    $_SESSION['verification_code'] = $verification_code;
    $_SESSION['verification_email'] = $email;
    $_SESSION['verification_time'] = time();

    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host = SMTP_HOST;
    $mail->SMTPAuth = true;
    $mail->Username = SMTP_USERNAME;
    $mail->Password = SMTP_PASSWORD;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = SMTP_PORT;


    $mail->setFrom(SMTP_FROM_EMAIL, 'Mater Dolorosa Church');
    $mail->addAddress($email);

    $mail->isHTML(true);
    $mail->Subject = 'Verify Your Email Address';
    $mail->Body = "
        <h2>Email Verification</h2>
        <p>Thank you for registering with Mater Dolorosa Church. Please use the following verification code to complete your registration:</p>
        <h1 style='font-size: 32px; letter-spacing: 5px; color: #007bff;'>{$verification_code}</h1>
        <p>This code will expire in 10 minutes.</p>
        <p>If you didn't request this verification code, please ignore this email.</p>
    ";

    $mail->send();
    echo json_encode(['success' => true, 'message' => 'Verification code sent successfully']);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error sending verification code: ' . $e->getMessage()]);
}