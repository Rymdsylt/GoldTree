<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$current_page = basename($_SERVER['PHP_SELF']);

if ($current_page !== 'register.php' && $current_page !== 'login.php' && $current_page !== 'forgot_password.php') {
    if (!isset($_SESSION['user_id'])) {
        if (strpos($_SERVER['REQUEST_URI'], '/crud/') !== false || strpos($_SERVER['REQUEST_URI'], '/admin/') !== false) {
            header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'User not logged in']);
            exit();
        } else {
            header("Location: /GoldTree/login.php");
            exit();
        }
    }
}

$username = $_SESSION['username'] ?? 'User';
?>