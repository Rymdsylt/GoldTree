<?php //DONT RUN! TESTING!
require_once 'connection.php';

try {
    $conn->exec("CREATE TABLE IF NOT EXISTS first_communion_records (
        id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(255) NOT NULL,
        gender VARCHAR(10) NOT NULL,
        address TEXT NOT NULL,
        birth_date DATE NOT NULL,
        birth_place VARCHAR(255) NOT NULL,
        parent1_name VARCHAR(255),
        parent1_origin VARCHAR(255),
        parent2_name VARCHAR(255),
        parent2_origin VARCHAR(255),
        baptism_date DATE NOT NULL,
        baptism_church VARCHAR(255) NOT NULL,
        church VARCHAR(255) NOT NULL,
        confirmation_date DATE NOT NULL,
        minister VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");

    $conn->exec("ALTER TABLE event_attendance ADD COLUMN IF NOT EXISTS attendance_date DATE DEFAULT CURRENT_DATE");
    

    $conn->exec("CREATE INDEX IF NOT EXISTS idx_event_attendance_date ON event_attendance(event_id, member_id, attendance_date)");
    
    
    $conn->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS privacy_agreement BOOLEAN DEFAULT NULL");
    
    echo "Schema updated successfully";
} catch(PDOException $e) {
    echo "Error updating schema: " . $e->getMessage();
}
?>