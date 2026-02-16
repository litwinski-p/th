#!/bin/sh

set -eu

cd /var/www/html

composer install --no-interaction --prefer-dist --optimize-autoloader

exec apache2-foreground
