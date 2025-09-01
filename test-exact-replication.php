<?php

require_once 'bootstrap/app.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== REPLICACIÓN EXACTA DE testConnection() ===\n\n";

// 1. Obtener configuración (igual que testConnection)
$emailConfig = \App\Shared\Models\EmailConfiguration::getActive();

if (!$emailConfig || !$emailConfig->isComplete()) {
    echo "❌ Configuración incompleta\n";
    exit;
}

echo "✅ Configuración obtenida\n";
echo "Host: " . $emailConfig->smtp_host . "\n";
echo "Username: " . $emailConfig->smtp_username . "\n";
echo "Password length: " . strlen($emailConfig->smtp_password) . "\n\n";

// 2. Aplicar configuración (igual que testConnection)
echo "🔧 Aplicando configuración...\n";
$applied = $emailConfig->applyToMail();
echo "Configuración aplicada: " . ($applied ? 'SUCCESS' : 'FAILED') . "\n\n";

// 3. Verificar configuración aplicada
echo "📋 Configuración Mail después de aplicar:\n";
echo "Host: " . config('mail.mailers.smtp.host') . "\n";
echo "Port: " . config('mail.mailers.smtp.port') . "\n";
echo "Username: " . config('mail.mailers.smtp.username') . "\n";
echo "Password: " . config('mail.mailers.smtp.password') . "\n";
echo "Encryption: " . config('mail.mailers.smtp.encryption') . "\n";
echo "From Address: " . config('mail.from.address') . "\n";
echo "From Name: " . config('mail.from.name') . "\n\n";

// 4. Usar EmailService::sendRaw exactamente como testConnection()
echo "📧 Llamando EmailService::sendRaw (igual que testConnection)...\n";
try {
    $result = \App\Services\EmailService::sendRaw(
        'Esta es una prueba de configuración SMTP desde Linkiu.bio',
        ['test@linkiu.email'],
        'Prueba de configuración SMTP - Linkiu.bio',
        'support'
    );
    
    echo "sendRaw result: " . ($result ? 'SUCCESS ✅' : 'FAILED ❌') . "\n";
    
    if ($result) {
        echo "✅ ¡FUNCIONA! El problema no está en sendRaw\n";
    } else {
        echo "❌ sendRaw falla - investigar más\n";
    }
    
} catch (Exception $e) {
    echo "❌ Exception en sendRaw: " . $e->getMessage() . "\n";
}

echo "\n=== FIN REPLICACIÓN ===\n";