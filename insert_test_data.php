<?php
require_once 'db/connection.php';

try {
    // Insert test members first
    $stmt = $conn->prepare("INSERT INTO members (first_name, last_name, email, phone, status, created_at) VALUES 
        ('John', 'Doe', 'john@example.com', '1234567890', 'active', CURRENT_TIMESTAMP),
        ('Jane', 'Smith', 'jane@example.com', '0987654321', 'active', CURRENT_TIMESTAMP)");
    $stmt->execute();
    
    // Get the IDs of the inserted members
    $member1Id = $conn->lastInsertId();
    $member2Id = $member1Id + 1;
    
    // Insert test donations using the new member IDs
    $stmt = $conn->prepare("INSERT INTO donations (member_id, amount, donation_type, donation_date, notes) VALUES 
        (:member1, 1000, 'tithe', CURRENT_DATE, 'Test donation 1'),
        (:member2, 500, 'offering', CURRENT_DATE, 'Test donation 2'),
        (NULL, 750, 'project', CURRENT_DATE, 'Test donation 3')");
    $stmt->bindValue(':member1', $member1Id, PDO::PARAM_INT);
    $stmt->bindValue(':member2', $member2Id, PDO::PARAM_INT);
    $stmt->execute();
    
    echo "Test data inserted successfully!";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>