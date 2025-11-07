<?php
require_once 'connection.php';

try {
    // Check table structure
    echo "Table structure for 'members':\n";
    $structure = $conn->query("SELECT column_name, data_type, character_maximum_length, column_default, is_nullable 
                             FROM information_schema.columns 
                             WHERE table_name = 'members' 
                             ORDER BY ordinal_position");
    print_r($structure->fetchAll(PDO::FETCH_ASSOC));

    // Count total members
    $count = $conn->query("SELECT COUNT(*) as total FROM members")->fetch(PDO::FETCH_ASSOC);
    echo "\nTotal members: " . $count['total'] . "\n\n";

    // Get all members
    echo "Members list:\n";
    $members = $conn->query("SELECT * FROM members ORDER BY id");
    $allMembers = $members->fetchAll(PDO::FETCH_ASSOC);
    print_r($allMembers);

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>