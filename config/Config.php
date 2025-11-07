<?php
class Config {
    private static $config = [];

    public static function init() {
        // Database Configuration
        if (getenv('JAWSDB_MARIA_URL')) {
            $url = parse_url(getenv('JAWSDB_MARIA_URL'));
            self::$config['db'] = [
                'host' => $url['host'],
                'username' => $url['user'],
                'password' => $url['pass'],
                'database' => ltrim($url['path'], '/'),
                'port' => isset($url['port']) ? $url['port'] : 3306
            ];
        } else {
            self::$config['db'] = [
                'host' => 'localhost',
                'username' => 'root',
                'password' => '',
                'database' => 'goldtree',
                'port' => 3306
            ];
        }

        // Email Configuration
        self::$config['mail'] = [
            'smtp_host' => getenv('SMTP_HOST') ?: 'smtp.gmail.com',
            'smtp_port' => getenv('SMTP_PORT') ?: 587,
            'smtp_user' => getenv('SMTP_USER') ?: '',
            'smtp_pass' => getenv('SMTP_PASS') ?: '',
            'from_email' => getenv('FROM_EMAIL') ?: 'noreply@materdolorosa.com',
            'from_name' => getenv('FROM_NAME') ?: 'Mater Dolorosa Parish'
        ];

        // App Configuration
        self::$config['app'] = [
            'url' => getenv('APP_URL') ?: 'http://localhost',
            'env' => getenv('APP_ENV') ?: 'local',
            'debug' => getenv('APP_DEBUG') === 'true',
            'timezone' => 'Asia/Manila'
        ];
    }

    public static function get($key, $default = null) {
        $keys = explode('.', $key);
        $config = self::$config;

        foreach ($keys as $k) {
            if (!isset($config[$k])) {
                return $default;
            }
            $config = $config[$k];
        }

        return $config;
    }
}