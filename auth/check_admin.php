<?php
// Check if user is admin
if (!isset($_SESSION['user_id'])) {
    require_once __DIR__ . '/../config.php';
    header("Location: " . base_path('login.php'));
    exit();
}

if (!isset($conn)) {
    require_once __DIR__ . '/../db/connection.php';
}

$stmt = $conn->prepare("SELECT admin_status FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user || $user['admin_status'] != 1) {
    require_once __DIR__ . '/../config.php';
    header("Location: " . base_path('dashboard.php'));
    exit();
}
?>

