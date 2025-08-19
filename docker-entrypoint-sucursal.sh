#!/bin/bash
set -e

cd /var/www/html

# Instala dependencias
if [ ! -d "vendor" ]; then
    composer install --no-dev --optimize-autoloader --no-interaction
fi

# Configura .env específico de sucursal
if [ ! -f .env ]; then
    cp .env.sucursal .env
    sed -i "s/SUCURSAL_ID=0/SUCURSAL_ID=$SUCURSAL_ID/" .env
    sed -i "s/DB_DATABASE=sucursal_0/DB_DATABASE=sucursal_$SUCURSAL_ID/" .env
    php artisan key:generate
fi

# Espera a la base de datos
while ! nc -z $DB_HOST $DB_PORT; do
    echo "Esperando a la base de datos de sucursal..."
    sleep 2
done

# Optimización
php artisan optimize:clear
php artisan optimize

apache2-foreground
