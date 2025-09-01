<?php

use App\Shared\Models\EmailConfiguration;

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== VERIFICAR CONFIGURACIÓN EN BD ===\n";

$config = EmailConfiguration::getActive();

if ($config) {
    echo "✅ Configuración activa encontrada:\n";
    echo "Host: " . $config->smtp_host . "\n";
    echo "Port: " . $config->smtp_port . "\n";
    echo "Username: " . $config->smtp_username . "\n";
    echo "Password: " . ($config->smtp_password ? '[CONFIGURADA - ' . strlen($config->smtp_password) . ' chars]' : '[VACÍA]') . "\n";
    echo "Encryption: " . $config->smtp_encryption . "\n";
    echo "From Email: " . $config->from_email . "\n";
    echo "From Name: " . $config->from_name . "\n";
    echo "Is Active: " . ($config->is_active ? 'YES' : 'NO') . "\n";
    echo "Last Test: " . ($config->last_test_at ? $config->last_test_at->format('Y-m-d H:i:s') : 'NUNCA') . "\n";
    echo "Last Result: " . ($config->last_test_result ?: 'N/A') . "\n";
    
    echo "\n=== PROBANDO CONFIGURACIÓN DE BD ===\n";
    
    // Probar con la configuración de la BD
    $result = $config->testConnection('mrgrafista@gmail.com');
    
    if ($result['success']) {
        echo "✅ ÉXITO: " . $result['message'] . "\n";
    } else {
        echo "❌ ERROR: " . $result['message'] . "\n";
    }
    
} else {
    echo "❌ No hay configuración activa en la base de datos\n";
    
    echo "\nTodas las configuraciones:\n";
    $allConfigs = EmailConfiguration::all();
    foreach ($allConfigs as $cfg) {
        echo "ID: {$cfg->id}, Active: " . ($cfg->is_active ? 'YES' : 'NO') . ", Host: {$cfg->smtp_host}\n";
    }
}

echo "\n=== FIN ===\n";