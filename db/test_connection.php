<?php
require_once 'connection.php';

try {
    // Test 1: Basic Connection and Version Check
    $result = $conn->query('SELECT version()');
    $version = $result->fetch(PDO::FETCH_ASSOC);
    echo "✓ Connection successful!\n";
    echo "✓ PostgreSQL version: " . $version['version'] . "\n\n";

    // Test 2: Check Server Time
    $query = "SELECT current_timestamp";
    $stmt = $conn->query($query);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "✓ Server time: " . $result['current_timestamp'] . "\n\n";

    // Test 3: Test Table Creation
    $conn->exec("DROP TABLE IF EXISTS connection_test");
    $conn->exec("CREATE TABLE IF NOT EXISTS connection_test (id SERIAL PRIMARY KEY, test_column VARCHAR(50))");
    echo "✓ Test table created successfully!\n";
    
    // Test 4: Test Insert
    $stmt = $conn->prepare("INSERT INTO connection_test (test_column) VALUES (?)");
    $stmt->execute(['Connection test successful']);
    echo "✓ Test data inserted successfully!\n";
    
    // Test 5: Test Select
    $result = $conn->query("SELECT * FROM connection_test");
    $data = $result->fetch(PDO::FETCH_ASSOC);
    echo "✓ Test data retrieved successfully: " . $data['test_column'] . "\n";
    
    // Clean up
    $conn->exec("DROP TABLE IF EXISTS connection_test");
    echo "✓ Test table cleaned up successfully!\n";
    
} catch (PDOException $e) {
    echo "❌ Connection failed: " . $e->getMessage() . "\n";
    error_log("Database connection error: " . $e->getMessage());
}
?>