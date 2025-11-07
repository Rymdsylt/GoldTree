<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Checking database connection...\n";
$databaseUrl = getenv('DATABASE_URL');
echo "Database URL: " . (empty($databaseUrl) ? "NOT SET" : "IS SET") . "\n";

require_once 'connection.php';

try {
    // Test connection
    echo "\nTesting connection to PostgreSQL...\n";
    $result = $conn->query('SELECT version()');
    $version = $result->fetch(PDO::FETCH_ASSOC);
    echo "Connected to: " . $version['version'] . "\n\n";
    
    // List all tables in the database
    $query = "SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'";
    $result = $conn->query($query);
    
    echo "Tables in the database:\n";
    $tables = $result->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($tables)) {
        echo "No tables found in the database. Database is empty.\n";
    } else {
        foreach ($tables as $row) {
            $tableName = $row['table_name'];
            echo "\n- $tableName\n";
            
            // Count rows in each table
            $countQuery = "SELECT COUNT(*) as count FROM \"$tableName\"";
            $countResult = $conn->query($countQuery);
            $count = $countResult->fetch(PDO::FETCH_ASSOC)['count'];
            echo "  Rows: $count\n";
            
            // Show table structure
            $structureQuery = "SELECT column_name, data_type, character_maximum_length 
                             FROM information_schema.columns 
                             WHERE table_name = '$tableName'";
            $structureResult = $conn->query($structureQuery);
            echo "  Columns:\n";
            while ($col = $structureResult->fetch(PDO::FETCH_ASSOC)) {
                echo "    - {$col['column_name']} ({$col['data_type']}" . 
                     ($col['character_maximum_length'] ? "({$col['character_maximum_length']})" : "") . ")\n";
            }
        }
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    error_log("Database check error: " . $e->getMessage());
}
?>