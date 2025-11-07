<?php
/**
 * Application Configuration
 * Centralized configuration for Heroku and local development
 */

// Detect if running on Heroku
define('IS_HEROKU', (bool)getenv('HEROKU_APP_NAME') || (bool)getenv('DYNO') || (bool)getenv('JAWSDB_MARIA_URL'));

// Base path - empty on Heroku, /GoldTree for local development
define('BASE_PATH', IS_HEROKU ? '' : '/GoldTree');

// Helper function to get full path
function base_path($path = '') {
    $path = ltrim($path, '/');
    return BASE_PATH . ($path ? '/' . $path : '');
}

// Error reporting - disable in production
if (IS_HEROKU) {
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
} else {
    // Development mode
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

