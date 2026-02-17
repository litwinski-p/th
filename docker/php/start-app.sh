#!/bin/sh

set -eu

cd /var/www/html

if command -v git >/dev/null 2>&1; then
    git config --global --add safe.directory /var/www/html || true
fi

composer install --no-interaction --prefer-dist --optimize-autoloader

exec apache2-foreground
