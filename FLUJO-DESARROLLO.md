# 📋 FLUJO DE DESARROLLO: LOCALHOST → VPS

## 🏠 1. CONFIGURACIÓN LOCALHOST (Windows)

### Requisitos Previos
- XAMPP o Laragon con PHP 8.2+
- Node.js 18+ y NPM
- Composer
- Git

### Configuración Inicial
```bash
# 1. Clonar o trabajar en tu directorio actual
cd E:\PROYECTOS\Drive\Linkiu.bio-App\LinkiuBioNew\linkiubio

# 2. Instalar dependencias
composer install
npm install

# 3. Configurar .env para localhost
cp .env.example .env.local
```

### Archivo .env.local (para desarrollo)
```env
APP_NAME=LinkiuBio
APP_ENV=local
APP_DEBUG=true
APP_KEY=base64:tu-key-aqui
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=linkiubio_local
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_COOKIE=linkiubio_local_session

FILESYSTEM_DISK=public

# Desactivar servicios externos en local
MAIL_MAILER=log
PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
```

### Comandos para Desarrollo Local
```bash
# Generar key
php artisan key:generate

# Migrar base de datos
php artisan migrate:fresh --seed

# Crear usuario de prueba
php artisan tinker
>>> User::create(['name'=>'Admin Local','email'=>'admin@local.test','password'=>Hash::make('password'),'role'=>'super_admin']);

# Iniciar servidor de desarrollo
php artisan serve

# En otra terminal, compilar assets en modo watch
npm run dev
```

## 🔄 2. FLUJO DE DESARROLLO

### A. Desarrollo de Nueva Funcionalidad
```bash
# 1. Crear rama para la feature
git checkout -b feature/nombre-funcionalidad

# 2. Desarrollar y probar en localhost
# - Hacer cambios
# - Probar en http://localhost:8000

# 3. Compilar assets si hay cambios en frontend
npm run build

# 4. Commit de cambios
git add .
git commit -m "feat: descripción de la funcionalidad"

# 5. Push a repositorio
git push origin feature/nombre-funcionalidad
```

### B. Testing Local
```bash
# Ejecutar tests si existen
php artisan test

# Verificar que no hay errores de sintaxis
php artisan tinker
>>> exit

# Limpiar caché para asegurar funcionamiento
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

## 📦 3. PREPARACIÓN PARA DEPLOY

### Checklist Pre-Deploy
- [ ] Código funcionando en localhost
- [ ] Assets compilados (`npm run build`)
- [ ] Migraciones creadas si hay cambios en BD
- [ ] Sin archivos de debug o temporales
- [ ] .env.production configurado correctamente

### Script de Preparación (prepare-deploy.sh)
```bash
#!/bin/bash
echo "🚀 Preparando para deploy..."

# Compilar assets
npm run build

# Limpiar caché local
php artisan config:clear
php artisan cache:clear

# Verificar sintaxis PHP
find app -name "*.php" -exec php -l {} \; | grep -v "No syntax errors"

