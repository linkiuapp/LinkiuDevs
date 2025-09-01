<?php
/**
 * Script de diagnóstico para problemas de email con Microsoft 365
 * Ejecutar con: php email-diagnostic.php
 */

require_once 'vendor/autoload.php';

// Cargar configuración de Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;

echo "=== DIAGNÓSTICO DE EMAIL MICROSOFT 365 ===\n\n";

// Configuraciones a probar
$configurations = [
    'Configuración 1: TLS Puerto 587 (Recomendada)' => [
        'host' => 'smtp.office365.com',
        'port' => 587,
        'encryption' => 'tls',
        'stream_options' => [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true,
            ]
        ]
    ],
    'Configuración 2: SSL Puerto 465' => [
        'host' => 'smtp.office365.com',
        'port' => 465,
        'encryption' => 'ssl',
        'stream_options' => [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true,
            ]
        ]
    ],
    'Configuración 3: Sin encriptación (Puerto 587)' => [
        'host' => 'smtp.office365.com',
        'port' => 587,
        'encryption' => null,
        'stream_options' => []
    ],
];

$testEmail = 'no-responder@linkiudev.co'; // Cambiar por el email de prueba

foreach ($configurations as $name => $config) {
    echo "Probando: $name\n";
    echo str_repeat('-', 50) . "\n";
    
    // Aplicar configuración temporalmente
    Config::set('mail.mailers.smtp', array_merge(Config::get('mail.mailers.smtp'), $config));
    
    try {
        // Probar conexión básica
        echo "Host: {$config['host']}\n";
        echo "Puerto: {$config['port']}\n";
        echo "Encriptación: " . ($config['encryption'] ?? 'ninguna') . "\n";
        
        // Intentar enviar email
        Mail::raw('Email de prueba - ' . $name, function ($message) use ($testEmail) {
            $message->to($testEmail)
                    ->subject('Test Email - Diagnóstico')
                    ->from(env('MAIL_USERNAME'), 'Linkiu.bio Test');
        });
        
        echo "✅ ÉXITO: Email enviado correctamente\n";
        echo "Esta configuración funciona!\n\n";
        
        // Si llegamos aquí, esta configuración funciona
        echo "=== CONFIGURACIÓN RECOMENDADA ===\n";
        echo "MAIL_HOST={$config['host']}\n";
        echo "MAIL_PORT={$config['port']}\n";
        echo "MAIL_ENCRYPTION=" . ($config['encryption'] ?? 'null') . "\n";
        if (!empty($config['stream_options']['ssl'])) {
            echo "MAIL_VERIFY_PEER=false\n";
            echo "MAIL_VERIFY_PEER_NAME=false\n";
            echo "MAIL_ALLOW_SELF_SIGNED=true\n";
        }
        break; // Salir del loop si encontramos una configuración que funciona
        
    } catch (Exception $e) {
        echo "❌ ERROR: " . $e->getMessage() . "\n";
        echo "Esta configuración no funciona.\n\n";
    }
}

echo "\n=== VERIFICACIONES ADICIONALES ===\n";

// Verificar variables de entorno
echo "Variables de entorno actuales:\n";
echo "MAIL_HOST: " . env('MAIL_HOST') . "\n";
echo "MAIL_PORT: " . env('MAIL_PORT') . "\n";
echo "MAIL_USERNAME: " . env('MAIL_USERNAME') . "\n";
echo "MAIL_ENCRYPTION: " . env('MAIL_ENCRYPTION') . "\n";
echo "MAIL_FROM_ADDRESS: " . env('MAIL_FROM_ADDRESS') . "\n";

// Verificar conectividad
echo "\nVerificando conectividad a smtp.office365.com...\n";
$connection = @fsockopen('smtp.office365.com', 587, $errno, $errstr, 10);
if ($connection) {
    echo "✅ Conectividad OK al puerto 587\n";
    fclose($connection);
} else {
    echo "❌ No se puede conectar al puerto 587: $errstr ($errno)\n";
}

$connection = @fsockopen('smtp.office365.com', 465, $errno, $errstr, 10);
if ($connection) {
    echo "✅ Conectividad OK al puerto 465\n";
    fclose($connection);
} else {
    echo "❌ No se puede conectar al puerto 465: $errstr ($errno)\n";
}

echo "\n=== RECOMENDACIONES ===\n";
echo "1. Verificar que el App Password de Microsoft 365 sea correcto\n";
echo "2. Verificar que la cuenta tenga permisos SMTP habilitados\n";
echo "3. Verificar que no haya firewall bloqueando los puertos 587/465\n";
echo "4. Probar con MAIL_VERIFY_PEER=false en producción\n";
echo "5. Limpiar cache de configuración: php artisan config:clear && php artisan config:cache\n";