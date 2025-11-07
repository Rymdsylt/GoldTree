<?php
// Use environment variables for Heroku, fallback to defaults for local development
define('SMTP_HOST', getenv('SMTP_HOST') ?: 'smtp.gmail.com');
define('SMTP_PORT', getenv('SMTP_PORT') ?: 587);
define('SMTP_USERNAME', getenv('SMTP_USERNAME') ?: 'justg00gl3itn0w@gmail.com');
define('SMTP_PASSWORD', getenv('SMTP_PASSWORD') ?: 'khtr toay tvtl qsoo');
define('SMTP_SECURE', getenv('SMTP_SECURE') ?: 'tls');
define('SMTP_FROM_EMAIL', getenv('SMTP_FROM_EMAIL') ?: 'justg00gl3itn0w@gmail.com');
define('SMTP_FROM_NAME', getenv('SMTP_FROM_NAME') ?: 'Mater Dolorosa Church');
?>