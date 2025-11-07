<?php
require_once 'db/connection.php';

try {
    echo "MEMBERS TABLE STRUCTURE:\n";
    echo "=======================\n\n";
    
    $columns = $conn->query("
        SELECT 
            column_name,
            data_type,
            character_maximum_length,
            column_default,
            is_nullable
        FROM information_schema.columns 
        WHERE table_name = 'members'
        ORDER BY ordinal_position;
    ");
    
    while ($col = $columns->fetch(PDO::FETCH_ASSOC)) {
        echo "Column: " . str_pad($col['column_name'], 20) . "\n";
        echo "Type: " . str_pad($col['data_type'], 20) . "\n";
        if ($col['character_maximum_length']) {
            echo "Length: " . str_pad($col['character_maximum_length'], 20) . "\n";
        }
        echo "Nullable: " . str_pad($col['is_nullable'], 20) . "\n";
        echo "Default: " . str_pad($col['column_default'] ?? 'NULL', 20) . "\n";
        echo "------------------------\n";
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>