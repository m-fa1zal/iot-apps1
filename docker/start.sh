#!/bin/bash

# Docker startup script for Railway Laravel IoT Application
set -e

echo "🐳 Starting Docker services for Laravel IoT Application..."

# Start PHP-FPM in background
echo "🐘 Starting PHP-FPM..."
php-fpm -D

# Start Supervisor for background processes
echo "👨‍💼 Starting Supervisor..."
supervisord -c /etc/supervisor/supervisord.conf -n &

# Start Nginx in foreground
echo "🌐 Starting Nginx..."
nginx -g "daemon off;"