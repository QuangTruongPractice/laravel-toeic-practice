#!/bin/bash

# Laravel TOEIC Platform - Deployment Script
# Usage: ./deploy.sh [production|staging]

set -e  # Exit on error

# Color codes for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Configuration
DEPLOY_USER="deploy"
DEPLOY_PATH="/home/deploy/laravel-toeic"
RELEASE_PATH="${DEPLOY_PATH}/releases/$(date +%Y%m%d_%H%M%S)"
CURRENT_PATH="${DEPLOY_PATH}/current"
ENV_FILE="${DEPLOY_PATH}/.env"
ENVIRONMENT="${1:-production}"
BRANCH="${2:-main}"

echo -e "${YELLOW}=== Laravel TOEIC Platform Deployment ===${NC}"
echo -e "Environment: ${ENVIRONMENT}"
echo -e "Branch: ${BRANCH}"
echo ""

# Function to log messages
log() {
    echo -e "${GREEN}[$(date +'%Y-%m-%d %H:%M:%S')]${NC} $1"
}

error() {
    echo -e "${RED}[ERROR]${NC} $1" >&2
}

warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

# Check if running as correct user
if [ "$(whoami)" != "$DEPLOY_USER" ] && [ "$(whoami)" != "root" ]; then
    error "This script must be run as $DEPLOY_USER or root"
    exit 1
fi

# 1. Create release directory
log "Creating release directory: $RELEASE_PATH"
mkdir -p "$RELEASE_PATH"

# 2. Clone or pull repository
log "Pulling latest code from Git..."
if [ -d "${CURRENT_PATH}/.git" ]; then
    cd "$CURRENT_PATH"
    git fetch origin
    git reset --hard origin/"$BRANCH"
else
    git clone --branch "$BRANCH" https://github.com/your-username/laravel-toeic-practice.git "$RELEASE_PATH"
fi

cd "$RELEASE_PATH" || exit 1

# 3. Install dependencies
log "Installing PHP dependencies..."
composer install --prefer-dist --no-dev --no-interaction --no-progress

log "Installing Node dependencies..."
npm ci

# 4. Build frontend assets
log "Building frontend assets..."
npm run build

# 5. Copy environment file if it exists
if [ -f "$ENV_FILE" ]; then
    log "Copying environment configuration..."
    cp "$ENV_FILE" "$RELEASE_PATH/.env"
else
    warning ".env file not found in $DEPLOY_PATH"
    warning "Please copy .env.production.example to .env manually"
    cp "$RELEASE_PATH/.env.production.example" "$RELEASE_PATH/.env"
fi

# 6. Generate app key if needed
log "Ensuring APP_KEY is set..."
if ! grep -q "APP_KEY=base64" "$RELEASE_PATH/.env"; then
    php artisan key:generate
fi

# 7. Database migrations
log "Running database migrations..."
php artisan migrate --force

# 8. Optimize application
log "Optimizing application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 9. Create storage link
log "Creating storage symbolic link..."
php artisan storage:link

# 10. Set permissions
log "Setting correct permissions..."
sudo chown -R www-data:www-data "$RELEASE_PATH"
sudo chmod -R 755 "$RELEASE_PATH"
sudo chmod -R 775 "$RELEASE_PATH/storage"
sudo chmod -R 775 "$RELEASE_PATH/bootstrap/cache"

# 11. Switch to new release
log "Switching to new release..."
if [ -L "$CURRENT_PATH" ]; then
    rm "$CURRENT_PATH"
fi
ln -s "$RELEASE_PATH" "$CURRENT_PATH"

# 12. Restart services
log "Restarting services..."
sudo systemctl restart php8.3-fpm nginx
sudo supervisorctl restart toeic-queue:* || warning "Failed to restart queue workers"

# 13. Cleanup old releases
log "Cleaning up old releases..."
cd "$DEPLOY_PATH/releases" || exit 1
ls -t | tail -n +4 | xargs -r rm -rf

# 14. Warm up caches
log "Warming up application caches..."
php artisan event:cache
php artisan model:cache

# 15. Health check
log "Performing health check..."
if curl -f -s https://"$ENVIRONMENT".your-domain.com/api/health > /dev/null; then
    log "✓ Health check passed!"
else
    warning "Health check may have failed, please verify manually"
fi

log ""
log "✅ Deployment completed successfully!"
log "Released at: $RELEASE_PATH"
log "Current symlink points to: $(readlink $CURRENT_PATH)"

# Send deployment notification (optional)
if [ -n "$SLACK_WEBHOOK" ]; then
    log "Sending deployment notification to Slack..."
    curl -X POST "$SLACK_WEBHOOK" \
        -H 'Content-Type: application/json' \
        -d "{\"text\": \"✅ Deployment successful on $ENVIRONMENT\", \"blocks\": [{\"type\": \"section\", \"text\": {\"type\": \"mrkdwn\", \"text\": \"*Deployment Status*\nEnvironment: $ENVIRONMENT\nBranch: $BRANCH\nTime: $(date)\nStatus: ✅ Success\"}}]}"
fi

echo ""
echo -e "${YELLOW}=== Deployment Summary ===${NC}"
echo "Environment: $ENVIRONMENT"
echo "Branch: $BRANCH"
echo "Release: $(basename $RELEASE_PATH)"
echo "Deployed at: $(date)"
echo ""
echo "Next steps:"
echo "1. Verify application: curl https://your-domain.com"
echo "2. Check logs: tail -f $DEPLOY_PATH/storage/logs/laravel.log"
echo "3. Monitor queue: php artisan queue:monitor"
echo ""
