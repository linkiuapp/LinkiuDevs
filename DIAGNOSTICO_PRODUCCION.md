# 🚀 Guía de Diagnóstico para Problemas en Producción

## Problema: Funciona en localhost pero falla en producción

Si tu aplicación funciona perfectamente en localhost pero da error 500 en producción, sigue esta guía paso a paso.

## 🔧 Herramientas de Diagnóstico Disponibles

### 1. Comando General de Diagnóstico

```bash
php artisan diagnose:production
```

**Qué hace:**
- ✅ Verifica configuración de entorno (APP_ENV, APP_DEBUG, etc.)
- ✅ Prueba conexión a base de datos y estructura de tablas
- ✅ Verifica permisos de archivos (storage/, cache/, etc.)
- ✅ Revisa estado de migraciones
- ✅ Verifica autoload de clases críticas
- ✅ Analiza logs de errores recientes

### 2. Comando con Limpieza de Cache

```bash
php artisan diagnose:production --clear-cache
```

**Importante:** En producción, los caches pueden causar problemas. Este comando limpia todos los caches antes del diagnóstico.

### 3. Diagnóstico de Tienda Específica

```bash
php artisan diagnose:production --store_slug=nombre-tienda
```

Prueba específicamente una tienda que esté dando problemas.

### 4. Debug de Login Específico

```bash
php artisan debug:tenant-login nombre-tienda --email=admin@ejemplo.com
```

Diagnostica problemas específicos de login en una tienda.

## 🌐 Debug Desde el Navegador

Puedes usar la ruta de debug directamente en el navegador:

```
https://tu-dominio.com/nombre-tienda/admin/debug
```

**Ejemplo:**
```
https://linkiu.bio/barranquilla/admin/debug
https://linkiu.bio/gaby-y-belen/admin/debug
```

Esta ruta te mostrará:
- Estado de la base de datos
- Información de la tienda
- Estado del middleware
- Permisos de archivos
- Información del usuario autenticado

## 🔍 Problemas Más Comunes en Producción

### 1. Migraciones Pendientes
**Error:** Tablas o columnas no existen
**Solución:**
```bash
php artisan migrate
php artisan migrate:status
```

### 2. Permisos de Archivos
**Error:** No puede escribir en storage/
**Solución:**
```bash
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/
chown -R www-data:www-data storage/
```

### 3. Cache Desactualizado
**Error:** Configuración antigua en cache
**Solución:**
```bash
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
```

### 4. Symlink de Storage
**Error:** Imágenes no se cargan
**Solución:**
```bash
php artisan storage:link
```

### 5. Variables de Entorno
**Error:** Configuración de BD incorrecta
**Verificar:** Archivo `.env` tiene las variables correctas para producción

### 6. Autoload Desactualizado
**Error:** Clases no encontradas
**Solución:**
```bash
composer install --optimize-autoloader --no-dev
composer dump-autoload
```

## 📝 Interpretando los Logs

### Ubicación de Logs
```bash
tail -f storage/logs/laravel.log
```

### Errores Comunes:
- `Class not found` → Problema de autoload
- `Table doesn't exist` → Migraciones pendientes
- `Permission denied` → Permisos de archivos
- `Connection refused` → Problema de BD

## ⚡ Solución Rápida (Checklist)

Ejecuta estos comandos en orden en tu servidor de producción:

```bash
# 1. Limpiar caches
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# 2. Actualizar autoload
composer dump-autoload --optimize

# 3. Ejecutar migraciones
php artisan migrate --force

# 4. Crear symlink de storage
php artisan storage:link

# 5. Diagnosticar
php artisan diagnose:production

# 6. Si hay una tienda específica con problemas
php artisan diagnose:production --store_slug=nombre-tienda
```

## 🎯 Para Laravel Cloud

Si estás usando Laravel Cloud, estos comandos adicionales pueden ayudar:

```bash
# Verificar estado del deploy
php artisan about

# Verificar configuración específica de Laravel Cloud
php artisan env

# Ejecutar el diagnóstico completo
php artisan diagnose:production --clear-cache
```

## 🆘 Si Nada Funciona

1. **Verifica los logs en tiempo real:**
   ```bash
   tail -f storage/logs/laravel.log
   ```

2. **Habilita debug temporal** (solo para diagnóstico):
   ```env
   APP_DEBUG=true
   ```
   ⚠️ **IMPORTANTE:** Vuelve a ponerlo en `false` después del diagnóstico.

3. **Prueba la ruta de debug:**
   ```
   https://tu-dominio.com/tienda-problema/admin/debug
   ```

4. **Contacta con los detalles del diagnóstico:**
   - Output de `php artisan diagnose:production`
   - Últimas líneas del log
   - URL específica que falla
   - Información de la ruta de debug

## 📞 Información para Soporte

Si necesitas ayuda, incluye:

```bash
# Ejecutar y enviar output
php artisan diagnose:production --store_slug=tienda-problema
php artisan about
tail -20 storage/logs/laravel.log
```

¡Con estas herramientas deberías poder identificar y resolver la mayoría de problemas de producción! 🚀 