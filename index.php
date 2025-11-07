<?php
// Error reporting - disable in production (Heroku)
$isProduction = (getenv('APP_ENV') === 'production' || getenv('DATABASE_URL') !== false);
if (!$isProduction) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
    ini_set('log_errors', 1);
}

// Load session management and database connection
require_once __DIR__ . '/auth/session.php';
require_once __DIR__ . '/db/connection.php';

// Initialize session
init_session();

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