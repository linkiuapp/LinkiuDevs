cd /home/wwlink/linkiubio_app


# Cambiar propietario a nobody (el usuario correcto del servidor web)
chown -R nobody:nobody storage/
chown -R nobody:nobody bootstrap/cache/

# Dar permisos de escritura
chmod -R 777 storage/
chmod -R 775 bootstrap/cache/

# Limpiar cachés de Laravel
php artisan view:clear
php artisan config:clear
php artisan cache:clear
php artisan route:clear

# Recrear cachés
php artisan config:cache
php artisan route:cache
php artisan view:cache

#jSAiaU9wD^gofu&7q
stsvhfzcgggrxtjd


# 🚀 Guía Completa de Deployment Automatizado
## Local → GitHub → VPS

### 📋 Resumen del Sistema
- **Desarrollo Local**: Cursor/VS Code
- **Repositorio**: GitHub (linkiuapp/LinkiuDevs)
- **Servidor**: VPS con cPanel/Apache
- **Framework**: Laravel 11
- **Deployment**: GitHub Actions (automatizado)

---

## 🔧 Configuración Inicial

### 1. Configuración del VPS

#### Accesos del Servidor
```bash
# Conexión SSH
ssh root@vps-1740977.linkiu.bio

# Datos del servidor
IP: 162.240.163.188
Dominio: linkiu.bio
Panel: cPanel
Servidor Web: Apache con cPanel EasyApache
```

#### Estructura de Directorios
```bash
/home/wwlink/
├── public_html -> /home/wwlink/linkiubio_app/public (symlink)
├── linkiubio_app/ (aplicación Laravel)
└── public_html_backup (backup del directorio original)
```

### 2. Configuración de GitHub Actions

#### Archivo: `.github/workflows/deploy.yml`
```yaml
name: Deploy to VPS

on:
  push:
    branches: [ main ]
  pull_request:
    branches: [ main ]

jobs:
  deploy:
    runs-on: ubuntu-latest
    
    steps:
    - uses: actions/checkout@v4
    
    - name: Deploy to server
      uses: appleboy/ssh-action@v1.0.3
      with:
        host: ${{ secrets.HOST }}
        username: ${{ secrets.USERNAME }}
        key: ${{ secrets.PRIVATE_KEY }}
        script: |
          cd /home/wwlink/linkiubio_app
          git pull origin main
          composer install --no-dev --optimize-autoloader
          php artisan config:cache
          php artisan route:cache
          php artisan view:cache
          chown -R nobody:nobody storage/
          chmod -R 777 storage/
```

#### Secrets de GitHub (Settings → Secrets and variables → Actions)
```
HOST: vps-1740977.linkiu.bio
USERNAME: root
PRIVATE_KEY: [Clave SSH privada completa]
```

### 3. Configuración SSH

#### Generar claves SSH (en local)
```bash
ssh-keygen -t rsa -b 4096 -C "deployment@linkiu.bio"
# Guardar como: ~/.ssh/linkiu_deploy
```

#### Instalar clave pública en VPS
```bash
# En el VPS
mkdir -p ~/.ssh
echo "tu_clave_publica_aqui" >> ~/.ssh/authorized_keys
chmod 700 ~/.ssh
chmod 600 ~/.ssh/authorized_keys
```

---

## 🔄 Proceso de Deployment

### Flujo Automatizado
1. **Push a main** → Trigger GitHub Actions
2. **GitHub Actions** → Conecta al VPS via SSH
3. **VPS** → Ejecuta comandos de deployment
4. **Apache** → Sirve la aplicación actualizada

### Comandos Ejecutados Automáticamente
```bash
cd /home/wwlink/linkiubio_app
git pull origin main                    # Actualizar código
composer install --no-dev              # Instalar dependencias
php artisan config:cache               # Cache de configuración
php artisan route:cache                # Cache de rutas
php artisan view:cache                 # Cache de vistas
chown -R apache:apache storage/        # Permisos de storage
chmod -R 777 storage/                  # Permisos de escritura
```

---

## 🛠️ Configuración del Servidor Web

