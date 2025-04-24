<?php
session_start();

$_SESSION = array();

session_destroy();

if (isset($_COOKIE['user_id'])) {
    setcookie('user_id', '', time() - 3600, '/');
}
if (isset($_COOKIE['username'])) {
    setcookie('username', '', time() - 3600, '/');
}
if (isset($_COOKIE['logged_in'])) {
    setcookie('logged_in', '', time() - 3600, '/');
}

header('Location: ../login.php');
exit();