#!/bin/bash

# ============================================
# CONFIGURAR .ENV EN VPS
# Ejecutar EN el VPS como root
# ============================================

set -e

APP_DIR="/var/www/linkiubio"
APP_USER="linkiubio"

# Colores
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

print_status() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

# Leer información de la instalación
if [ -f "/root/linkiubio-info.txt" ]; then
    source <(grep = /root/linkiubio-info.txt | sed 's/: /=/' | sed 's/ /_/g')
fi

print_status "Configurando archivo .env para producción..."

# Generar APP_KEY si no existe
cd $APP_DIR
if [ ! -f ".env" ]; then
    APP_KEY=$(sudo -u $APP_USER php artisan key:generate --show)
else
    APP_KEY=$(grep APP_KEY .env | cut -d'=' -f2)
fi

# Crear archivo .env optimizado
cat > .env << EOF
# ============================================
# LINKIU.BIO - PRODUCCIÓN VPS
# ============================================

# Aplicación
APP_NAME="Linkiu Bio"
APP_ENV=production
APP_KEY=$APP_KEY
APP_DEBUG=false
APP_URL=https://linkiu.bio

# Localización
APP_LOCALE=es
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=es_ES

# Mantenimiento y rendimiento
APP_MAINTENANCE_DRIVER=file
PHP_CLI_SERVER_WORKERS=4

# ============================================
# LOGS Y DEBUGGING
# ============================================
LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

# ============================================
# BASE DE DATOS
# ============================================
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=${Database:-linkiubio_prod}
DB_USERNAME=${DB_User:-linkiubio_user}
DB_PASSWORD=${DB_Password:-}

# ============================================
# SESIONES Y CACHE
# ============================================
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=true
SESSION_PATH=/
SESSION_DOMAIN=linkiu.bio

CACHE_STORE=database
QUEUE_CONNECTION=database

# ============================================
# ALMACENAMIENTO LOCAL
# ============================================
FILESYSTEM_DISK=public
MEDIA_DRIVER=local

# Variables para compatibilidad (NO se usan)
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=
AWS_BUCKET=
AWS_URL=

# ============================================
# AUTENTICACIÓN
# ============================================
AUTH_MODEL=App\Shared\Models\User

# ============================================
# REDIS (OPCIONAL)
# ============================================
REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# ============================================
# EMAIL CORPORATIVO
# ============================================
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=TU_EMAIL_CORPORATIVO@tu-dominio.com
MAIL_PASSWORD=TU_APP_PASSWORD_GMAIL
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@linkiu.bio"
MAIL_FROM_NAME="\${APP_NAME}"

# ============================================
# NOTIFICACIONES EN TIEMPO REAL (PUSHER)
# ============================================
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=2026135
PUSHER_APP_KEY=f0468741ee9a31070624
PUSHER_APP_SECRET=a94ead20c269c3121481
PUSHER_APP_CLUSTER=us2
VITE_PUSHER_APP_KEY="\${PUSHER_APP_KEY}"
VITE_PUSHER_APP_CLUSTER="\${PUSHER_APP_CLUSTER}"

# ============================================
# ASSETS DEL SISTEMA
# ============================================
APP_LOGO=system/logo_1753385645.png
APP_FAVICON=system/favicon_1753386590.png

# ============================================
# CONFIGURACIONES DE SEGURIDAD
# ============================================
TRUSTED_PROXIES=
TRUSTED_HOSTS=linkiu.bio,www.linkiu.bio

# ============================================
# CONFIGURACIÓN DE VITE PARA PRODUCCIÓN
# ============================================
VITE_APP_NAME="\${APP_NAME}"
VITE_APP_URL="\${APP_URL}"
EOF

# Configurar permisos
chown $APP_USER:www-data .env
chmod 640 .env

print_status "✅ Archivo .env configurado"
print_status "============================================"
echo ""
echo -e "${BLUE}Información configurada:${NC}"
echo -e "${YELLOW}Database:${NC} ${Database:-linkiubio_prod}"
echo -e "${YELLOW}DB User:${NC} ${DB_User:-linkiubio_user}"
echo -e "${YELLOW}DB Password:${NC} ${DB_Password:-[CONFIGURADO]}"
echo ""
echo -e "${YELLOW}⚠️  IMPORTANTE:${NC}"
echo "1. Edita el archivo .env y configura:"
echo "   - MAIL_USERNAME (tu email corporativo)"
echo "   - MAIL_PASSWORD (tu app password de Gmail)"
echo ""
echo "2. Ejecuta las migraciones:"
echo "   cd $APP_DIR && sudo -u $APP_USER php artisan migrate"
echo ""
echo "3. Crea el usuario super admin:"
echo "   cd $APP_DIR && sudo -u $APP_USER php artisan make:super-admin"
echo ""
echo "4. Configura SSL:"
echo "   certbot --nginx -d linkiu.bio -d www.linkiu.bio"
