<?php

require_once 'bootstrap/app.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== DEBUG DETALLADO COMPARACIÓN ===\n\n";

// 1. Obtener configuración
$emailConfig = \App\Shared\Models\EmailConfiguration::getActive();

if (!$emailConfig) {
    echo "❌ No hay configuración activa\n";
    exit;
}

echo "📧 Configuración encontrada:\n";
echo "Host: " . $emailConfig->smtp_host . "\n";
echo "Port: " . $emailConfig->smtp_port . "\n";
echo "Username: " . $emailConfig->smtp_username . "\n";
echo "Password length: " . strlen($emailConfig->smtp_password) . "\n";
echo "Encryption: " . $emailConfig->smtp_encryption . "\n";
echo "From Email: " . $emailConfig->from_email . "\n";
echo "From Name: " . $emailConfig->from_name . "\n\n";

// 2. Probar testConnection (que funciona)
echo "🔍 PROBANDO testConnection() (CLI que funciona):\n";
try {
    $result = $emailConfig->testConnection('test@linkiu.email');
    echo "Result: " . ($result['success'] ? 'SUCCESS ✅' : 'FAILED ❌') . "\n";
    echo "Message: " . $result['message'] . "\n\n";
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n\n";
}

// 3. Probar sendTestEmail (que falla)
echo "🔍 PROBANDO sendTestEmail() (Panel que falla):\n";
try {
    $result = \App\Services\EmailService::sendTestEmail('test@linkiu.email');
    echo "Result: " . ($result['success'] ? 'SUCCESS ✅' : 'FAILED ❌') . "\n";
    echo "Message: " . $result['message'] . "\n\n";
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n\n";
}

// 4. Verificar configuración de Mail después de cada método
echo "🔧 CONFIGURACIÓN MAIL DESPUÉS DE testConnection():\n";
$emailConfig->applyToMail();
echo "Host: " . config('mail.mailers.smtp.host') . "\n";
echo "Port: " . config('mail.mailers.smtp.port') . "\n";
echo "Username: " . config('mail.mailers.smtp.username') . "\n";
echo "Password length: " . strlen(config('mail.mailers.smtp.password')) . "\n";
echo "Encryption: " . config('mail.mailers.smtp.encryption') . "\n";
echo "From Address: " . config('mail.from.address') . "\n";
echo "From Name: " . config('mail.from.name') . "\n\n";

// 5. Probar Mail::raw directamente con la configuración aplicada
echo "🔍 PROBANDO Mail::raw() DIRECTAMENTE:\n";
try {
    \Illuminate\Support\Facades\Mail::raw(
        'Prueba directa de Mail::raw',
        function ($message) {
            $message->to('test@linkiu.email')
                   ->from(config('mail.from.address'), config('mail.from.name'))
                   ->subject('Prueba Mail::raw directo');
        }
    );
    echo "Mail::raw directo: SUCCESS ✅\n";
} catch (Exception $e) {
    echo "Mail::raw directo: FAILED ❌\n";
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n=== FIN DEBUG DETALLADO ===\n";