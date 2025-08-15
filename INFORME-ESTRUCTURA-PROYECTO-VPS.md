# 📁 INFORME ESTRUCTURA PROYECTO - VPS PRODUCCIÓN

## 🏗️ UBICACIÓN ACTUAL DEL PROYECTO

### **Directorio Principal**
```
/home/wwlink/linkiubio_app/
```
**⚠️ IMPORTANTE**: Esta es la ubicación DEFINITIVA del proyecto Laravel en producción.

### **Estructura de Directorios**
```
/home/wwlink/linkiubio_app/
├── app/                           # Código fuente de la aplicación
├── bootstrap/                     # Archivos de arranque
├── config/                        # Configuraciones
├── database/                      # Migraciones y seeders
├── public/                        # NO SE USA DIRECTAMENTE
├── resources/                     # Vistas, CSS, JS
├── routes/                        # Definición de rutas
├── storage/                       # Logs, cache, uploads
│   ├── app/
│   │   └── public/               # Archivos públicos (imágenes, etc.)
│   ├── framework/                # Cache de Laravel
│   └── logs/                     # Logs de la aplicación
├── vendor/                       # Dependencias de Composer
├── .env                          # Configuración de producción
└── artisan                       # CLI de Laravel
```

## 🌐 CONFIGURACIÓN APACHE/WEB

### **DocumentRoot Público**
```
/var/www/html/
```

### **Archivos Web Públicos**
```
/var/www/html/
├── index.php                     # Punto de entrada de Laravel
├── .htaccess                     # Reglas de reescritura
├── storage/                      # ENLACE SIMBÓLICO a storage de Laravel
│   └── -> /home/wwlink/linkiubio_app/storage/app/public
├── assets/                       # CSS/JS compilados
├── build/                        # Archivos de Vite
└── robots.txt                    # SEO
```

## 🔗 ENLACES SIMBÓLICOS CRÍTICOS

### **Storage Link (OBLIGATORIO)**
```bash
# Enlace actual (CORRECTO):
/var/www/html/storage -> /home/wwlink/linkiubio_app/storage/app/public

# Comando para recrear si es necesario:
ln -s /home/wwlink/linkiubio_app/storage/app/public /var/www/html/storage
```

## ⚙️ CONFIGURACIONES IMPORTANTES

### **Archivo .env de Producción**
```
Ubicación: /home/wwlink/linkiubio_app/.env

Configuraciones clave:
- APP_URL=http://162.240.163.188
- DB_DATABASE=linkiubio_prod
- DB_USERNAME=linkiubio_user
- SESSION_DRIVER=file
- FILESYSTEM_DISK=public
```

### **Permisos Críticos**
```bash
# Storage (logs, cache, uploads)
chown -R nobody:nobody /home/wwlink/linkiubio_app/storage/
chmod -R 755 /home/wwlink/linkiubio_app/storage/
chmod -R 777 /home/wwlink/linkiubio_app/storage/logs/
chmod -R 777 /home/wwlink/linkiubio_app/storage/framework/

# Bootstrap cache
chmod -R 777 /home/wwlink/linkiubio_app/bootstrap/cache/

# Archivos web públicos
chown -R nobody:nobody /var/www/html/
chmod -R 755 /var/www/html/
```

## 🚨 REGLAS QUE SE DEBEN RESPETAR

### **❌ NUNCA HACER**
1. **No mover** `/home/wwlink/linkiubio_app/` a otra ubicación
2. **No eliminar** el enlace simbólico `/var/www/html/storage`
3. **No cambiar** permisos de storage a menos que sea necesario
4. **No editar** directamente archivos en `/var/www/html/` (excepto configuraciones)
5. **No usar** `/home/wwlink/linkiubio_app/public/` como DocumentRoot

### **✅ SIEMPRE HACER**
1. **Trabajar** desde `/home/wwlink/linkiubio_app/` para cambios de código
2. **Usar** `php artisan` desde `/home/wwlink/linkiubio_app/`
3. **Verificar** que el enlace storage esté correcto después de cambios
4. **Respetar** la estructura de permisos usuario `nobody:nobody`
5. **Hacer backup** antes de cambios importantes

## 🔄 COMANDOS DE MANTENIMIENTO

### **Limpiar Cache Laravel**
```bash
cd /home/wwlink/linkiubio_app
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```

### **Reinstalar Storage Link**
```bash
# Si se rompe el enlace:
rm /var/www/html/storage
ln -s /home/wwlink/linkiubio_app/storage/app/public /var/www/html/storage
```

### **Verificar Estado del Proyecto**
```bash
# Verificar enlace storage
ls -la /var/www/html/storage

# Verificar permisos storage
ls -la /home/wwlink/linkiubio_app/storage/

# Verificar configuración Apache
httpd -S | grep "162.240.163.188"

# Probar funcionalidad
curl -I "http://162.240.163.188/superlinkiu/login"
```

## 📊 URLS IMPORTANTES

### **Aplicación Principal**
- **Frontend**: `http://162.240.163.188/`
- **SuperLinkiu Login**: `http://162.240.163.188/superlinkiu/login`
- **Storage Público**: `http://162.240.163.188/storage/`

### **Archivos de Log**
- **Laravel**: `/home/wwlink/linkiubio_app/storage/logs/laravel.log`
- **Apache**: `/var/log/apache2/error_log`

---

## ⚠️ NOTA IMPORTANTE
**Esta estructura NO debe modificarse sin coordinación previa. Cualquier cambio puede romper la funcionalidad de la aplicación en producción.**

