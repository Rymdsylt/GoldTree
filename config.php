<?php

define('IS_HEROKU', (bool)getenv('HEROKU_APP_NAME') || (bool)getenv('DYNO') || (bool)getenv('JAWSDB_MARIA_URL'));


define('BASE_PATH', IS_HEROKU ? '' : '/GoldTree');

function base_path($path = '') {
    $path = ltrim($path, '/');
    return BASE_PATH . ($path ? '/' . $path : '');
}


if (IS_HEROKU) {
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
} else {

    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

