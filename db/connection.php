<?php
date_default_timezone_set('Asia/Manila');

try {
    if (getenv('DATABASE_URL')) {
        // Heroku Postgres configuration
        $db = parse_url(getenv('DATABASE_URL'));
        $pgsqlConfig = sprintf(
            "pgsql:host=%s;port=%s;dbname=%s;user=%s;password=%s",
            $db['host'],
            isset($db['port']) ? $db['port'] : 5432,
            ltrim($db['path'], '/'),
            $db['user'],
            $db['pass']
        );
        
        $conn = new PDO($pgsqlConfig);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Create tables for PostgreSQL
        $conn->exec("CREATE TABLE IF NOT EXISTS users (
            id SERIAL PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            reset_password VARCHAR(255) DEFAULT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            member_id INTEGER,
            admin_status INTEGER DEFAULT 0,
            privacy_agreement BOOLEAN DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            last_login TIMESTAMP DEFAULT NULL
        )");
        
        // Add default admin user if not exists
        $checkAdmin = $conn->query("SELECT id FROM users WHERE username = 'root'")->fetch();
        if (!$checkAdmin) {
            $hashedPassword = password_hash('mdradmin', PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, password, email, admin_status) VALUES (?, ?, ?, ?)");
            $stmt->execute(['root', $hashedPassword, 'admin@materdolorosa.com', 1]);
        }
        
    } else {
        // Local MySQL configuration
        define('DB_HOST', 'localhost');
        define('DB_USER', 'root');
        define('DB_PASS', '');
        define('DB_NAME', 'goldtree');
        
        $conn = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $conn->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME);
        $conn->exec("USE " . DB_NAME);
        
        // Create tables for MySQL
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
        
        // Add default admin user if not exists
        $checkAdmin = $conn->query("SELECT id FROM users WHERE username = 'root'")->fetch();
        if (!$checkAdmin) {
            $hashedPassword = password_hash('mdradmin', PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, password, email, admin_status) VALUES (?, ?, ?, ?)");
            $stmt->execute(['root', $hashedPassword, 'admin@materdolorosa.com', 1]);
        }
    }
} catch(PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    die("Connection failed: " . $e->getMessage());
}
?>