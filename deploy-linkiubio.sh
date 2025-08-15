#!/bin/bash

# ============================================
# SCRIPT DE DEPLOY LINKIU.BIO
# Ejecutar desde tu máquina local
# ============================================

set -e

# Configuración
VPS_IP="162.240.163.188"
VPS_USER="root"
VPS_PORT="22022"
APP_DIR="/var/www/linkiubio"
APP_USER="linkiubio"

# Colores
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

print_status() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Verificar que estamos en el directorio correcto
if [ ! -f "composer.json" ] || [ ! -f "artisan" ]; then
    print_error "Este script debe ejecutarse desde el directorio raíz del proyecto Laravel"
    exit 1
fi

echo "🚀 Iniciando deploy de Linkiu.bio al VPS..."
echo "============================================"

# ============================================
# 1. PREPARAR ARCHIVOS LOCALMENTE
# ============================================
print_status "Preparando archivos para deploy..."

# Crear directorio temporal
TEMP_DIR=$(mktemp -d)
print_status "Directorio temporal: $TEMP_DIR"

# Copiar archivos del proyecto (excluyendo archivos innecesarios)
rsync -av \
    --exclude='.git' \
    --exclude='node_modules' \
    --exclude='.env' \
    --exclude='storage/app' \
    --exclude='storage/framework/cache' \
    --exclude='storage/framework/sessions' \
    --exclude='storage/framework/views' \
    --exclude='storage/logs' \
    --exclude='vendor' \
    --exclude='public/storage' \
    --exclude='*.log' \
    . "$TEMP_DIR/"

# ============================================
# 2. SUBIR ARCHIVOS AL VPS
# ============================================
print_status "Subiendo archivos al VPS..."

# Crear backup en el VPS si existe la aplicación
ssh -p $VPS_PORT $VPS_USER@$VPS_IP "
if [ -d '$APP_DIR' ]; then
    echo '📦 Creando backup...'
    mkdir -p /var/backups/linkiubio
    tar -czf /var/backups/linkiubio/backup-\$(date +%Y%m%d-%H%M%S).tar.gz -C $APP_DIR .
fi
"

# Subir archivos nuevos
print_status "Transfiriendo archivos..."
rsync -avz --delete -e "ssh -p $VPS_PORT" "$TEMP_DIR/" $VPS_USER@$VPS_IP:$APP_DIR/

# ============================================
# 3. CONFIGURAR EN EL VPS
# ============================================
print_status "Configurando aplicación en VPS..."

ssh -p $VPS_PORT $VPS_USER@$VPS_IP "
set -e

cd $APP_DIR

echo '🔐 Configurando permisos...'
chown -R $APP_USER:www-data .
chmod -R 755 .
chmod -R 775 storage bootstrap/cache

echo '📦 Instalando dependencias PHP...'
sudo -u $APP_USER composer install --optimize-autoloader --no-dev --no-interaction

echo '📦 Instalando dependencias Node.js...'
sudo -u $APP_USER npm install

echo '🏗️ Building assets...'
sudo -u $APP_USER npm run build

echo '🔗 Creando storage link...'
sudo -u $APP_USER php artisan storage:link

# Crear directorios de storage necesarios
mkdir -p storage/app/public/system
mkdir -p storage/app/public/tickets
mkdir -p storage/app/public/avatars
mkdir -p storage/app/public/products
mkdir -p storage/app/public/categories
mkdir -p storage/app/public/sliders
chmod -R 775 storage/app/public

echo '⚡ Optimizando aplicación...'
sudo -u $APP_USER php artisan config:cache
sudo -u $APP_USER php artisan route:cache
sudo -u $APP_USER php artisan view:cache

echo '🔄 Reiniciando servicios...'
systemctl reload nginx
systemctl reload php8.2-fpm

echo '✅ Deploy completado!'
"

# ============================================
# 4. MIGRACIÓN DE BASE DE DATOS
# ============================================
print_warning "¿Ejecutar migraciones de base de datos? (y/N)"
read -r response
if [[ "$response" =~ ^[Yy]$ ]]; then
    print_status "Ejecutando migraciones..."
    ssh -p $VPS_PORT $VPS_USER@$VPS_IP "
        cd $APP_DIR
        sudo -u $APP_USER php artisan migrate --force
    "
fi

# ============================================
# 5. LIMPIAR ARCHIVOS TEMPORALES
# ============================================
print_status "Limpiando archivos temporales..."
rm -rf "$TEMP_DIR"

# ============================================
# 6. VERIFICAR ESTADO
# ============================================
print_status "Verificando estado de la aplicación..."
ssh -p $VPS_PORT $VPS_USER@$VPS_IP "
    cd $APP_DIR
    echo '🔍 Estado de Nginx:'
    systemctl status nginx --no-pager -l
    echo ''
    echo '🔍 Estado de PHP-FPM:'
    systemctl status php8.2-fpm --no-pager -l
    echo ''
    echo '🔍 Logs recientes de Nginx:'
    tail -5 /var/log/nginx/error.log
"

echo ""
print_status "============================================"
print_status "🎉 DEPLOY COMPLETADO!"
print_status "============================================"
echo ""
echo -e "${BLUE}Próximos pasos si es el primer deploy:${NC}"
echo "1. Configurar archivo .env en el VPS"
echo "2. Ejecutar migraciones: php artisan migrate"
echo "3. Crear usuario super admin: php artisan make:super-admin"
echo "4. Configurar SSL: certbot --nginx -d linkiu.bio -d www.linkiu.bio"
echo ""
echo -e "${GREEN}La aplicación debería estar disponible en: https://linkiu.bio${NC}"
