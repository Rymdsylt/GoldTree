<?php
class SessionHelper {
    public static function init() {
        if (session_status() === PHP_SESSION_NONE) {
            ini_set('session.cookie_httponly', 1);
            ini_set('session.use_only_cookies', 1);
            
            $isHeroku = (getenv('DATABASE_URL') !== false);
            
            if ($isHeroku) {
                ini_set('session.cookie_secure', 1);
                ini_set('session.cookie_samesite', 'Strict');
                
                // Set session handler for Redis if available
                if (getenv('REDIS_URL')) {
                    ini_set('session.save_handler', 'redis');
                    ini_set('session.save_path', getenv('REDIS_URL'));
                }
            }
            
            session_start();
        }
    }

    public static function regenerate() {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
    }

    public static function destroy() {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
            if (isset($_COOKIE[session_name()])) {
                setcookie(session_name(), '', [
                    'expires' => time() - 3600,
                    'path' => '/',
                    'secure' => true,
                    'httponly' => true,
                    'samesite' => 'Strict'
                ]);
            }
        }
    }
}