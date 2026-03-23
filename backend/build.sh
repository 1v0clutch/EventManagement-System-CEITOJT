#!/usr/bin/env bash
# Render.com build script — runs on every deploy
set -e

echo "==> Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader

echo "==> Running database migrations..."
php artisan migrate --force

echo "==> Caching config, routes, and views..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "==> Creating storage symlink..."
php artisan storage:link

echo "==> Build complete!"
