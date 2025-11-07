<?php
require_once '../db/connection.php';

// Check if database is PostgreSQL
$isPostgres = (getenv('DATABASE_URL') !== false);

try {
    // Use database-specific table structure query
    if ($isPostgres) {
        $stmt = $conn->query("SELECT column_name, data_type, is_nullable FROM information_schema.columns WHERE table_name = 'donations' ORDER BY ordinal_position");
    } else {
        $stmt = $conn->query("SHOW COLUMNS FROM donations");
    }
    echo "=== Donations Table Structure ===\n";
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo json_encode($row) . "\n";
    }
    
    $stmt = $conn->query("SELECT COALESCE(SUM(amount), 0) as total, COUNT(*) as count FROM donations");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "\n=== Donations Summary ===\n";
    echo json_encode($result) . "\n";
    
    $stmt = $conn->query("SELECT id, amount, donation_date FROM donations ORDER BY donation_date DESC LIMIT 5");
    echo "\n=== Recent Donations ===\n";
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo json_encode($row) . "\n";
    }
    
} catch(PDOException $e) {
    echo "Database error: " . $e->getMessage();
}
?>
