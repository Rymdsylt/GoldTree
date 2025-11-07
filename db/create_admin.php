<?php
require_once 'connection.php';

try {
    // Check if admin exists
    $checkAdmin = $conn->query("SELECT id, username, admin_status FROM users WHERE username = 'root'")->fetch();
    
    if ($checkAdmin) {
        echo "Admin user exists:\n";
        print_r($checkAdmin);
    } else {
        echo "Admin user does not exist. Creating...\n";
        
        // Create admin user
        $hashedPassword = password_hash('mdradmin', PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (username, password, email, admin_status, privacy_agreement) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute(['root', $hashedPassword, 'admin@materdolorosa.com', 1, true]);
        
        $newAdmin = $conn->query("SELECT id, username, admin_status FROM users WHERE username = 'root'")->fetch();
        echo "Admin user created successfully:\n";
        print_r($newAdmin);
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
