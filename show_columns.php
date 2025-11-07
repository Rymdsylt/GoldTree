<?php
try {
    $dbUrl = getenv('DATABASE_URL');
    if (!$dbUrl) die("No DATABASE_URL set\n");
    
    $db = parse_url($dbUrl);
    $pgsql = sprintf(
        "pgsql:host=%s;port=%s;dbname=%s",
        $db['host'],
        $db['port'] ?? '5432',
        ltrim($db['path'], '/')
    );
    
    $conn = new PDO($pgsql, $db['user'], $db['pass']);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $result = $conn->query("
        SELECT column_name, data_type, character_maximum_length, is_nullable, column_default
        FROM information_schema.columns 
        WHERE table_name = 'members'
        ORDER BY ordinal_position;
    ");
    
    echo "MEMBERS TABLE STRUCTURE:\n";
    echo "=======================\n\n";
    
    foreach ($result as $col) {
        echo "COLUMN: {$col['column_name']}\n";
        echo "TYPE: {$col['data_type']}";
        if ($col['character_maximum_length']) {
            echo "({$col['character_maximum_length']})";
        }
        echo "\n";
        echo "NULLABLE: {$col['is_nullable']}\n";
        echo "DEFAULT: " . ($col['column_default'] ?? 'NULL') . "\n";
        echo "--------------------\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}