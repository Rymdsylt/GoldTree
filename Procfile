web: vendor/bin/heroku-php-apache2 -C apache_app.conf
scheduler: php cron/mark_daily_absences.php
release: php db/migrations/001_heroku_postgres.php