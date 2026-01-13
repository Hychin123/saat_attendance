# 🚀 SAAT Attendance System - Deployment Checklist

## Pre-Deployment Preparation

### 1. Environment Configuration
- [ ] Create production `.env` file
- [ ] Set `APP_ENV=production`
- [ ] Set `APP_DEBUG=false`
- [ ] Generate new `APP_KEY` with `php artisan key:generate`
- [ ] Configure production database credentials
- [ ] Set up mail server (SMTP/Mailgun/SendGrid)
- [ ] Configure queue driver (Redis recommended)
- [ ] Set up backup storage credentials
- [ ] Configure session and cache drivers

### 2. Security Hardening
- [ ] Review and update all user permissions
- [ ] Ensure all Filament resources have proper policies
- [ ] Verify CSRF protection is enabled
- [ ] Configure CORS if using API
- [ ] Set up HTTPS/SSL certificates
- [ ] Configure firewall rules
- [ ] Update `config/app.php` trusted proxies if behind load balancer
- [ ] Enable rate limiting on routes
- [ ] Review file upload security settings

### 3. Database Preparation
- [ ] Run migrations on production database: `php artisan migrate --force`
- [ ] Seed initial data if needed: `php artisan db:seed`
- [ ] Create super admin user
- [ ] Set up database backups (automated daily)
- [ ] Test database connection
- [ ] Configure database read replicas (if needed)
- [ ] Optimize database indexes

### 4. Performance Optimization
- [ ] Run `php artisan config:cache`
- [ ] Run `php artisan route:cache`
- [ ] Run `php artisan view:cache`
- [ ] Run `composer install --optimize-autoloader --no-dev`
- [ ] Run `npm install && npm run build`
- [ ] Enable OPcache in PHP
- [ ] Configure Redis for caching and sessions
- [ ] Set up CDN for static assets (optional)
- [ ] Enable Gzip compression on web server

### 5. Code Quality
- [ ] Run tests: `php artisan test`
- [ ] Check for TODO/FIXME comments
- [ ] Review error handling
- [ ] Ensure all environment variables are documented
- [ ] Update `.env.example` with all required variables

## Deployment Steps

### 6. Server Setup
- [ ] Install PHP 8.2 or higher
- [ ] Install required PHP extensions (see below)
- [ ] Install Composer
- [ ] Install Node.js and npm
- [ ] Install MySQL/MariaDB
- [ ] Install Redis (recommended)
- [ ] Install and configure web server (Nginx/Apache)
- [ ] Set up supervisor for queue workers
- [ ] Configure cron jobs

#### Required PHP Extensions
```
- php-mysql
- php-xml
- php-mbstring
- php-curl
- php-zip
- php-gd
- php-intl
- php-bcmath
- php-redis (if using Redis)
```

### 7. Application Deployment
- [ ] Clone repository to server
- [ ] Run `composer install --optimize-autoloader --no-dev`
- [ ] Run `npm install && npm run build`
- [ ] Copy `.env.production` to `.env`
- [ ] Run `php artisan key:generate`
- [ ] Run `php artisan migrate --force`
- [ ] Run `php artisan storage:link`
- [ ] Set correct file permissions (see below)
- [ ] Clear and cache configurations

#### File Permissions
```bash
# Web server user (e.g., www-data)
sudo chown -R www-data:www-data /path/to/app
sudo chmod -R 755 /path/to/app
sudo chmod -R 775 /path/to/app/storage
sudo chmod -R 775 /path/to/app/bootstrap/cache
```

### 8. Web Server Configuration

