# Configuración Microsoft 365 SMTP para Producción

## Problema Identificado
Error SSL: `certificate verify failed` con Microsoft 365 SMTP

## Configuración Correcta para Microsoft 365

### Variables de entorno (.env producción)
```env
# Email Configuration - Microsoft 365
MAIL_MAILER=smtp
MAIL_HOST=smtp.office365.com
MAIL_PORT=587
MAIL_USERNAME=no-responder@linkiudev.co
MAIL_PASSWORD=tu_app_password_aqui
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=no-responder@linkiudev.co
MAIL_FROM_NAME="Linkiu.bio"

# Configuraciones SSL adicionales
MAIL_VERIFY_PEER=false
MAIL_VERIFY_PEER_NAME=false
MAIL_ALLOW_SELF_SIGNED=true
```

### Configuración Alternativa (si persiste el problema)
```env
# Opción 1: Puerto 25 con STARTTLS
MAIL_HOST=smtp.office365.com
MAIL_PORT=25
MAIL_ENCRYPTION=tls

# Opción 2: Puerto 465 con SSL
MAIL_HOST=smtp.office365.com
MAIL_PORT=465
MAIL_ENCRYPTION=ssl

# Opción 3: Sin encriptación (solo para pruebas)
MAIL_HOST=smtp.office365.com
MAIL_PORT=587
MAIL_ENCRYPTION=null
```

## Pasos para Resolver

1. **Verificar App Password de Microsoft 365**
2. **Actualizar configuración de mail.php**
3. **Probar diferentes configuraciones SSL**
4. **Verificar certificados del servidor**

## Comandos de Verificación
```bash
# Limpiar cache de configuración
php artisan config:clear
php artisan config:cache

# Probar conexión SMTP
telnet smtp.office365.com 587

# Verificar certificados SSL
openssl s_client -connect smtp.office365.com:587 -starttls smtp
```