# 📋 INFORME DE DEPLOYMENT VPS - LINKIU.BIO
**Fecha:** 12 de Agosto 2025
**Proyecto:** LinkiuBio - Sistema Multi-tenant Laravel

## ✅ RESUMEN EJECUTIVO
Deployment exitoso del proyecto LinkiuBio en VPS HostGator (AlmaLinux 9.6). Sistema funcionando correctamente con autenticación, base de datos y assets compilados.

## 🖥️ INFORMACIÓN DEL SERVIDOR
- **IP:** 162.240.163.188
- **Puerto SSH:** 22022
- **Sistema Operativo:** AlmaLinux 9.6
- **Dominio:** linkiu.bio (DNS configurado en Hostinger)
- **Panel:** cPanel/WHM instalado

## 📦 STACK TECNOLÓGICO INSTALADO
- **PHP:** 8.2
- **MySQL:** 8.0
- **Apache:** 2.4.58 (httpd)
- **Node.js:** 18.20.8
- **NPM:** 10.8.2
- **Composer:** /opt/cpanel/composer/bin/composer
- **Laravel:** 12.x

## 🗄️ BASE DE DATOS
- **Nombre BD:** linkiubio_prod
- **Usuario BD:** linkiubio_user
- **Password BD:** LinkiuBio2024!
- **Tablas:** 49 tablas migradas correctamente

## 👤 USUARIOS DEL SISTEMA

### Usuario Super Admin (para login)
- **Email:** test@linkiu.bio
- **Password:** 123456
- **Role:** super_admin
- **URL Login:** https://linkiu.bio/superlinkiu/login

### Usuario Sistema Linux
- **Usuario:** linkiubio
- **Home:** /home/linkiubio
- **Proyecto en:** /var/www/linkiubio

## 🔧 CONFIGURACIONES IMPORTANTES

### Archivo .env Principal
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://linkiu.bio
SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=
SESSION_COOKIE=linkiubio_app_session
FILESYSTEM_DISK=public
```

### Permisos de Directorios
```bash
# Propietario: nobody:nobody (usuario de Apache)
chmod -R 755 /var/www/linkiubio
chmod -R 775 /var/www/linkiubio/storage
chmod -R 775 /var/www/linkiubio/bootstrap/cache
```

## 🚀 COMANDOS ÚTILES PARA MANTENIMIENTO

### Limpiar Caché
```bash
cd /var/www/linkiubio
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### Reconstruir Caché (Producción)
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Compilar Assets Frontend
```bash
cd /var/www/linkiubio
npm install
npm run build
```

### Reiniciar Servicios
```bash
systemctl restart httpd
systemctl restart mysqld
```

### Ver Logs
```bash
# Logs de Laravel
tail -f /var/www/linkiubio/storage/logs/laravel.log

# Logs de Apache
tail -f /var/log/httpd/linkiubio_error.log
tail -f /var/log/httpd/linkiubio_access.log
```

## 🐛 PROBLEMAS RESUELTOS

### 1. Error 419 Page Expired
**Causa:** Conflicto de cookies con cPanel/WHM y configuración incorrecta de sesiones.
**Solución:** 
- Cambiar SESSION_DRIVER de 'database' a 'file'
- Configurar SESSION_COOKIE único: 'linkiubio_app_session'
- Ajustar APP_URL para coincidir con la IP de acceso

### 2. Assets sin Estilos
**Causa:** Assets no compilados.
**Solución:** npm install && npm run build

### 3. Middleware Bloqueando Acceso
**Causa:** SuperAdminMiddleware verificando autenticación que no persistía.
**Solución:** Configuración correcta de sesiones y cookies.

## 📂 ESTRUCTURA DE ARCHIVOS
```
/var/www/linkiubio/
├── app/
│   ├── Features/
│   │   ├── SuperLinkiu/    # Panel super admin
│   │   ├── TenantAdmin/    # Panel admin tienda
│   │   └── Tenant/          # Frontend tienda
│   └── Shared/              # Componentes compartidos
├── public/
│   └── build/               # Assets compilados
├── storage/                 # Storage local (no S3)
├── .env                     # Configuración
└── ...
```

## 🔒 SEGURIDAD
- APP_DEBUG=false en producción
- Todos los archivos de debug eliminados
- Permisos correctos en directorios
- CSRF activo y funcionando
- Middleware de autenticación activo

## 📝 RUTAS PRINCIPALES
- **Web Principal:** http://162.240.163.188
- **SuperAdmin:** http://162.240.163.188/superlinkiu
- **Tienda:** http://162.240.163.188/{slug-tienda}
- **Admin Tienda:** http://162.240.163.188/{slug-tienda}/admin

## 🔄 PRÓXIMOS PASOS RECOMENDADOS
1. **Configurar SSL/HTTPS** con Let's Encrypt
2. **Configurar dominio linkiu.bio** en lugar de IP
3. **Configurar backups automáticos** de BD y archivos
4. **Configurar monitoreo** del servidor
5. **Optimizar configuración de PHP** para producción
6. **Configurar email corporativo** en .env

## 📊 ESTADO ACTUAL
✅ Sistema funcionando correctamente
✅ Login SuperAdmin operativo
✅ Base de datos conectada
✅ Assets compilados y cargando
✅ Seguridad restaurada
✅ Sin archivos de debug

## 🆘 COMANDOS DE EMERGENCIA

### Si el login deja de funcionar:
```bash
# Verificar sesiones
ls -la /var/www/linkiubio/storage/framework/sessions/

# Limpiar sesiones
rm -f /var/www/linkiubio/storage/framework/sessions/*

# Reiniciar
php artisan config:clear
systemctl restart httpd
```

### Para crear nuevo super admin:
```bash
cd /var/www/linkiubio
php artisan tinker
```
```php
use App\Shared\Models\User;
User::create([
    'name' => 'Nuevo Admin',
    'email' => 'nuevo@email.com',
    'password' => Hash::make('password'),
    'role' => 'super_admin',
    'email_verified_at' => now()
]);
exit
```

## 📌 NOTAS IMPORTANTES
- El proyecto usa almacenamiento LOCAL, no AWS S3
- La autenticación multi-tenant es personalizada, no usa paquetes
- Los ServiceProviders de cada feature se cargan automáticamente
- El sistema de diseño usa colores personalizados definidos en tailwind.config.js

---
**Documento preparado para continuidad del proyecto**
