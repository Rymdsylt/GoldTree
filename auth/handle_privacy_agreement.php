<?php
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/../db/connection.php';

init_session();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

if (!isset($_POST['agreed'])) {
    echo json_encode(['success' => false, 'message' => 'Missing agreement status']);
    exit();
}

$agreed = $_POST['agreed'] === 'true';

try {
    if ($agreed) {

        $stmt = $conn->prepare("UPDATE users SET privacy_agreement = ? WHERE id = ?");
        $stmt->execute([1, $_SESSION['user_id']]);
        $_SESSION['privacy_agreed'] = true;
    }
    
    if (!$agreed) {

        $_SESSION = array();
        
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
        session_destroy();

        setcookie('logged_in', '', [
            'expires' => time() - 3600,
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Strict'
        ]);

        setcookie('user_id', '', time() - 3600, '/');
        setcookie('username', '', time() - 3600, '/');
    }

    echo json_encode(['success' => true, 'agreed' => $agreed]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}