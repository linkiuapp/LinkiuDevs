#!/bin/bash

# Script para arreglar permisos en producción
echo "🔧 Arreglando permisos de Laravel en producción..."

# Dar permisos de escritura a storage y bootstrap/cache
sudo chmod -R 775 storage/
sudo chmod -R 775 bootstrap/cache/

# Cambiar propietario a www-data (usuario del servidor web)
sudo chown -R www-data:www-data storage/
sudo chown -R www-data:www-data bootstrap/cache/

# Limpiar cachés de Laravel
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Recrear cachés optimizados
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "✅ Permisos arreglados correctamente!"