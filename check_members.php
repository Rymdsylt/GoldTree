<?php
require_once __DIR__ . '/db/connection.php';

try {
    // Get total count
    $result = $conn->query("SELECT COUNT(*) as count FROM members");
    $count = $result->fetch(PDO::FETCH_ASSOC);
    echo "Total members: " . $count['count'] . "\n\n";

    // Get all members
    $result = $conn->query("SELECT * FROM members ORDER BY id LIMIT 10");
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        echo "ID: " . $row['id'] . "\n";
        echo "Name: " . $row['first_name'] . " " . $row['last_name'] . "\n";
        echo "Email: " . $row['email'] . "\n";
        echo "Status: " . $row['status'] . "\n";
        echo "Created at: " . $row['created_at'] . "\n";
        echo "----------------------\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>