<?php
/**
 * Script de prueba SMTP directo desde web
 * Para diagnosticar diferencias entre terminal y web
 */

// Simular entorno Laravel
require __DIR__ . '/vendor/autoload.php';

// Cargar configuración
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "<h1>Diagnóstico SMTP Web vs Terminal</h1>";

echo "<h2>1. Configuración desde .env</h2>";
echo "MAIL_HOST: " . env('MAIL_HOST', 'NO_ENCONTRADO') . "<br>";
echo "MAIL_PORT: " . env('MAIL_PORT', 'NO_ENCONTRADO') . "<br>";
echo "MAIL_USERNAME: " . env('MAIL_USERNAME', 'NO_ENCONTRADO') . "<br>";
echo "MAIL_PASSWORD: " . (env('MAIL_PASSWORD') ? '***CONFIGURADA***' : 'NO_ENCONTRADA') . "<br>";
echo "MAIL_ENCRYPTION: " . env('MAIL_ENCRYPTION', 'NO_ENCONTRADO') . "<br>";

echo "<h2>2. Configuración desde config()</h2>";
echo "Config Host: " . config('mail.mailers.smtp.host', 'NO_ENCONTRADO') . "<br>";
echo "Config Port: " . config('mail.mailers.smtp.port', 'NO_ENCONTRADO') . "<br>";
echo "Config Username: " . config('mail.mailers.smtp.username', 'NO_ENCONTRADO') . "<br>";
echo "Config Password: " . (config('mail.mailers.smtp.password') ? '***CONFIGURADA***' : 'NO_ENCONTRADA') . "<br>";
echo "Config Encryption: " . config('mail.mailers.smtp.encryption', 'NO_ENCONTRADO') . "<br>";

echo "<h2>3. Llamada directa a EmailService</h2>";
try {
    $result = \App\Services\EmailService::sendTestEmail('mrgrafista@gmail.com');
    echo "Resultado: " . ($result['success'] ? 'ÉXITO' : 'ERROR') . "<br>";
    if (!$result['success']) {
        echo "Error: " . $result['message'] . "<br>";
    }
} catch (Exception $e) {
    echo "Excepción: " . $e->getMessage() . "<br>";
}

echo "<h2>4. Verificar plantillas</h2>";
$templates = \App\Models\EmailTemplate::all();
echo "Total plantillas: " . $templates->count() . "<br>";
foreach ($templates as $template) {
    echo "- " . $template->key . " (" . $template->name . ")<br>";
}

echo "<h2>5. Test de conexión pura</h2>";
$host = config('mail.mailers.smtp.host');
$port = config('mail.mailers.smtp.port');

if ($host && $port) {
    $connection = @fsockopen($host, $port, $errno, $errstr, 10);
    if ($connection) {
        echo "Conexión a $host:$port: ✅ EXITOSA<br>";
        fclose($connection);
    } else {
        echo "Conexión a $host:$port: ❌ FALLÓ - $errstr<br>";
    }
} else {
    echo "Host o puerto no configurados<br>";
}

echo "<h2>6. Información del entorno</h2>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Laravel Version: " . app()->version() . "<br>";
echo "Environment: " . app()->environment() . "<br>";
echo "Config Cached: " . (file_exists(base_path('bootstrap/cache/config.php')) ? 'SÍ' : 'NO') . "<br>";

if (file_exists(base_path('bootstrap/cache/config.php'))) {
    echo "Config Cache Modified: " . date('Y-m-d H:i:s', filemtime(base_path('bootstrap/cache/config.php'))) . "<br>";
}