echo "✅ Listo para deploy"
```

## 🚀 4. DEPLOY AL VPS

### Opción A: Deploy Manual (Recomendado para cambios grandes)
```bash
# 1. En tu local, comprimir proyecto
tar --exclude=vendor --exclude=node_modules --exclude=.git \
    --exclude=storage/app/public/* --exclude=storage/logs/* \
    -czf deploy_$(date +%Y%m%d_%H%M%S).tar.gz .

# 2. Subir al VPS
scp -P 22022 deploy_*.tar.gz root@162.240.163.188:/tmp/

# 3. En el VPS, hacer backup
ssh -p 22022 root@162.240.163.188
cd /var/www
tar -czf linkiubio_backup_$(date +%Y%m%d).tar.gz linkiubio/

# 4. Descomprimir nueva versión
cd /var/www/linkiubio
tar -xzf /tmp/deploy_*.tar.gz

# 5. Actualizar dependencias y caché
composer install --no-dev --optimize-autoloader
npm install --production
npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 6. Ajustar permisos
chown -R nobody:nobody /var/www/linkiubio
chmod -R 755 /var/www/linkiubio
chmod -R 775 /var/www/linkiubio/storage
chmod -R 775 /var/www/linkiubio/bootstrap/cache

# 7. Reiniciar servicios
systemctl restart httpd
```

### Opción B: Deploy con Git (Recomendado para cambios pequeños)
```bash
# 1. En el VPS, configurar repositorio
ssh -p 22022 root@162.240.163.188
cd /var/www/linkiubio
git init
git remote add origin tu-repositorio-git

# 2. Para cada deploy
git pull origin main

# 3. Actualizar dependencias
composer install --no-dev --optimize-autoloader
npm install --production
npm run build

# 4. Ejecutar migraciones si hay
php artisan migrate --force

# 5. Limpiar y reconstruir caché
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 6. Ajustar permisos
chown -R nobody:nobody /var/www/linkiubio
chmod -R 775 storage bootstrap/cache
```

## 🔄 5. SCRIPT AUTOMATIZADO DE DEPLOY

### deploy-to-vps.ps1 (PowerShell para Windows)
```powershell
# Script de deploy automatizado desde Windows
param(
    [string]$mensaje = "Update"
)

Write-Host "🚀 Iniciando deploy a VPS..." -ForegroundColor Green

# 1. Compilar assets
Write-Host "📦 Compilando assets..." -ForegroundColor Yellow
npm run build

# 2. Comprimir proyecto
Write-Host "🗜️ Comprimiendo proyecto..." -ForegroundColor Yellow
$fecha = Get-Date -Format "yyyyMMdd_HHmmss"
$archivo = "deploy_$fecha.zip"
Compress-Archive -Path * -DestinationPath $archivo -Force

# 3. Subir al VPS
Write-Host "📤 Subiendo al VPS..." -ForegroundColor Yellow
scp -P 22022 $archivo root@162.240.163.188:/tmp/

# 4. Ejecutar comandos en VPS
Write-Host "🔧 Ejecutando deploy en VPS..." -ForegroundColor Yellow
$comandos = @"
cd /var/www/linkiubio
unzip -o /tmp/$archivo
composer install --no-dev --optimize-autoloader
npm install --production
npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
chown -R nobody:nobody /var/www/linkiubio
chmod -R 775 storage bootstrap/cache
systemctl restart httpd
rm /tmp/$archivo
echo '✅ Deploy completado!'
"@

ssh -p 22022 root@162.240.163.188 $comandos

# 5. Limpiar archivo local
Remove-Item $archivo

Write-Host "✅ Deploy completado exitosamente!" -ForegroundColor Green
```

## 🧪 6. TESTING POST-DEPLOY

### Verificaciones Básicas
```bash
# 1. Verificar que el sitio carga
curl -I http://162.240.163.188

# 2. Verificar login SuperAdmin
curl -I http://162.240.163.188/superlinkiu/login

# 3. Revisar logs de errores
ssh -p 22022 root@162.240.163.188
tail -20 /var/www/linkiubio/storage/logs/laravel.log

# 4. Verificar servicios
systemctl status httpd
systemctl status mysqld
```

## 🔙 7. ROLLBACK (En caso de problemas)

### Procedimiento de Rollback
```bash
# 1. Conectar al VPS
ssh -p 22022 root@162.240.163.188

# 2. Restaurar backup
cd /var/www
mv linkiubio linkiubio_failed
tar -xzf linkiubio_backup_[fecha].tar.gz

# 3. Restaurar base de datos si es necesario
mysql -u root linkiubio_prod < /root/backups_linkiu/db_backup_[fecha].sql

# 4. Reiniciar servicios
systemctl restart httpd
```

## 📝 8. MEJORES PRÁCTICAS

### DO's ✅
- Siempre hacer backup antes de deploy
- Probar TODO en localhost primero
- Compilar assets antes de subir
- Usar versionado semántico en commits
- Documentar cambios importantes

### DON'Ts ❌
- NO hacer cambios directamente en producción
- NO subir node_modules o vendor
- NO dejar APP_DEBUG=true en producción
- NO subir archivos .env al repositorio
- NO hacer deploy sin probar primero

## 🛠️ 9. COMANDOS ÚTILES

### Desarrollo Local
```bash
php artisan serve              # Servidor local
npm run dev                     # Compilar y watch
php artisan migrate:fresh       # Recrear BD
php artisan tinker             # Consola interactiva
```

### Producción (VPS)
```bash
php artisan down               # Modo mantenimiento
php artisan up                 # Salir de mantenimiento
php artisan queue:restart      # Reiniciar colas
php artisan cache:clear        # Limpiar todo caché
tail -f storage/logs/laravel.log  # Ver logs en tiempo real
```

## 📊 10. MONITOREO

### Verificar Estado del Sistema
```bash
# Espacio en disco
df -h

# Memoria
free -m

# Procesos
top

# Conexiones activas
netstat -tulpn

# Logs de Apache
tail -f /var/log/httpd/linkiubio_access.log
```

---

**📌 NOTA:** Guarda este documento para referencia. Actualízalo según evolucione tu flujo de trabajo.