### Apache Virtual Host (cPanel)
```apache
# /etc/apache2/conf.d/userdata/std/2_4/wwlink/linkiu.bio/00-priority.conf
<Directory "/home/wwlink/public_html">
    AllowOverride All
    Require all granted
    Options -Indexes +FollowSymLinks
    DirectoryIndex index.php index.html
</Directory>

<FilesMatch \.php$>
    SetHandler application/x-httpd-ea-php82
</FilesMatch>
```

### Symlink Configuration
```bash
# Crear symlink para redireccionar public_html a Laravel public
cd /home/wwlink/
mv public_html public_html_backup
ln -s /home/wwlink/linkiubio_app/public public_html
```

---

## 📁 Estructura del Proyecto

### Repositorio GitHub
```
LinkiuDevs/
├── .github/workflows/deploy.yml
├── app/
├── public/
├── storage/
├── .env.example
└── composer.json
```

### VPS Structure
```
/home/wwlink/linkiubio_app/
├── app/
├── public/ (servido por Apache via symlink)
├── storage/ (permisos 777 para Apache)
├── .env (configuración de producción)
└── vendor/
```

---

## 🔐 Configuración de Seguridad

### Archivo .env (VPS)
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://linkiu.bio

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=linkiubio_prod
DB_USERNAME=linkiubio_user
DB_PASSWORD=LinkiuBio2024!

LOG_CHANNEL=stack
LOG_LEVEL=error
```

### Permisos Críticos
```bash
# Aplicación
chown -R wwlink:wwlink /home/wwlink/linkiubio_app/
chmod -R 755 /home/wwlink/linkiubio_app/

# Storage (Apache ejecuta como nobody, necesita escribir)
chown -R nobody:nobody /home/wwlink/linkiubio_app/storage/
chmod -R 777 /home/wwlink/linkiubio_app/storage/

# Bootstrap cache
chmod -R 775 /home/wwlink/linkiubio_app/bootstrap/cache/
```

---

## 🚨 Solución de Problemas Comunes

### Error 500 - Permisos
```bash
# Verificar logs
tail -20 /home/wwlink/linkiubio_app/storage/logs/laravel.log
tail -20 /etc/apache2/logs/error_log

# Arreglar permisos
chown -R nobody:nobody /home/wwlink/linkiubio_app/storage/
chmod -R 777 /home/wwlink/linkiubio_app/storage/
```

### Error de Deployment
```bash
# Verificar conexión SSH
ssh -i ~/.ssh/linkiu_deploy root@vps-1740977.linkiu.bio

# Verificar git en VPS
cd /home/wwlink/linkiubio_app
git status
git pull origin main
```

### Reconstruir Apache (cPanel)
```bash
/scripts/rebuildhttpdconf
systemctl reload httpd
```

---

## 📊 Monitoreo y Logs

### Logs Importantes
```bash
# Laravel
tail -f /home/wwlink/linkiubio_app/storage/logs/laravel.log

# Apache
tail -f /etc/apache2/logs/error_log
tail -f /etc/apache2/logs/domlogs/linkiu.bio

# GitHub Actions
# Ver en: https://github.com/linkiuapp/LinkiuDevs/actions
```

### Verificación de Deployment
```bash
# Verificar sitio
curl -I https://linkiu.bio

# Verificar última actualización
cd /home/wwlink/linkiubio_app
git log --oneline -5
```

---

## 🎯 Comandos de Mantenimiento

### Deployment Manual (si es necesario)
```bash
cd /home/wwlink/linkiubio_app
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
chown -R nobody:nobody storage/
chmod -R 777 storage/
```

### Backup antes de cambios importantes
```bash
cd /home/wwlink/
tar -czf backup_$(date +%Y%m%d_%H%M%S).tar.gz linkiubio_app/
```

---

## ✅ Checklist de Deployment

- [ ] Código pusheado a `main`
- [ ] GitHub Actions ejecutado exitosamente
- [ ] Sitio accesible en https://linkiu.bio
- [ ] Logs sin errores críticos
- [ ] Funcionalidades principales funcionando
- [ ] Base de datos actualizada (migraciones)

---

**🎉 ¡Deployment automatizado configurado y funcionando!**

*Última actualización: 30 de Agosto, 2025*