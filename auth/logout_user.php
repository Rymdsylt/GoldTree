<?php
require_once __DIR__ . '/../config.php';
session_start();

$_SESSION = array();

if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}
session_destroy();

setcookie('logged_in', '', [
    'expires' => time() - 3600,
    'path' => '/',
    'httponly' => true,
    'samesite' => 'Strict'
]);

setcookie('user_id', '', time() - 3600, '/');
setcookie('username', '', time() - 3600, '/');

header('Location: ' . base_path('login.php'));
exit();