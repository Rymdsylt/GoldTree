<?php
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/../db/connection.php';

init_session();
header('Content-Type: application/json');

// Check if database is PostgreSQL
$isPostgres = (getenv('DATABASE_URL') !== false);

try {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Not logged in']);
        exit();
    }

    if (!isset($_POST['agreed'])) {
        echo json_encode(['success' => false, 'message' => 'Missing agreement status']);
        exit();
    }

    $agreed = filter_var($_POST['agreed'], FILTER_VALIDATE_BOOLEAN);

    if ($agreed) {
        // For PostgreSQL, use boolean true; for MySQL, use 1
        $agreementValue = $isPostgres ? true : 1;
        
        $stmt = $conn->prepare("UPDATE users SET privacy_agreement = ? WHERE id = ?");
        $stmt->execute([$agreementValue, $_SESSION['user_id']]);
        $_SESSION['privacy_agreed'] = true;
        $_SESSION['privacy_checked'] = true;
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
    error_log("Privacy agreement error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Database error: ' . ($e->getMessage() ?: 'Unknown error')
    ]);
} catch (Exception $e) {
    error_log("Privacy agreement error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Error: ' . ($e->getMessage() ?: 'Unknown error')
    ]);
}