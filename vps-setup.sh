#!/bin/bash

# ============================================
# SCRIPT DE INSTALACIÓN LINKIU.BIO - VPS
# Ubuntu 22.04 + PHP 8.2 + MySQL + Nginx
# ============================================

set -e  # Salir si hay errores

echo "🚀 Iniciando instalación de Linkiu.bio en VPS..."
echo "============================================"

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Variables de configuración
DOMAIN="linkiu.bio"
DB_NAME="linkiubio_prod"
DB_USER="linkiubio_user"
DB_PASSWORD=""  # Se generará automáticamente
APP_USER="linkiubio"
APP_DIR="/var/www/linkiubio"

# Función para mostrar mensajes
print_status() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# ============================================
# 1. ACTUALIZACIÓN DEL SISTEMA
# ============================================
print_status "Actualizando sistema..."
apt update && apt upgrade -y

# ============================================
# 2. INSTALACIÓN DE DEPENDENCIAS BÁSICAS
# ============================================
print_status "Instalando dependencias básicas..."
apt install -y \
    curl \
    wget \
    git \
    unzip \
    software-properties-common \
    apt-transport-https \
    ca-certificates \
    gnupg \
    lsb-release \
    ufw \
    fail2ban

# ============================================
# 3. INSTALACIÓN DE PHP 8.2
# ============================================
print_status "Instalando PHP 8.2..."
add-apt-repository ppa:ondrej/php -y
apt update
apt install -y \
    php8.2 \
    php8.2-fpm \
    php8.2-mysql \
    php8.2-xml \
    php8.2-mbstring \
    php8.2-curl \
    php8.2-zip \
    php8.2-bcmath \
    php8.2-gd \
    php8.2-intl \
    php8.2-soap \
    php8.2-redis \
    php8.2-cli

# ============================================
# 4. INSTALACIÓN DE COMPOSER
# ============================================
print_status "Instalando Composer..."
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
chmod +x /usr/local/bin/composer

# ============================================
# 5. INSTALACIÓN DE NODE.js Y NPM
# ============================================
print_status "Instalando Node.js..."
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
apt install -y nodejs

# ============================================
# 6. INSTALACIÓN DE NGINX
# ============================================
print_status "Instalando Nginx..."
apt install -y nginx

# ============================================
# 7. INSTALACIÓN DE MYSQL
# ============================================
print_status "Instalando MySQL..."
apt install -y mysql-server

# Generar password seguro para la base de datos
DB_PASSWORD=$(openssl rand -base64 32)

print_status "Configurando MySQL..."
mysql -e "CREATE DATABASE ${DB_NAME};"
mysql -e "CREATE USER '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASSWORD}';"
mysql -e "GRANT ALL PRIVILEGES ON ${DB_NAME}.* TO '${DB_USER}'@'localhost';"
mysql -e "FLUSH PRIVILEGES;"

# ============================================
# 8. CREACIÓN DE USUARIO DE APLICACIÓN
# ============================================
print_status "Creando usuario de aplicación..."
useradd -m -s /bin/bash ${APP_USER}
usermod -aG www-data ${APP_USER}

