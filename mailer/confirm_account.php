<?php
require_once '_credentials.php';
require_once '../vendor/autoload.php';
require_once '../db/connection.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

try {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    
    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email address');
    }

    session_start();
    
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->rowCount() > 0) {
        throw new Exception('Email already registered');
    }
    $attempt_key = 'email_verify_attempts_' . md5($email);
    $last_attempt_key = 'last_email_verify_' . md5($email);
    
    if (isset($_SESSION[$attempt_key]) && $_SESSION[$attempt_key] >= 3) {
        if (time() - $_SESSION[$last_attempt_key] < 1800) {
            throw new Exception('Too many attempts. Please try again in ' . 
                ceil((1800 - (time() - $_SESSION[$last_attempt_key])) / 60) . ' minutes');
        } else {
            $_SESSION[$attempt_key] = 0;
        }
    }

    if (isset($_SESSION[$last_attempt_key]) && (time() - $_SESSION[$last_attempt_key] < 120)) {
        throw new Exception('Please wait ' . 
            ceil(120 - (time() - $_SESSION[$last_attempt_key])) . ' seconds before requesting another code');
    }

 
    $_SESSION[$attempt_key] = isset($_SESSION[$attempt_key]) ? $_SESSION[$attempt_key] + 1 : 1;
    $_SESSION[$last_attempt_key] = time();


    $verification_code = sprintf('%06d', mt_rand(0, 999999));
    
    $_SESSION['verification_code'] = $verification_code;
    $_SESSION['verification_email'] = $email;
    $_SESSION['verification_time'] = time();

    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = SMTP_HOST;
    $mail->SMTPAuth = true;
    $mail->Username = SMTP_USERNAME;
    $mail->Password = SMTP_PASSWORD;
    $mail->SMTPSecure = SMTP_SECURE;
    $mail->Port = SMTP_PORT;

    $mail->SMTPKeepAlive = true; 
    $mail->Timeout = 10; 
    $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );

    $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
    $mail->addAddress($email);

    $mail->isHTML(true);
    $mail->Subject = 'Email Verification Code';
    $mail->Body = "
        <h2>Email Verification</h2>
        <p>Your verification code is: <strong>{$verification_code}</strong></p>
        <p>This code will expire in 10 minutes.</p>
        <p>If you did not request this code, please ignore this email.</p>
    ";

    $mail->send();
    echo json_encode(['status' => 'success', 'message' => 'Verification code sent successfully. Valid for 10 minutes. Check your inbox or spam folder.']);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error sending verification code: ' . $e->getMessage()]);
}

if (isset($mail) && $mail->getSMTPInstance()) {
    $mail->getSMTPInstance()->quit();
}