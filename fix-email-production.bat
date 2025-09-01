@echo off
echo === SOLUCIONANDO PROBLEMA DE EMAIL EN PRODUCCION ===
echo.

REM Navegar al directorio de la aplicacion
cd /d "C:\path\to\your\app"

echo 1. Limpiando cache de configuracion...
php artisan config:clear
php artisan cache:clear

echo 2. Verificando configuracion actual...
findstr "MAIL_HOST" .env
findstr "MAIL_PORT" .env
findstr "MAIL_ENCRYPTION" .env
findstr "MAIL_USERNAME" .env

echo.
echo 3. Creando backup del .env...
copy .env .env.backup.%date:~-4,4%%date:~-10,2%%date:~-7,2%_%time:~0,2%%time:~3,2%%time:~6,2%

echo 4. Aplicando configuracion SSL permisiva para Microsoft 365...

REM Agregar configuraciones SSL al final del archivo
echo MAIL_VERIFY_PEER=false >> .env
echo MAIL_VERIFY_PEER_NAME=false >> .env
echo MAIL_ALLOW_SELF_SIGNED=true >> .env

echo 5. Recreando cache de configuracion...
php artisan config:cache

echo 6. Verificando nueva configuracion...
findstr "MAIL_VERIFY_PEER" .env
findstr "MAIL_VERIFY_PEER_NAME" .env
findstr "MAIL_ALLOW_SELF_SIGNED" .env

echo.
echo === CONFIGURACION COMPLETADA ===
echo Si el problema persiste, prueba estas alternativas:
echo - Cambiar MAIL_PORT=465 y MAIL_ENCRYPTION=ssl
echo - Cambiar MAIL_ENCRYPTION=null (sin encriptacion)
echo - Verificar que el App Password de Microsoft 365 sea correcto
pause