<?php
require_once '../../db/connection.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

try {
    $conn->beginTransaction();
    
    $stmt = $conn->prepare("SELECT username, email FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $currentUser = $stmt->fetch();
    
    $isAdminUser = ($currentUser['username'] === 'root' || $currentUser['email'] === 'admin@materdolorosa.com');

    $username = $_POST['username'] ?? '';
    $first_name = $_POST['first_name'] ?? '';
    $last_name = $_POST['last_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';

    if ($isAdminUser) {
        if ($username !== 'root' || $email !== 'admin@materdolorosa.com') {
            throw new Exception('Admin username and email cannot be changed');
        }
    } else {
        $check_username = $conn->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
        $check_username->execute([$username, $_SESSION['user_id']]);
        if ($check_username->fetch()) {
            throw new Exception('This username is already taken');
        }

        $check_email = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $check_email->execute([$email, $_SESSION['user_id']]);
        if ($check_email->fetch()) {
            throw new Exception('This email is already registered to another user');
        }

        $stmt = $conn->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
        $stmt->execute([$username, $email, $_SESSION['user_id']]);
    }

    $stmt = $conn->prepare("SELECT member_id FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $member_id = $stmt->fetchColumn();


    if ($member_id) {
        $stmt = $conn->prepare("
            UPDATE members 
            SET first_name = ?, 
                last_name = ?, 
                email = ?, 
                phone = ?, 
                address = ?
            WHERE id = ?");
        $stmt->execute([$first_name, $last_name, $email, $phone, $address, $member_id]);

        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['profile_image']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            if (in_array($ext, $allowed)) {
                $profile_image = file_get_contents($_FILES['profile_image']['tmp_name']);
                $stmt = $conn->prepare("UPDATE members SET profile_image = ? WHERE id = ?");
                $stmt->execute([$profile_image, $member_id]);
            }
        }
    }

    $conn->commit();
    $_SESSION['username'] = $username;
    echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);

} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}