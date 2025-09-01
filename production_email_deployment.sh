#!/bin/bash

echo "=== DEPLOYMENT DEL SISTEMA DE EMAILS EN PRODUCCIÓN ==="
echo "Fecha: $(date)"
echo ""

# Navegar al directorio de la aplicación
cd /home/wwlink/linkiubio_app

echo "1. Verificando que GitHub Actions haya actualizado el código..."
git log --oneline -2
echo ""

echo "2. Backup de la base de datos antes de cambios..."
# Crear backup de seguridad
mysqldump -u linkiubio_user -p'LinkiuBio2024!' linkiubio_prod > /home/wwlink/backup_emails_$(date +%Y%m%d_%H%M%S).sql
echo "✅ Backup creado"
echo ""

echo "3. Actualizando .env con configuración SMTP correcta..."
# Backup del .env actual
cp .env .env.backup.$(date +%Y%m%d_%H%M%S)

# Actualizar configuración SMTP en .env
sed -i 's/MAIL_PORT=587/MAIL_PORT=465/' .env
sed -i 's/MAIL_ENCRYPTION=tls/MAIL_ENCRYPTION=ssl/' .env

echo "✅ .env actualizado:"
echo "MAIL_PORT=465"
echo "MAIL_ENCRYPTION=ssl"
echo ""

echo "4. Eliminando tablas del sistema anterior..."
# Conectar a MySQL y eliminar tablas antiguas
mysql -u linkiubio_user -p'LinkiuBio2024!' linkiubio_prod << EOF
DROP TABLE IF EXISTS email_configurations;
DROP TABLE IF EXISTS email_settings;
-- Nota: email_templates puede existir, la migración manejará esto
SHOW TABLES LIKE '%email%';
EOF

echo "✅ Tablas antiguas eliminadas"
echo ""

echo "5. Ejecutando nueva migración..."
php artisan migrate --force
echo "✅ Migración ejecutada"
echo ""

echo "6. Creando plantillas por defecto..."
php artisan db:seed --class=EmailTemplateSeederNew --force
echo "✅ Plantillas creadas"
echo ""

echo "7. Limpiando caches..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

echo "8. Recreando caches optimizados..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
echo "✅ Caches optimizados"
echo ""

echo "9. Ajustando permisos..."
chown -R nobody:nobody storage/
chmod -R 777 storage/
chown -R nobody:nobody bootstrap/cache/
chmod -R 775 bootstrap/cache/
echo "✅ Permisos ajustados"
echo ""

echo "10. Verificando configuración de emails..."
php artisan tinker --execute="
echo 'Configuración SMTP:';
echo 'Host: ' . env('MAIL_HOST');
echo 'Port: ' . env('MAIL_PORT');
echo 'Encryption: ' . env('MAIL_ENCRYPTION');
echo 'Username: ' . env('MAIL_USERNAME');
echo '';
echo 'Plantillas creadas: ' . App\Models\EmailTemplate::count();
echo 'Plantillas activas: ' . App\Models\EmailTemplate::where('is_active', true)->count();
"
echo ""

echo "=== DEPLOYMENT COMPLETADO ==="
echo ""
echo "📧 Sistema de emails deployado exitosamente"
echo "🌐 Acceso: https://linkiu.bio/superlinkiu/email/"
echo "🔧 Dashboard: https://linkiu.bio/superlinkiu/email/"
echo "📋 Plantillas: https://linkiu.bio/superlinkiu/email/templates"
echo ""
echo "⚠️  IMPORTANTE: Probar el botón 'Probar SMTP' en el dashboard"
echo ""

# Mostrar logs recientes por si hay errores
echo "Logs recientes (últimas 10 líneas):"
tail -10 storage/logs/laravel.log 2>/dev/null || echo "No hay logs de Laravel"
