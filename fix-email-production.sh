#!/bin/bash

echo "=== SOLUCIONANDO PROBLEMA DE EMAIL EN PRODUCCIÓN ==="
echo ""

# Navegar al directorio de la aplicación
cd /home/wwlink/linkiubio_app

echo "1. Limpiando cache de configuración..."
php artisan config:clear
php artisan cache:clear

echo "2. Verificando configuración actual..."
echo "MAIL_HOST: $(grep MAIL_HOST .env)"
echo "MAIL_PORT: $(grep MAIL_PORT .env)"
echo "MAIL_ENCRYPTION: $(grep MAIL_ENCRYPTION .env)"
echo "MAIL_USERNAME: $(grep MAIL_USERNAME .env)"

echo ""
echo "3. Aplicando configuración SSL permisiva para Microsoft 365..."

# Backup del .env actual
cp .env .env.backup.$(date +%Y%m%d_%H%M%S)

# Agregar o actualizar configuraciones SSL
if grep -q "MAIL_VERIFY_PEER" .env; then
    sed -i 's/MAIL_VERIFY_PEER=.*/MAIL_VERIFY_PEER=false/' .env
else
    echo "MAIL_VERIFY_PEER=false" >> .env
fi

if grep -q "MAIL_VERIFY_PEER_NAME" .env; then
    sed -i 's/MAIL_VERIFY_PEER_NAME=.*/MAIL_VERIFY_PEER_NAME=false/' .env
else
    echo "MAIL_VERIFY_PEER_NAME=false" >> .env
fi

if grep -q "MAIL_ALLOW_SELF_SIGNED" .env; then
    sed -i 's/MAIL_ALLOW_SELF_SIGNED=.*/MAIL_ALLOW_SELF_SIGNED=true/' .env
else
    echo "MAIL_ALLOW_SELF_SIGNED=true" >> .env
fi

# Asegurar configuración correcta de Microsoft 365
sed -i 's/MAIL_HOST=.*/MAIL_HOST=smtp.office365.com/' .env
sed -i 's/MAIL_PORT=.*/MAIL_PORT=587/' .env
sed -i 's/MAIL_ENCRYPTION=.*/MAIL_ENCRYPTION=tls/' .env

echo "4. Recreando cache de configuración..."
php artisan config:cache

echo "5. Verificando nueva configuración..."
echo "MAIL_VERIFY_PEER: $(grep MAIL_VERIFY_PEER .env)"
echo "MAIL_VERIFY_PEER_NAME: $(grep MAIL_VERIFY_PEER_NAME .env)"
echo "MAIL_ALLOW_SELF_SIGNED: $(grep MAIL_ALLOW_SELF_SIGNED .env)"

echo ""
echo "6. Probando envío de email..."
echo "Ejecuta: php artisan tinker"
echo "Luego: App\\Services\\EmailService::sendTestEmail('tu-email@ejemplo.com')"

echo ""
echo "=== CONFIGURACIÓN COMPLETADA ==="
echo "Si el problema persiste, prueba estas alternativas:"
echo "- Cambiar MAIL_PORT=465 y MAIL_ENCRYPTION=ssl"
echo "- Cambiar MAIL_ENCRYPTION=null (sin encriptación)"
echo "- Verificar que el App Password de Microsoft 365 sea correcto"