# ============================================
# 9. CONFIGURACIÓN DE NGINX
# ============================================
print_status "Configurando Nginx..."
cat > /etc/nginx/sites-available/${DOMAIN} << EOF
server {
    listen 80;
    server_name ${DOMAIN} www.${DOMAIN};
    root ${APP_DIR}/public;
    index index.php index.html;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;

    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types text/plain text/css text/xml text/javascript application/javascript application/xml+rss application/json;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location /storage {
        alias ${APP_DIR}/storage/app/public;
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    location = /favicon.ico {
        access_log off;
        log_not_found off;
    }

    location = /robots.txt {
        access_log off;
        log_not_found off;
    }
}
EOF

# Habilitar el sitio
ln -sf /etc/nginx/sites-available/${DOMAIN} /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default

# ============================================
# 10. CONFIGURACIÓN DE PHP-FPM
# ============================================
print_status "Configurando PHP-FPM..."
sed -i 's/upload_max_filesize = 2M/upload_max_filesize = 20M/' /etc/php/8.2/fpm/php.ini
sed -i 's/post_max_size = 8M/post_max_size = 20M/' /etc/php/8.2/fpm/php.ini
sed -i 's/max_execution_time = 30/max_execution_time = 300/' /etc/php/8.2/fpm/php.ini
sed -i 's/memory_limit = 128M/memory_limit = 256M/' /etc/php/8.2/fpm/php.ini

# ============================================
# 11. CREACIÓN DE DIRECTORIO DE APLICACIÓN
# ============================================
print_status "Creando directorio de aplicación..."
mkdir -p ${APP_DIR}
chown -R ${APP_USER}:www-data ${APP_DIR}
chmod -R 755 ${APP_DIR}

# ============================================
# 12. CONFIGURACIÓN DE FIREWALL
# ============================================
print_status "Configurando firewall..."
ufw --force enable
ufw allow ssh
ufw allow 80
ufw allow 443

# ============================================
# 13. INSTALACIÓN DE CERTBOT (SSL)
# ============================================
print_status "Instalando Certbot para SSL..."
snap install core; snap refresh core
snap install --classic certbot
ln -sf /snap/bin/certbot /usr/bin/certbot

# ============================================
# 14. CONFIGURACIÓN DE SERVICIOS
# ============================================
print_status "Habilitando servicios..."
systemctl enable nginx
systemctl enable php8.2-fpm
systemctl enable mysql
systemctl enable fail2ban

systemctl restart nginx
systemctl restart php8.2-fpm
systemctl restart mysql

# ============================================
# 15. CREACIÓN DE SCRIPT DE DEPLOY
# ============================================
print_status "Creando script de deploy..."
cat > /home/${APP_USER}/deploy.sh << 'EOF'
#!/bin/bash

# Script de deploy para Linkiu.bio
set -e

APP_DIR="/var/www/linkiubio"
BACKUP_DIR="/var/backups/linkiubio"

echo "🚀 Iniciando deploy..."

# Crear backup
mkdir -p ${BACKUP_DIR}
if [ -d "${APP_DIR}" ]; then
    echo "📦 Creando backup..."
    tar -czf ${BACKUP_DIR}/backup-$(date +%Y%m%d-%H%M%S).tar.gz -C ${APP_DIR} .
fi

# Ir al directorio de la aplicación
cd ${APP_DIR}

# Git pull (si existe repositorio)
if [ -d ".git" ]; then
    echo "📥 Actualizando código..."
    git pull origin main
fi

# Composer install
echo "📦 Instalando dependencias PHP..."
composer install --optimize-autoloader --no-dev

# NPM install y build
echo "🏗️ Building assets..."
npm install
npm run build

# Permisos
echo "🔐 Configurando permisos..."
chown -R linkiubio:www-data .
chmod -R 755 .
chmod -R 775 storage bootstrap/cache
chmod -R 777 storage/app/public

# Storage link
php artisan storage:link

# Migraciones
echo "🗄️ Ejecutando migraciones..."
php artisan migrate --force

# Cache
echo "⚡ Optimizando cache..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Queue restart
php artisan queue:restart

echo "✅ Deploy completado!"
EOF

chmod +x /home/${APP_USER}/deploy.sh
chown ${APP_USER}:${APP_USER} /home/${APP_USER}/deploy.sh

# ============================================
# 16. RESUMEN FINAL
# ============================================
print_status "============================================"
print_status "🎉 INSTALACIÓN COMPLETADA!"
print_status "============================================"
echo ""
echo -e "${BLUE}Información importante:${NC}"
echo -e "${YELLOW}Domain:${NC} ${DOMAIN}"
echo -e "${YELLOW}App Directory:${NC} ${APP_DIR}"
echo -e "${YELLOW}App User:${NC} ${APP_USER}"
echo -e "${YELLOW}Database:${NC} ${DB_NAME}"
echo -e "${YELLOW}DB User:${NC} ${DB_USER}"
echo -e "${YELLOW}DB Password:${NC} ${DB_PASSWORD}"
echo ""
echo -e "${BLUE}Próximos pasos:${NC}"
echo "1. Configurar DNS del dominio hacia este servidor"
echo "2. Subir código de la aplicación a ${APP_DIR}"
echo "3. Configurar archivo .env con los datos de arriba"
echo "4. Ejecutar: certbot --nginx -d ${DOMAIN} -d www.${DOMAIN}"
echo "5. Ejecutar: /home/${APP_USER}/deploy.sh"
echo ""
echo -e "${GREEN}Guarda la información de la base de datos en un lugar seguro!${NC}"

# Guardar info en archivo
cat > /root/linkiubio-info.txt << EOF
=== INFORMACIÓN DE INSTALACIÓN LINKIU.BIO ===
Fecha: $(date)
Domain: ${DOMAIN}
App Directory: ${APP_DIR}
App User: ${APP_USER}
Database: ${DB_NAME}
DB User: ${DB_USER}
DB Password: ${DB_PASSWORD}
EOF

echo ""
print_status "Información guardada en: /root/linkiubio-info.txt"
