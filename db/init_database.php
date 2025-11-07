<?php
require_once 'connection.php';

try {
    echo "Starting database initialization...\n";
    
    // First, verify connection
    $testQuery = $conn->query("SELECT version()");
    $version = $testQuery->fetch(PDO::FETCH_ASSOC);
    echo "Connected to PostgreSQL version: " . $version['version'] . "\n\n";
    
    // Create users table first
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
    echo "Users table created.\n";
    
    // Create members table
    $conn->exec("CREATE TABLE IF NOT EXISTS members (
        id SERIAL PRIMARY KEY,
        first_name VARCHAR(50) NOT NULL,
        last_name VARCHAR(50) NOT NULL,
        email VARCHAR(100),
        phone VARCHAR(20),
        address TEXT,
        birthdate DATE,
        membership_date DATE DEFAULT CURRENT_DATE,
        gender VARCHAR(10),
        category VARCHAR(50) DEFAULT NULL,
        status VARCHAR(10) DEFAULT 'active',
        profile_image BYTEA,
        user_id INTEGER,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    echo "Members table created.\n";
    
    // Create admin user
    $hashedPassword = password_hash('mdradmin', PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (username, password, email, admin_status, privacy_agreement) 
                           VALUES (?, ?, ?, ?, ?) 
                           ON CONFLICT (username) DO UPDATE 
                           SET admin_status = 1, 
                               privacy_agreement = true");
    $stmt->execute(['root', $hashedPassword, 'admin@materdolorosa.com', 1, true]);
    echo "Admin user created/updated.\n";
    
    // Verify tables
    $tables = $conn->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'");
    echo "\nCreated tables:\n";
    while ($table = $tables->fetch(PDO::FETCH_ASSOC)) {
        echo "- " . $table['table_name'] . "\n";
    }
    
    echo "\nDatabase initialization complete!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    error_log("Database initialization error: " . $e->getMessage());
}
?>