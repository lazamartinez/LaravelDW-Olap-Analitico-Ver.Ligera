#!/bin/bash
set -e

cd /var/www/html

# Instala dependencias
composer install --no-interaction --prefer-dist --optimize-autoloader

# Crea .env si no existe
if [ ! -f .env ]; then
    if [ -f .env.example ]; then
        cp .env.example .env
    else
        echo "No se encontró .env ni .env.example"
        exit 1
    fi
    php artisan key:generate
fi

# Espera base de datos
while ! nc -z $DB_HOST $DB_PORT; do
    echo "Esperando a la base de datos..."
    sleep 2
done

# Migraciones y seeders
php artisan migrate --force
php artisan db:seed --force || echo "Seeders ya ejecutados, se ignoran duplicados."

# Limpieza y optimización
php artisan optimize:clear
php artisan optimize

# Levanta Apache
apache2-foreground
