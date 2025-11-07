<?php
function init_session() {
    if (session_status() === PHP_SESSION_NONE) {
        // Set secure session parameters
        ini_set('session.cookie_httponly', 1);
        ini_set('session.use_only_cookies', 1);
        
        // Only set secure cookie if HTTPS is available (Heroku or local HTTPS)
        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') 
                   || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
                   || (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on');
        ini_set('session.cookie_secure', $isHttps ? 1 : 0);
        ini_set('session.cookie_samesite', 'Lax');
        ini_set('session.gc_maxlifetime', 3600); // 1 hour
        
        session_name('GOLDTREE_SESSION');
        session_start();
        
        // Regenerate session ID periodically
        if (!isset($_SESSION['last_regeneration'])) {
            regenerate_session();
        } elseif (time() - $_SESSION['last_regeneration'] > 1800) { // 30 minutes
            regenerate_session();
        }
    }
}

function destroy_session() {
    $_SESSION = array();
    
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    
    session_destroy();
}

function regenerate_session() {
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
    }
}