<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== DIAGNÓSTICO ESPECÍFICO PARA PANEL DE ADMIN ===\n\n";

// 1. Verificar que el código actualizado esté en producción
echo "1. VERIFICAR CÓDIGO ACTUALIZADO:\n";
$emailServicePath = app_path('Services/EmailService.php');
$content = file_get_contents($emailServicePath);

if (strpos($content, 'EmailConfiguration::getActive()') !== false) {
    echo "✅ Código actualizado encontrado - EmailConfiguration::getActive() presente\n";
} else {
    echo "❌ Código NO actualizado - EmailConfiguration::getActive() NO encontrado\n";
}

if (strpos($content, 'applyToMail()') !== false) {
    echo "✅ Código actualizado encontrado - applyToMail() presente\n";
} else {
    echo "❌ Código NO actualizado - applyToMail() NO encontrado\n";
}

echo "\n";

// 2. Simular exactamente lo que hace el panel
echo "2. SIMULAR LLAMADA DEL PANEL:\n";
try {
    // Esto es exactamente lo que hace el controlador
    $result = App\Services\EmailService::sendTestEmail('mrgrafista@gmail.com');
    
    echo "Resultado del panel:\n";
    echo "Success: " . ($result['success'] ? 'SÍ' : 'NO') . "\n";
    echo "Message: " . $result['message'] . "\n";
    
} catch (Exception $e) {
    echo "❌ Error en simulación del panel: " . $e->getMessage() . "\n";
}

echo "\n";

// 3. Verificar configuración actual después de llamada
echo "3. CONFIGURACIÓN DESPUÉS DE LLAMADA:\n";
echo "Host: " . config('mail.mailers.smtp.host') . "\n";
echo "Port: " . config('mail.mailers.smtp.port') . "\n";
echo "Username: " . config('mail.mailers.smtp.username') . "\n";
echo "Encryption: " . config('mail.mailers.smtp.encryption') . "\n";
echo "From Address: " . config('mail.from.address') . "\n";
echo "From Name: " . config('mail.from.name') . "\n";

echo "\n";

// 4. Verificar configuración de BD
echo "4. CONFIGURACIÓN DE BASE DE DATOS:\n";
$emailConfig = App\Shared\Models\EmailConfiguration::getActive();
if ($emailConfig) {
    echo "✅ Configuración activa encontrada\n";
    echo "Host: {$emailConfig->smtp_host}\n";
    echo "Username: {$emailConfig->smtp_username}\n";
    echo "Complete: " . ($emailConfig->isComplete() ? 'SÍ' : 'NO') . "\n";
    
    // Probar applyToMail directamente
    echo "\n5. PROBAR applyToMail() DIRECTAMENTE:\n";
    $result = $emailConfig->applyToMail();
    echo "applyToMail result: " . ($result ? 'SUCCESS' : 'FAILED') . "\n";
    
    echo "Configuración después de applyToMail:\n";
    echo "Host: " . config('mail.mailers.smtp.host') . "\n";
    echo "Username: " . config('mail.mailers.smtp.username') . "\n";
    
} else {
    echo "❌ No hay configuración activa\n";
}

echo "\n=== FIN DIAGNÓSTICO ===\n";