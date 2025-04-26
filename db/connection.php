<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'goldtree');

try {
    $conn = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $conn->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME);
    $conn->exec("USE " . DB_NAME);

    $conn->exec("CREATE TABLE IF NOT EXISTS members (
        id INT PRIMARY KEY AUTO_INCREMENT,
        first_name VARCHAR(50) NOT NULL,
        last_name VARCHAR(50) NOT NULL,
        email VARCHAR(100),
        phone VARCHAR(20),
        address TEXT,
        birthdate DATE,
        membership_date DATE,
        status ENUM('active', 'inactive') DEFAULT 'active',
        profile_image LONGBLOB,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    try {
        $conn->exec("ALTER TABLE members ADD COLUMN IF NOT EXISTS profile_image LONGBLOB");
    } catch(PDOException $e) {
        if($conn->inTransaction()) {
            $conn->rollBack();
        }
    }

    $conn->exec("CREATE TABLE IF NOT EXISTS users (
        id INT PRIMARY KEY AUTO_INCREMENT,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        member_id INT,
        admin_status INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_login TIMESTAMP NULL DEFAULT NULL,
        FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE
    )");

    // Create default admin user if it doesn't exist
    $checkAdmin = $conn->query("SELECT id FROM users WHERE username = 'root'")->fetch();
    if (!$checkAdmin) {
        $hashedPassword = password_hash('mdradmin', PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (username, password, email, admin_status) VALUES (?, ?, ?, ?)");
        $stmt->execute(['root', $hashedPassword, 'admin@materdolorosa.com', 1]);
    }

    $conn->exec("CREATE TABLE IF NOT EXISTS donations (
        id INT PRIMARY KEY AUTO_INCREMENT,
        member_id INT,
        amount DECIMAL(10,2) NOT NULL,
        donation_type ENUM('tithe', 'offering', 'project', 'other') NOT NULL,
        donation_date DATE NOT NULL,
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE
    )");

    $conn->exec("CREATE TABLE IF NOT EXISTS events (
        id INT PRIMARY KEY AUTO_INCREMENT,
        title VARCHAR(100) NOT NULL,
        description TEXT,
        start_datetime DATETIME NOT NULL,
        end_datetime DATETIME NOT NULL,
        event_type ENUM('worship', 'prayer', 'youth', 'outreach', 'special') NOT NULL,
        location VARCHAR(100),
        max_attendees INT,
        registration_deadline DATETIME,
        image LONGBLOB,
        status ENUM('upcoming', 'ongoing', 'completed', 'cancelled') DEFAULT 'upcoming',
        created_by INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        last_notification_sent TIMESTAMP NULL,
        reminder_sent BOOLEAN DEFAULT FALSE,
        FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
    )");


    $conn->exec("CREATE TABLE IF NOT EXISTS event_attendance (
        id INT PRIMARY KEY AUTO_INCREMENT,
        event_id INT NOT NULL,
        member_id INT NOT NULL,
        attendance_status ENUM('present', 'absent', 'late') NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
        FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE
    )");

    $conn->exec("CREATE TABLE IF NOT EXISTS notifications (
        id INT PRIMARY KEY AUTO_INCREMENT,
        title VARCHAR(100) NOT NULL,
        content TEXT NOT NULL,
        priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
        start_date DATE NOT NULL,
        end_date DATE,
        is_read BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    $conn->exec("CREATE TABLE IF NOT EXISTS pastoral_care (
        id INT PRIMARY KEY AUTO_INCREMENT,
        member_id INT NOT NULL,
        notes TEXT NOT NULL,
        care_date DATE NOT NULL,
        care_type ENUM('counseling', 'visitation', 'prayer', 'other') NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE
    )");

} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>