<?php

$dbUrl = getenv('JAWSDB_MARIA_URL');
if (!$dbUrl) {
    $dbUrl = getenv('DATABASE_URL');
}
if ($dbUrl) {
    $url = parse_url($dbUrl);
    define('DB_HOST', $url['host']);
    define('DB_USER', $url['user']);
    define('DB_PASS', $url['pass']);
    define('DB_NAME', ltrim($url['path'], '/'));
    $port = isset($url['port']) ? $url['port'] : 3306;
} else {

    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'goldtree');
    $port = 3306;
}

try {
    date_default_timezone_set('Asia/Manila');
    

    if ($dbUrl) {
        $conn = new PDO("mysql:host=" . DB_HOST . ";port=" . $port . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    } else {
  
        $conn = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
        $conn->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME);
        $conn->exec("USE " . DB_NAME);
    }
    
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $conn->exec("CREATE TABLE IF NOT EXISTS users (
        id INT PRIMARY KEY AUTO_INCREMENT,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        reset_password VARCHAR(255) DEFAULT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        member_id INT,
        admin_status INT DEFAULT 0,
        privacy_agreement BOOLEAN DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_login TIMESTAMP NULL DEFAULT NULL
    )");

    $conn->exec("CREATE TABLE IF NOT EXISTS members (
        id INT PRIMARY KEY AUTO_INCREMENT,
        first_name VARCHAR(50) NOT NULL,
        last_name VARCHAR(50) NOT NULL,
        email VARCHAR(100),
        phone VARCHAR(20),
        address TEXT,
        birthdate DATE,
        membership_date DATE DEFAULT CURRENT_DATE,
        gender ENUM('male', 'female', 'other') DEFAULT NULL,
        category VARCHAR(50) DEFAULT NULL,
        status ENUM('active', 'inactive') DEFAULT 'active',
        profile_image LONGBLOB,
        user_id INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    try {
        $conn->exec("ALTER TABLE users DROP FOREIGN KEY IF EXISTS users_member_id_fk");
        $conn->exec("ALTER TABLE users ADD CONSTRAINT users_member_id_fk FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE");
        
        $conn->exec("ALTER TABLE members DROP FOREIGN KEY IF EXISTS members_user_id_fk");
        $conn->exec("ALTER TABLE members ADD CONSTRAINT members_user_id_fk FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL");
    } catch(PDOException $e) {
        error_log("Error adding foreign keys: " . $e->getMessage());
    }

    $checkAdmin = $conn->query("SELECT id FROM users WHERE username = 'root'")->fetch();
    if (!$checkAdmin) {
        $hashedPassword = password_hash('mdradmin', PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (username, password, email, admin_status) VALUES (?, ?, ?, ?)");
        $stmt->execute(['root', $hashedPassword, 'admin@materdolorosa.com', 1]);
    }

    $conn->exec("CREATE TABLE IF NOT EXISTS donations (
        id INT PRIMARY KEY AUTO_INCREMENT,
        member_id INT,
        donor_name VARCHAR(100),
        amount DECIMAL(10,2) NOT NULL,
        donation_type ENUM('tithe', 'offering', 'project', 'other') NOT NULL,
        donation_date DATE NOT NULL,
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE
    )");

    try {   
        $conn->exec("ALTER TABLE donations ADD COLUMN IF NOT EXISTS donor_name VARCHAR(100)");
    } catch(PDOException $e) {
        if($conn->inTransaction()) {
            $conn->rollBack();
        }
    }

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
        attendance_date DATE DEFAULT CURRENT_DATE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
        FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE
    )");

    try {
        $conn->exec("CREATE TABLE notifications (
            id INT PRIMARY KEY AUTO_INCREMENT,
            notification_type ENUM('announcement', 'event', 'donation', 'other') NOT NULL,
            subject VARCHAR(255) NOT NULL,
            message TEXT NOT NULL,
            send_email BOOLEAN DEFAULT FALSE,
            status ENUM('pending', 'sent', 'failed') DEFAULT 'pending',
            created_by INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
        )");

        $conn->exec("CREATE TABLE notification_recipients (
            id INT PRIMARY KEY AUTO_INCREMENT,
            notification_id INT NOT NULL,
            user_id INT NOT NULL,
            user_email VARCHAR(100),
            is_read BOOLEAN DEFAULT FALSE,
            email_sent BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (notification_id) REFERENCES notifications(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )");
    } catch(PDOException $e) {
        error_log("Error recreating notification tables: " . $e->getMessage());
    }

    $conn->exec("CREATE TABLE IF NOT EXISTS member_notes (
        id INT PRIMARY KEY AUTO_INCREMENT,
        member_id INT NOT NULL,
        note_text TEXT NOT NULL,
        note_type ENUM('general', 'pastoral', 'counseling', 'other') NOT NULL,
        created_by INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE,
        FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
    )");

    $conn->exec("CREATE TABLE IF NOT EXISTS event_assignments (
        id INT PRIMARY KEY AUTO_INCREMENT,
        event_id INT NOT NULL,
        user_id INT NOT NULL,
        assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        UNIQUE KEY unique_assignment (event_id, user_id)
    )");

    $conn->exec("CREATE TABLE IF NOT EXISTS baptismal_records (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        parent1_name VARCHAR(255),
        parent1_origin VARCHAR(255),
        parent2_name VARCHAR(255),
        parent2_origin VARCHAR(255),
        address TEXT NOT NULL,
        birth_date DATE NOT NULL,
        birth_place VARCHAR(255) NOT NULL,
        gender ENUM('male', 'female', 'other') NOT NULL,
        baptism_date DATE NOT NULL,
        minister VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    $conn->exec("CREATE TABLE IF NOT EXISTS baptismal_sponsors (
        id INT AUTO_INCREMENT PRIMARY KEY,
        baptismal_record_id INT NOT NULL,
        sponsor_name VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (baptismal_record_id) REFERENCES baptismal_records(id) ON DELETE CASCADE
    )");

    $conn->exec("CREATE TABLE IF NOT EXISTS confirmation_records (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        parent1_name VARCHAR(255),
        parent1_origin VARCHAR(255),
        parent2_name VARCHAR(255),
        parent2_origin VARCHAR(255),
        address TEXT NOT NULL,
        birth_date DATE NOT NULL,
        birth_place VARCHAR(255) NOT NULL,
        gender ENUM('male', 'female', 'other') NOT NULL,
        baptism_date DATE NOT NULL,
        minister VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    $conn->exec("CREATE TABLE IF NOT EXISTS confirmation_sponsors (
        id INT AUTO_INCREMENT PRIMARY KEY,
        confirmation_record_id INT NOT NULL,
        sponsor_name VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (confirmation_record_id) REFERENCES confirmation_records(id) ON DELETE CASCADE
    )");

    $conn->exec("CREATE TABLE IF NOT EXISTS first_communion_records (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        parent1_name VARCHAR(255),
        parent1_origin VARCHAR(255),
        parent2_name VARCHAR(255),
        parent2_origin VARCHAR(255),
        address TEXT NOT NULL,
        birth_date DATE NOT NULL,
        birth_place VARCHAR(255) NOT NULL,
        gender ENUM('male', 'female', 'other') NOT NULL,
        baptism_date DATE NOT NULL,
        baptism_church VARCHAR(255) NOT NULL,
        church VARCHAR(255) NOT NULL,
        communion_date DATE NOT NULL,
        confirmation_date DATE NOT NULL,
        minister VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    $conn->exec("CREATE TABLE IF NOT EXISTS matrimony_records (
        id INT AUTO_INCREMENT PRIMARY KEY,
        matrimony_date DATE NOT NULL,
        church VARCHAR(255) NOT NULL,
        minister VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    $conn->exec("CREATE TABLE IF NOT EXISTS matrimony_couples (
        id INT AUTO_INCREMENT PRIMARY KEY,
        matrimony_record_id INT NOT NULL,
        type ENUM('bride', 'groom') NOT NULL,
        name VARCHAR(255) NOT NULL,
        parent1_name VARCHAR(255),
        parent1_origin VARCHAR(255),
        parent2_name VARCHAR(255),
        parent2_origin VARCHAR(255),
        birth_date DATE NOT NULL,
        birth_place VARCHAR(255) NOT NULL,
        gender ENUM('male', 'female', 'other') NOT NULL,
        baptism_date DATE NOT NULL,
        baptism_church VARCHAR(255) NOT NULL,
        confirmation_date DATE NOT NULL,
        confirmation_church VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (matrimony_record_id) REFERENCES matrimony_records(id) ON DELETE CASCADE
    )");

    $conn->exec("CREATE TABLE IF NOT EXISTS matrimony_sponsors (
        id INT AUTO_INCREMENT PRIMARY KEY,
        matrimony_record_id INT NOT NULL,
        sponsor_name VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (matrimony_record_id) REFERENCES matrimony_records(id) ON DELETE CASCADE
    )");

    $conn->exec("CREATE TABLE IF NOT EXISTS sacramental_records_deprecated (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        age INT NOT NULL,
        address TEXT NOT NULL,
        sacrament_type ENUM('Baptism', 'Confirmation', 'First Communion', 'Marriage') NOT NULL,
        date DATE NOT NULL,
        priest_presiding VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        migrated BOOLEAN DEFAULT FALSE
    ) COMMENT 'Deprecated: Use specific sacramental record tables instead'");

} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>