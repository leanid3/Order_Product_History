#!/bin/sh
set -e

# Выставляем права на папки
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Создаём директории, если их нет
mkdir -p /var/www/html/storage/framework/views /var/www/html/storage/logs