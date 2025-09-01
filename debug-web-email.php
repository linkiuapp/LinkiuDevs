<?php
// Este archivo debe ejecutarse desde el navegador para simular el contexto web

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Simular una request HTTP
$request = Illuminate\Http\Request::create('/debug-email', 'GET');
$response = $kernel->handle($request);

// Bootstrap de la aplicación
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

header('Content-Type: text/plain');

echo "=== DIAGNÓSTICO WEB CONTEXT ===\n\n";

// 1. Información del contexto
echo "1. CONTEXTO DE EJECUCIÓN:\n";
echo "SAPI: " . php_sapi_name() . "\n";
echo "User: " . get_current_user() . "\n";
echo "Working Dir: " . getcwd() . "\n";
echo "Script: " . $_SERVER['SCRIPT_NAME'] ?? 'N/A' . "\n";

echo "\n";

// 2. Verificar configuración actual
echo "2. CONFIGURACIÓN ACTUAL (WEB):\n";
echo "Host: " . config('mail.mailers.smtp.host') . "\n";
echo "Port: " . config('mail.mailers.smtp.port') . "\n";
echo "Username: " . config('mail.mailers.smtp.username') . "\n";
echo "Encryption: " . config('mail.mailers.smtp.encryption') . "\n";
echo "From Address: " . config('mail.from.address') . "\n";

echo "\n";

// 3. Verificar EmailConfiguration
echo "3. EMAIL CONFIGURATION (WEB):\n";
try {
    $emailConfig = App\Shared\Models\EmailConfiguration::getActive();
    if ($emailConfig) {
        echo "✅ Configuración encontrada\n";
        echo "Host: {$emailConfig->smtp_host}\n";
        echo "Username: {$emailConfig->smtp_username}\n";
        echo "Complete: " . ($emailConfig->isComplete() ? 'SÍ' : 'NO') . "\n";
        echo "Has Password: " . (!empty($emailConfig->smtp_password) ? 'SÍ' : 'NO') . "\n";
        
        if (!empty($emailConfig->smtp_password)) {
            echo "Password Length: " . strlen($emailConfig->smtp_password) . "\n";
        }
    } else {
        echo "❌ No se encontró configuración\n";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n";

// 4. Probar sendTestEmail en contexto web
echo "4. PROBAR sendTestEmail (WEB CONTEXT):\n";
try {
    $result = App\Services\EmailService::sendTestEmail('mrgrafista@gmail.com');
    echo "Success: " . ($result['success'] ? 'SÍ' : 'NO') . "\n";
    echo "Message: " . $result['message'] . "\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n";

// 5. Verificar configuración después de la llamada
echo "5. CONFIGURACIÓN DESPUÉS DE LLAMADA (WEB):\n";
echo "Host: " . config('mail.mailers.smtp.host') . "\n";
echo "Username: " . config('mail.mailers.smtp.username') . "\n";
echo "From Address: " . config('mail.from.address') . "\n";

echo "\n=== FIN DIAGNÓSTICO WEB ===\n";

$kernel->terminate($request, $response);