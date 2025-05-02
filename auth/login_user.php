<?php
session_start();
require_once '../db/connection.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = $_POST['login'];
    $password = $_POST['password'];
    
    try {
        $stmt = $conn->prepare("SELECT id, username, password, reset_password, admin_status FROM users WHERE username = :login OR email = :login");
        $stmt->execute(['login' => $login]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $isValid = false;
            
            // First check bcrypt password
            if (password_verify($password, $user['password'])) {
                $isValid = true;
            }
            // Then check reset_password if it exists
            elseif ($user['reset_password'] !== null && $password === $user['reset_password']) {
                $isValid = true;
                // Optionally upgrade to bcrypt here if needed
                $stmt = $conn->prepare("UPDATE users SET password = :new_password, reset_password = NULL WHERE id = :id");
                $stmt->execute([
                    'new_password' => password_hash($password, PASSWORD_DEFAULT),
                    'id' => $user['id']
                ]);
            }

            if ($isValid) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['admin_status'] = $user['admin_status'];
                
                setcookie('logged_in', 'true', time() + (86400 * 30), "/");
                
                $stmt = $conn->prepare("UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = :id");
                $stmt->execute(['id' => $user['id']]);

                echo json_encode(['success' => true]);
                exit;
            }
        }
        
        echo json_encode(['success' => false, 'message' => 'Invalid username/email or password']);
        
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error occurred']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
