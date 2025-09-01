<?php

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== ACTUALIZAR CONFIGURACIÓN DE EMAILS ===\n\n";

// Actualizar los emails en la base de datos para que coincidan con el sistema centralizado
$updates = [
    'store_management' => 'no-responder@linkiu.email',
    'support' => 'soporte@linkiu.email', 
    'billing' => 'contabilidad@linkiu.email'
];

foreach ($updates as $context => $email) {
    echo "Actualizando contexto '{$context}' a '{$email}'...\n";
    
    try {
        App\Models\EmailSetting::updateContext($context, $email);
        echo "✅ {$context} actualizado exitosamente\n";
    } catch (Exception $e) {
        echo "❌ Error actualizando {$context}: " . $e->getMessage() . "\n";
    }
}

echo "\n=== VERIFICAR CONFIGURACIÓN ACTUALIZADA ===\n";

foreach ($updates as $context => $expectedEmail) {
    $actualEmail = App\Models\EmailSetting::getEmail($context);
    echo "{$context}: {$actualEmail} " . ($actualEmail === $expectedEmail ? '✅' : '❌') . "\n";
}

echo "\n=== VERIFICAR CONFIGURACIÓN SMTP ===\n";
$smtpConfig = App\Shared\Models\EmailConfiguration::getActive();
if ($smtpConfig) {
    echo "SMTP Username: {$smtpConfig->smtp_username}\n";
    echo "From Email: {$smtpConfig->from_email}\n";
    echo "Configuración completa: " . ($smtpConfig->isComplete() ? 'SÍ' : 'NO') . "\n";
} else {
    echo "❌ No hay configuración SMTP activa\n";
}

echo "\n=== FIN ACTUALIZACIÓN ===\n";