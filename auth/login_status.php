<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$current_page = basename($_SERVER['PHP_SELF']);
$public_pages = ['register.php', 'login.php', 'forgot_password.php'];

if (in_array($current_page, $public_pages)) {
    if ($current_page === 'login.php' && isset($_SESSION['user_id'])) {
        header("Location: /GoldTree/dashboard.php");
        exit();
    }
    return;
}

if (!isset($_SESSION['user_id'])) {
    header("Location: /GoldTree/login.php");
    exit();
}

if (!isset($_SESSION['privacy_checked']) && 
    strpos($_SERVER['REQUEST_URI'], '/crud/') === false && 
    strpos($_SERVER['REQUEST_URI'], '/admin/') === false &&
    strpos($_SERVER['REQUEST_URI'], '/auth/') === false) {
    
    $privacyStmt = $conn->prepare("SELECT privacy_agreement FROM users WHERE id = ?");
    $privacyStmt->execute([$_SESSION['user_id']]);
    $privacyStatus = $privacyStmt->fetch();
    

    $_SESSION['privacy_checked'] = true;
    $_SESSION['privacy_agreed'] = $privacyStatus['privacy_agreement'] === 1;
}

$username = $_SESSION['username'] ?? 'User';
?>