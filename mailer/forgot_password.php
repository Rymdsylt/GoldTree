<?php
require_once '_credentials.php';
require_once '../vendor/autoload.php';
require_once '../db/connection.php';

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

    // Check if email exists in database
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->rowCount() === 0) {
        throw new Exception('No account found with this email address');
    }

    // Generate 6-digit code
    $verification_code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    
    // Store in session
    session_start();
    $_SESSION['reset_code'] = $verification_code;
    $_SESSION['reset_email'] = $email;
    $_SESSION['reset_time'] = time();

    // Send email
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = SMTP_HOST;
    $mail->SMTPAuth = true;
    $mail->Username = SMTP_USERNAME;
    $mail->Password = SMTP_PASSWORD;
    $mail->SMTPSecure = SMTP_SECURE;
    $mail->Port = SMTP_PORT;

    $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
    $mail->addAddress($email);

    $mail->isHTML(true);
    $mail->Subject = 'Password Reset Verification Code';
    $mail->Body = "
        <h2>Password Reset Request</h2>
        <p>Your verification code is: <strong>{$verification_code}</strong></p>
        <p>This code will expire in 10 minutes.</p>
        <p>If you did not request this password reset, please ignore this email.</p>
    ";

    $mail->send();
    echo json_encode(['success' => true, 'message' => 'Verification code has been sent to your email']);

} catch(Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>