#### Nginx Configuration Example
```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /path/to/app/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### 9. Queue Workers Setup

#### Supervisor Configuration
Create `/etc/supervisor/conf.d/saat-attendance-worker.conf`:
```ini
[program:saat-attendance-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/app/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/app/storage/logs/worker.log
stopwaitsecs=3600
```

Then run:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start saat-attendance-worker:*
```

### 10. Cron Jobs Setup
Add to crontab (`sudo crontab -e`):
```bash
* * * * * cd /path/to/app && php artisan schedule:run >> /dev/null 2>&1
```

### 11. Monitoring & Logging
- [ ] Set up application monitoring (e.g., Sentry, Bugsnag)
- [ ] Configure log rotation for Laravel logs
- [ ] Set up server monitoring (CPU, Memory, Disk)
- [ ] Configure uptime monitoring
- [ ] Set up database query logging (temporarily)
- [ ] Enable slow query logging
- [ ] Set up alerts for critical errors

### 12. Backup Strategy
- [ ] Set up automated database backups
- [ ] Configure backup retention policy
- [ ] Set up file storage backups
- [ ] Test backup restoration process
- [ ] Document backup procedures

#### Backup Cron Job Example
```bash
# Daily database backup at 2 AM
0 2 * * * /usr/bin/mysqldump -u username -p'password' database_name | gzip > /backups/db_$(date +\%Y\%m\%d).sql.gz

# Keep only last 7 days
0 3 * * * find /backups -name "db_*.sql.gz" -mtime +7 -delete
```

## Post-Deployment Verification

### 13. Functionality Testing
- [ ] Test login functionality
- [ ] Test attendance check-in/check-out
- [ ] Test QR code generation and scanning
- [ ] Test sales and machine creation
- [ ] Test stock management operations
- [ ] Test filter replacement workflows
- [ ] Test notification system
- [ ] Test data exports
- [ ] Test user permissions and roles

### 14. Performance Testing
- [ ] Run load testing
- [ ] Check page load times
- [ ] Verify database query performance
- [ ] Test under concurrent users
- [ ] Monitor memory usage
- [ ] Check queue processing speed

### 15. Security Audit
- [ ] Run security scanner (e.g., OWASP ZAP)
- [ ] Test authentication and authorization
- [ ] Verify HTTPS is enforced
- [ ] Check for exposed sensitive data
- [ ] Test file upload security
- [ ] Verify API rate limiting

## Maintenance

### 16. Regular Maintenance Tasks
- [ ] Weekly: Review error logs
- [ ] Weekly: Check disk space
- [ ] Weekly: Review slow queries
- [ ] Monthly: Update dependencies (`composer update`)
- [ ] Monthly: Review and optimize database
- [ ] Monthly: Test backup restoration
- [ ] Quarterly: Security audit
- [ ] Quarterly: Performance review

### 17. Update Procedures
```bash
# Before updating
php artisan down

# Pull latest code
git pull origin main

# Update dependencies
composer install --optimize-autoloader --no-dev
npm install && npm run build

# Run migrations
php artisan migrate --force

# Clear caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# Recache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Restart services
sudo supervisorctl restart saat-attendance-worker:*

# Bring application back up
php artisan up
```

## Emergency Procedures

### 18. Rollback Plan
- [ ] Document current version/commit hash
- [ ] Keep previous release available
- [ ] Have database backup ready
- [ ] Know how to restore from backup
- [ ] Test rollback procedure

### 19. Incident Response
- [ ] Define critical vs non-critical issues
- [ ] Create escalation procedures
- [ ] Document emergency contacts
- [ ] Have debugging procedures ready
- [ ] Know how to enable maintenance mode

## Documentation

### 20. Project Documentation
- [ ] Update README.md with deployment instructions
- [ ] Document environment variables
- [ ] Create user manual
- [ ] Document API endpoints (if applicable)
- [ ] Create troubleshooting guide
- [ ] Document backup and restore procedures

## Production Environment Variables

### Required Variables
```env
APP_NAME="SAAT Attendance System"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=saat_attendance
DB_USERNAME=
DB_PASSWORD=

CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=
MAIL_FROM_NAME="${APP_NAME}"

# Telegram (Optional)
TELEGRAM_BOT_TOKEN=
TELEGRAM_CHAT_ID=
```

## Final Checklist

- [ ] All environment variables configured
- [ ] Database migrations completed
- [ ] File permissions set correctly
- [ ] Queue workers running
- [ ] Cron jobs configured
- [ ] SSL certificate installed
- [ ] Backups configured and tested
- [ ] Monitoring enabled
- [ ] All tests passing
- [ ] Performance optimized
- [ ] Security hardened
- [ ] Documentation updated
- [ ] Team trained on maintenance procedures

---

## Support Contacts

- **Developer:** [Your Name/Team]
- **Server Admin:** [Admin Contact]
- **Emergency Contact:** [Emergency Number]

## Useful Commands Reference

```bash
# Check application status
php artisan about

# Clear all caches
php artisan optimize:clear

# Recache everything
php artisan optimize

# Check queue status
php artisan queue:work --once

# Run scheduled tasks manually
php artisan schedule:run

# Create super admin user
php artisan tinker
>>> $user = User::find(1);
>>> $user->is_super_admin = true;
>>> $user->save();

# Check database connection
php artisan db:show

# View failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all
```

---

**Last Updated:** January 9, 2026
**Version:** 1.0
