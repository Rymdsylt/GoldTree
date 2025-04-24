<?php
$current_page = basename($_SERVER['PHP_SELF']);

if ($current_page !== 'register.php' && $current_page !== 'login.php') {
    if (!isset($_COOKIE['logged_in']) || $_COOKIE['logged_in'] !== 'true') {
        header("Location: login.php");
        exit();
    }
}

$username = $_COOKIE['username'] ?? 'User';
?>