#!/bin/bash

# ============================================
# SCRIPT DE INSTALACIÓN LINKIU.BIO - ALMALINUX 9
# AlmaLinux 9.6 + PHP 8.2 + MySQL + Nginx
# ============================================

set -e  # Salir si hay errores

echo "🚀 Iniciando instalación de Linkiu.bio en AlmaLinux..."
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
print_status "Actualizando sistema AlmaLinux..."
dnf update -y

# ============================================
# 2. INSTALACIÓN DE DEPENDENCIAS BÁSICAS
# ============================================
print_status "Instalando dependencias básicas..."
dnf install -y \
    curl \
    wget \
    git \
    unzip \
    tar \
    epel-release \
    dnf-plugins-core \
    firewalld \
    fail2ban

# Habilitar repositorios adicionales
dnf config-manager --set-enabled crb

# ============================================
# 3. INSTALACIÓN DE REMI REPOSITORY PARA PHP 8.2
# ============================================
print_status "Configurando repositorio Remi para PHP 8.2..."
dnf install -y https://rpms.remirepo.net/enterprise/remi-release-9.rpm
dnf module reset php -y
dnf module enable php:remi-8.2 -y

# ============================================
# 4. INSTALACIÓN DE PHP 8.2
# ============================================
print_status "Instalando PHP 8.2..."
dnf install -y \
    php \
    php-fpm \
    php-mysqlnd \
    php-xml \
    php-mbstring \
    php-curl \
    php-zip \
    php-bcmath \
    php-gd \
    php-intl \
    php-soap \
    php-redis \
    php-cli \
    php-json \
    php-opcache

# ============================================
# 5. INSTALACIÓN DE COMPOSER
# ============================================
print_status "Instalando Composer..."
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
chmod +x /usr/local/bin/composer

# ============================================
# 6. INSTALACIÓN DE NODE.js Y NPM
# ============================================
print_status "Instalando Node.js..."
curl -fsSL https://rpm.nodesource.com/setup_18.x | bash -
dnf install -y nodejs

# ============================================
# 7. INSTALACIÓN DE NGINX
# ============================================
print_status "Instalando Nginx..."
dnf install -y nginx

# ============================================
# 8. INSTALACIÓN DE MYSQL
# ============================================
print_status "Instalando MySQL..."
dnf install -y mysql-server mysql

# ============================================
# 9. CONFIGURACIÓN INICIAL DE SERVICIOS
# ============================================
print_status "Habilitando servicios..."
systemctl enable nginx
systemctl enable php-fpm
systemctl enable mysqld
systemctl enable firewalld
systemctl enable fail2ban

systemctl start mysqld
systemctl start php-fpm

# ============================================
# 10. CONFIGURACIÓN DE MYSQL
# ============================================
print_status "Configurando MySQL..."

# Generar password seguro para la base de datos
DB_PASSWORD=$(openssl rand -base64 32)

# Configuración segura de MySQL
mysql_secure_installation_auto() {
    mysql -e "ALTER USER 'root'@'localhost' IDENTIFIED BY 'TempRootPass123!';"
    mysql -u root -pTempRootPass123! -e "DELETE FROM mysql.user WHERE User='';"
    mysql -u root -pTempRootPass123! -e "DELETE FROM mysql.user WHERE User='root' AND Host NOT IN ('localhost', '127.0.0.1', '::1');"
    mysql -u root -pTempRootPass123! -e "DROP DATABASE IF EXISTS test;"
    mysql -u root -pTempRootPass123! -e "DELETE FROM mysql.db WHERE Db='test' OR Db='test\\_%';"
    mysql -u root -pTempRootPass123! -e "CREATE DATABASE ${DB_NAME};"
    mysql -u root -pTempRootPass123! -e "CREATE USER '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASSWORD}';"
    mysql -u root -pTempRootPass123! -e "GRANT ALL PRIVILEGES ON ${DB_NAME}.* TO '${DB_USER}'@'localhost';"
    mysql -u root -pTempRootPass123! -e "FLUSH PRIVILEGES;"
}

mysql_secure_installation_auto

