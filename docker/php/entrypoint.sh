#!/bin/bash
set -e

if [ ! -f ".env" ]; then
    cp .env.example .env
fi

# Only run initialization for the main app service
if [ "$PROCESS" = "app" ]; then
    echo "Initializing application..."

    # Wait for database
    while ! mysqladmin ping -h"db" -u"${DB_USERNAME}" -p"${DB_PASSWORD}" --silent; do
        echo "Waiting for database connection..."
        sleep 2
    done

    # Run initialization commands
    php artisan key:generate
    php artisan config:cache
    php artisan migrate --force
    php artisan storage:link

    echo "Initialization complete"
fi

# Start Supervisor
echo "Starting Supervisor..."
exec supervisord --nodaemon --configuration /etc/supervisord.conf
