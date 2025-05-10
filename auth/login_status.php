<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$current_page = basename($_SERVER['PHP_SELF']);
$public_pages = ['register.php', 'login.php', 'forgot_password.php'];


if (isset($_SESSION['user_id']) && $current_page === 'login.php') {
    header("Location: /GoldTree/dashboard.php");
    exit();
}


if (in_array($current_page, $public_pages)) {
    return;
}


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