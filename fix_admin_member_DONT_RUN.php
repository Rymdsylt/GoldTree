<?php
require_once 'db/connection.php';

try {
    $conn->beginTransaction();

    // Get admin user
    $stmt = $conn->prepare("SELECT id, email FROM users WHERE username = 'root'");
    $stmt->execute();
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$admin) {
        throw new Exception('Admin user not found');
    }

    // Check if admin already has an associated member
    $checkStmt = $conn->prepare("SELECT id FROM members WHERE user_id = ?");
    $checkStmt->execute([$admin['id']]);
    
    if ($checkStmt->fetch()) {
        echo "Admin user already has an associated member record.";
        $conn->rollBack();
        exit;
    }

    // Create member record for admin
    $stmt = $conn->prepare("
        INSERT INTO members (
            first_name,
            last_name,
            email,
            status,
            category,
            user_id,
            membership_date
        ) VALUES (
            'System',
            'Administrator',
            ?,
            'active',
            'administrator',
            ?,
            CURRENT_DATE
        )
    ");
    $stmt->execute([$admin['email'], $admin['id']]);
    $memberId = $conn->lastInsertId();

    // Update user record with member_id
    $stmt = $conn->prepare("UPDATE users SET member_id = ? WHERE id = ?");
    $stmt->execute([$memberId, $admin['id']]);

    $conn->commit();
    echo "Successfully created and associated member record for admin user.";

} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo "Error: " . $e->getMessage();
}