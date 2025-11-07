<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Start session
session_start();

// Load database connection
require_once __DIR__ . '/db/connection.php';

// Default route to login page
if (!isset($_SERVER['PATH_INFO']) || $_SERVER['PATH_INFO'] == '/') {
    require_once __DIR__ . '/login.php';
    exit();
}

// Handle other routes
$path = $_SERVER['PATH_INFO'];
$file = __DIR__ . $path;

if (file_exists($file)) {
    require_once $file;
} else {
    http_response_code(404);
    echo "404 - Page not found";
}
?>