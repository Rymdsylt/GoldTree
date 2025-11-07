# Heroku Deployment Guide

This guide will help you deploy the Mater Dolorosa Parish Management System to Heroku.

## Prerequisites

1. Heroku account (already logged in)
2. Heroku CLI installed
3. Git repository initialized

## Step 1: Initialize Git (if not already done)

```bash
git init
git add .
git commit -m "Initial commit - Heroku ready"
```

## Step 2: Create Heroku App

```bash
heroku create your-app-name
```

## Step 3: Add MySQL Database (ClearDB)

Heroku uses ClearDB for MySQL databases:

```bash
heroku addons:create cleardb:ignite
```

This will automatically set the `DATABASE_URL` environment variable.

## Step 4: Set Environment Variables

Set your SMTP email credentials:

```bash
heroku config:set SMTP_HOST=smtp.gmail.com
heroku config:set SMTP_PORT=587
heroku config:set SMTP_USERNAME=your-email@gmail.com
heroku config:set SMTP_PASSWORD=your-app-password
heroku config:set SMTP_SECURE=tls
heroku config:set SMTP_FROM_EMAIL=your-email@gmail.com
heroku config:set SMTP_FROM_NAME="Mater Dolorosa Church"
```

**Note:** For Gmail, you'll need to use an App Password, not your regular password.

## Step 5: Deploy to Heroku

```bash
git push heroku main
```

Or if you're using master branch:
```bash
git push heroku master
```

## Step 6: Run Database Migrations

After deployment, the database tables will be created automatically on first connection. However, if you need to run migrations manually:

```bash
heroku run php db/update_schema.php
```

## Step 7: Create Admin User

The system will automatically create an admin user with:
- Username: `root`
- Password: `mdradmin`
- Email: `admin@materdolorosa.com`

**IMPORTANT:** Change this password immediately after first login!

## Step 8: Set Up Cron Jobs (Optional)

For automated tasks like marking absences and updating event statuses, you can use Heroku Scheduler:

1. Add the addon:
```bash
heroku addons:create scheduler:standard
```

2. Configure jobs in Heroku Dashboard:
   - Go to your app → Resources → Heroku Scheduler
   - Add job: `php cron/mark_daily_absences.php` (run daily)
   - Add job: `php cron/update_event_status.php` (run hourly)

## Troubleshooting

### Database Connection Issues

Check your database URL:
```bash
heroku config:get DATABASE_URL
```

### View Logs

```bash
heroku logs --tail
```

### Run Commands

```bash
heroku run php your-script.php
```

## Environment Variables Reference

| Variable | Description | Default |
|----------|-------------|---------|
| `DATABASE_URL` | Auto-set by ClearDB addon | - |
| `SMTP_HOST` | SMTP server hostname | smtp.gmail.com |
| `SMTP_PORT` | SMTP server port | 587 |
| `SMTP_USERNAME` | SMTP username/email | - |
| `SMTP_PASSWORD` | SMTP password/app password | - |
| `SMTP_SECURE` | Encryption type (tls/ssl) | tls |
| `SMTP_FROM_EMAIL` | From email address | - |
| `SMTP_FROM_NAME` | From name | Mater Dolorosa Church |

## Notes

- The application will automatically detect if it's running on Heroku by checking for the `DATABASE_URL` environment variable
- Local development will continue to work with the default hardcoded values
- Make sure to keep your `.gitignore` file to avoid committing sensitive credentials
- The `vendor/` directory should be committed to Heroku (it's in the repo)

## Security Recommendations

1. Change the default admin password immediately
2. Use strong passwords for all user accounts
3. Regularly update dependencies: `composer update`
4. Monitor Heroku logs for any errors
5. Set up proper backup strategies for your database