# ============================================
# 11. CREACIÓN DE USUARIO DE APLICACIÓN
# ============================================
print_status "Creando usuario de aplicación..."
useradd -m -s /bin/bash ${APP_USER}
usermod -aG nginx ${APP_USER}

# ============================================
# 12. CONFIGURACIÓN DE NGINX
# ============================================
print_status "Configurando Nginx..."
cat > /etc/nginx/conf.d/${DOMAIN}.conf << EOF
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
        fastcgi_pass unix:/var/run/php-fpm/www.sock;
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

# ============================================
# 13. CONFIGURACIÓN DE PHP-FPM
# ============================================
print_status "Configurando PHP-FPM..."
sed -i 's/upload_max_filesize = 2M/upload_max_filesize = 20M/' /etc/php.ini
sed -i 's/post_max_size = 8M/post_max_size = 20M/' /etc/php.ini
sed -i 's/max_execution_time = 30/max_execution_time = 300/' /etc/php.ini
sed -i 's/memory_limit = 128M/memory_limit = 256M/' /etc/php.ini

# Configurar PHP-FPM para usar nginx
sed -i 's/user = apache/user = nginx/' /etc/php-fpm.d/www.conf
sed -i 's/group = apache/group = nginx/' /etc/php-fpm.d/www.conf

# ============================================
# 14. CREACIÓN DE DIRECTORIO DE APLICACIÓN
# ============================================
print_status "Creando directorio de aplicación..."
mkdir -p ${APP_DIR}
chown -R ${APP_USER}:nginx ${APP_DIR}
chmod -R 755 ${APP_DIR}

# ============================================
# 15. CONFIGURACIÓN DE FIREWALL
# ============================================
print_status "Configurando firewall..."
systemctl start firewalld
firewall-cmd --permanent --add-service=ssh
firewall-cmd --permanent --add-service=http
firewall-cmd --permanent --add-service=https
firewall-cmd --permanent --add-port=22022/tcp
firewall-cmd --reload

# ============================================
# 16. INSTALACIÓN DE CERTBOT (SSL)
# ============================================
print_status "Instalando Certbot para SSL..."
dnf install -y python3-certbot-nginx

# ============================================
# 17. REINICIAR SERVICIOS
# ============================================
print_status "Reiniciando servicios..."
systemctl restart nginx
systemctl restart php-fpm

# ============================================
# 18. CREACIÓN DE SCRIPT DE DEPLOY
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
chown -R linkiubio:nginx .
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
# 19. CONFIGURAR SELINUX (IMPORTANTE PARA ALMALINUX)
# ============================================
print_status "Configurando SELinux..."
setsebool -P httpd_can_network_connect 1
setsebool -P httpd_execmem 1
semanage fcontext -a -t httpd_exec_t "${APP_DIR}/public(/.*)?"
semanage fcontext -a -t httpd_exec_t "${APP_DIR}/storage(/.*)?"
semanage fcontext -a -t httpd_exec_t "${APP_DIR}/bootstrap/cache(/.*)?"

# ============================================
# 20. RESUMEN FINAL
# ============================================
print_status "============================================"
print_status "🎉 INSTALACIÓN COMPLETADA EN ALMALINUX!"
print_status "============================================"
echo ""
echo -e "${BLUE}Información importante:${NC}"
echo -e "${YELLOW}Sistema:${NC} AlmaLinux 9.6"
echo -e "${YELLOW}Domain:${NC} ${DOMAIN}"
echo -e "${YELLOW}App Directory:${NC} ${APP_DIR}"
echo -e "${YELLOW}App User:${NC} ${APP_USER}"
echo -e "${YELLOW}Database:${NC} ${DB_NAME}"
echo -e "${YELLOW}DB User:${NC} ${DB_USER}"
echo -e "${YELLOW}DB Password:${NC} ${DB_PASSWORD}"
echo -e "${YELLOW}MySQL Root Password:${NC} TempRootPass123!"
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
Sistema: AlmaLinux 9.6
Domain: ${DOMAIN}
App Directory: ${APP_DIR}
App User: ${APP_USER}
Database: ${DB_NAME}
DB User: ${DB_USER}
DB Password: ${DB_PASSWORD}
MySQL Root Password: TempRootPass123!
EOF

echo ""
print_status "Información guardada en: /root/linkiubio-info.txt"
