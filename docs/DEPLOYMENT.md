# Deployment & Operations Guide

> 📋 Lesson 12: Deployment & DevOps  
> Complete guide for deploying Laravel TOEIC Platform to production and maintaining it.

## 📖 Table of Contents

1. [Pre-Deployment Checklist](#pre-deployment-checklist)
2. [Server Setup](#server-setup)
3. [Deployment Process](#deployment-process)
4. [Task Scheduling](#task-scheduling)
5. [Queue Workers](#queue-workers)
6. [Monitoring & Maintenance](#monitoring--maintenance)
7. [Troubleshooting](#troubleshooting)

---

## Pre-Deployment Checklist

### Application Preparation

- [ ] All tests pass: `php artisan test --parallel`
- [ ] Code style is correct: `php artisan pint --test`
- [ ] Environment variables are set in `.env.production`
- [ ] Database migrations are tested locally
- [ ] Sensitive data is not hardcoded (check for credentials in code)
- [ ] All dependencies are installed: `composer install --prefer-dist --no-dev`
- [ ] Frontend assets are built: `npm run build`
- [ ] Cache, routes, views are optimized (will do on server)

### Server Requirements

- **OS**: Ubuntu 22.04 LTS or similar
- **PHP**: 8.3 or 8.4
- **Web Server**: Nginx (recommended) or Apache
- **Database**: MySQL 8.0+ or PostgreSQL 14+
- **Redis**: For caching and queues
- **Composer**: Latest version
- **Node.js**: 20+ (for asset pipeline)

---

## Server Setup

### 1. SSH Access & Initial Setup

```bash
# Connect to your server
ssh deploy@your-server.com

# Update system packages
sudo apt update && sudo apt upgrade -y

# Install required software
sudo apt install -y php8.3-fpm php8.3-mysql php8.3-redis php8.3-dom \
  php8.3-curl php8.3-mbstring php8.3-zip php8.3-bcmath \
  nginx mysql-client redis-server supervisor git curl
```

### 2. Install Composer

```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
composer --version
```

### 3. Install Node.js

```bash
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs
node --version && npm --version
```

### 4. Create Application User

```bash
# Create deploy user
sudo useradd -m -s /bin/bash deploy
sudo usermod -aG www-data deploy

# Create application directory
sudo mkdir -p /home/deploy/laravel-toeic
sudo chown -R deploy:deploy /home/deploy/laravel-toeic
```

### 5. Configure Web Server (Nginx)

Create `/etc/nginx/sites-available/toeic-platform`:

```nginx
server {
    listen 80;
    listen [::]:80;
    
    server_name your-domain.com www.your-domain.com;
    root /home/deploy/laravel-toeic/public;
    
    # Redirect HTTP to HTTPS
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    
    server_name your-domain.com www.your-domain.com;
    root /home/deploy/laravel-toeic/public;
    
    # SSL Certificate (use Let's Encrypt)
    ssl_certificate /etc/letsencrypt/live/your-domain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/your-domain.com/privkey.pem;
    
    # Security headers
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;
    
    index index.php;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    location ~ /\.env {
        deny all;
    }
    
    client_max_body_size 100M;
}
```

Enable the site:

```bash
sudo ln -s /etc/nginx/sites-available/toeic-platform /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

### 6. Setup SSL Certificate (Let's Encrypt)

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot certonly --nginx -d your-domain.com -d www.your-domain.com
```

### 7. Setup Database

```bash
# Login to MySQL
mysql -u root -p

# Create database and user
CREATE DATABASE toeic_production CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'toeic_user'@'localhost' IDENTIFIED BY 'your-secure-password';
GRANT ALL PRIVILEGES ON toeic_production.* TO 'toeic_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 8. Configure Redis

```bash
# Edit Redis config
sudo nano /etc/redis/redis.conf

# Add or uncomment:
# appendonly yes
# maxmemory 256mb
# maxmemory-policy allkeys-lru

sudo systemctl restart redis-server
redis-cli ping  # Should return PONG
```

---

## Deployment Process

### 1. Clone Repository

```bash
cd /home/deploy/laravel-toeic
git clone https://github.com/your-username/laravel-toeic-practice.git .
```

### 2. Install Dependencies

```bash
# PHP dependencies
composer install --prefer-dist --no-dev --no-progress --no-interaction

# Node dependencies
npm ci
npm run build
```

### 3. Setup Environment & Keys

```bash
# Copy production environment file
cp .env.production.example .env

# Edit .env with your server settings
nano .env

# Generate app key
php artisan key:generate

# Set proper permissions
sudo chown -R www-data:www-data /home/deploy/laravel-toeic
sudo chmod -R 755 /home/deploy/laravel-toeic
sudo chmod -R 775 /home/deploy/laravel-toeic/storage
sudo chmod -R 775 /home/deploy/laravel-toeic/bootstrap/cache
```

### 4. Database Setup

```bash
php artisan migrate --force
php artisan db:seed --class=ProductionSeeder
```

### 5. Optimize Application

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link
```

### 6. Verify Installation

```bash
# Test PHP connection
php -r "phpinfo();" | head -20

# Test Laravel
php artisan tinker
>>> echo "Hello from Laravel!"
```

---

## Task Scheduling

### Setup Cron Job

The Laravel task scheduler requires a single cron entry:

```bash
# Edit crontab
sudo crontab -e -u deploy

# Add this line:
* * * * * cd /home/deploy/laravel-toeic && php artisan schedule:run >> /dev/null 2>&1
```

### Scheduled Tasks

The following tasks are configured in `app/Console/Kernel.php`:

| Task | Frequency | Purpose |
|------|-----------|---------|
| `stats:calculate` | Daily (00:00) | Calculate user statistics and cache results |
| `cleanup:sessions` | Weekly (Monday 02:00) | Clean up expired sessions |
| `cleanup:imports` | Weekly (Sunday 03:00) | Remove old temporary import files |
| `email:daily-summary` | Daily (09:00) | Send admin summary emails |

### Monitor Scheduled Tasks

```bash
# View all scheduled commands
php artisan schedule:list

# Run scheduler in debug mode
php artisan schedule:work
```

---

## Queue Workers

### Setup Supervisor Configuration

Create `/etc/supervisor/conf.d/toeic-queue.conf`:

```ini
[program:toeic-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /home/deploy/laravel-toeic/artisan queue:work redis --sleep=3 --tries=3 --timeout=90
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=deploy
numprocs=4
redirect_stderr=true
stdout_logfile=/var/log/toeic-queue.log
stopwait=3600
```

Enable and start:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start toeic-queue:*
sudo supervisorctl status

# Monitor logs
tail -f /var/log/toeic-queue.log
```

### Horizon (Optional - Advanced Queue Monitoring)

```bash
# Install Horizon for queue monitoring UI
composer require laravel/horizon

# Publish Horizon assets
php artisan horizon:install

# Configure Supervisor for Horizon
# Similar to above but run: php artisan horizon
```

---

## Monitoring & Maintenance

### 1. Log Monitoring

```bash
# View application logs
tail -f /home/deploy/laravel-toeic/storage/logs/laravel.log

# Real-time log streaming with Laravel Pail
php artisan pail

# Filter by level
php artisan pail --level=error
```

### 2. Health Checks

Create a simple health check endpoint in `routes/api.php`:

```php
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now(),
        'database' => DB::connection()->getDatabaseName() ? 'ok' : 'error',
        'redis' => Cache::store('redis')->get('test') !== null ? 'ok' : 'error',
    ]);
});
```

Monitor with external service:

```bash
# Example: every 5 minutes
*/5 * * * * curl -f https://your-domain.com/api/health || alert
```

### 3. Database Backups

```bash
# Create backup directory
mkdir -p /home/deploy/backups

# Add to crontab (daily backup at 2 AM)
0 2 * * * mysqldump -u toeic_user -p'your-password' toeic_production | gzip > /home/deploy/backups/toeic_$(date +\%Y\%m\%d_\%H\%M\%S).sql.gz

# Keep only last 30 days
0 3 * * * find /home/deploy/backups -name "*.sql.gz" -mtime +30 -delete
```

### 4. Disk Space Monitoring

```bash
# Check disk usage
df -h

# Clean old logs (if not using rotating logs)
find /home/deploy/laravel-toeic/storage/logs -name "*.log" -mtime +30 -delete

# Clear Laravel cache
php artisan cache:clear
php artisan view:clear
```

### 5. Performance Optimization

```bash
# Monitor queue jobs
php artisan queue:failed  # See failed jobs
php artisan queue:retry all  # Retry failed jobs

# Check Redis memory
redis-cli INFO memory

# Clear old query cache
php artisan tinker
>>> Cache::flush()
```

---

## Troubleshooting

### Common Issues

#### 1. **Permission Denied on storage/**

```bash
sudo chown -R www-data:www-data /home/deploy/laravel-toeic/storage
sudo chmod -R 775 /home/deploy/laravel-toeic/storage
```

#### 2. **502 Bad Gateway (Nginx)**

```bash
# Check PHP-FPM status
sudo systemctl status php8.3-fpm

# Check Nginx error log
sudo tail -f /var/log/nginx/error.log

# Restart services
sudo systemctl restart php8.3-fpm nginx
```

#### 3. **Queue Jobs Not Processing**

```bash
# Check if Supervisor is running
sudo supervisorctl status

# Restart queue workers
sudo supervisorctl restart toeic-queue:*

# Check for failed jobs
php artisan queue:failed
php artisan queue:retry all
```

#### 4. **Database Connection Error**

```bash
# Test database connection
php artisan tinker
>>> DB::connection()->getPdo()

# Check environment variables
cat /home/deploy/laravel-toeic/.env | grep DB_
```

#### 5. **Redis Connection Error**

```bash
# Test Redis connection
redis-cli ping

# Check if Redis is running
sudo systemctl status redis-server

# Verify Redis configuration
redis-cli CONFIG GET "maxmemory"
```

---

## Deployment Rollback

### Quick Rollback Strategy

```bash
# Keep multiple versions of the app
mkdir -p /home/deploy/releases

# Before deploying, create a backup
cp -r /home/deploy/laravel-toeic /home/deploy/releases/v$(date +%s)

# If something goes wrong, restore from backup
cp -r /home/deploy/releases/v<timestamp>/* /home/deploy/laravel-toeic/

# Migrate down if needed
php artisan migrate:rollback
```

---

## Updating the Application

```bash
# Pull latest code
git pull origin main

# Install new dependencies
composer install --prefer-dist --no-dev

# Build frontend
npm run build

# Run migrations
php artisan migrate --force

# Optimize cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Restart queue workers
sudo supervisorctl restart toeic-queue:*
```

---

## Helpful Commands

```bash
# Database
php artisan migrate              # Run migrations
php artisan migrate:rollback     # Rollback migrations
php artisan tinker              # Interactive shell
php artisan db:seed             # Seed database

# Caching
php artisan cache:clear         # Clear all cache
php artisan route:cache         # Cache routes
php artisan config:cache        # Cache config
php artisan view:cache          # Cache views

# Scheduling
php artisan schedule:run        # Run scheduler once
php artisan schedule:work       # Run scheduler in foreground
php artisan schedule:list       # List all scheduled tasks

# Queues
php artisan queue:work          # Process jobs
php artisan queue:failed        # List failed jobs
php artisan queue:retry all     # Retry all failed jobs

# Maintenance
php artisan down                # Put app in maintenance mode
php artisan up                  # Bring app back online
php artisan storage:link        # Create symbolic link to storage
```

---

## Monitoring Services (Recommended)

- **Error Tracking**: Sentry (configured in .env)
- **Performance**: New Relic, Datadog
- **Uptime Monitoring**: Pingdom, Uptime Robot
- **Log Management**: ELK Stack, Loggly
- **Email Delivery**: SendGrid dashboard

---

## Resources

- [Laravel Deployment Guide](https://laravel.com/docs/deployment)
- [Nginx Configuration](https://nginx.org/en/docs/)
- [PHP-FPM Setup](https://www.php.net/manual/en/install.fpm.php)
- [Supervisor Documentation](http://supervisord.org/)
- [Redis Getting Started](https://redis.io/docs/getting-started/)

---

✅ **Deployment & Operations Complete!**  
Your Laravel TOEIC Platform is now production-ready with:
- ✅ Automated task scheduling
- ✅ Queue worker management
- ✅ CI/CD pipeline with GitHub Actions
- ✅ Comprehensive deployment guide
- ✅ Monitoring and maintenance procedures
