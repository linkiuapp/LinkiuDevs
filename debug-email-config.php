<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== DIAGNÓSTICO DE CONFIGURACIÓN DE EMAIL ===\n\n";

// 1. Verificar configuración actual de Laravel
echo "1. CONFIGURACIÓN ACTUAL DE LARAVEL:\n";
echo "MAIL_HOST: " . config('mail.mailers.smtp.host') . "\n";
echo "MAIL_PORT: " . config('mail.mailers.smtp.port') . "\n";
echo "MAIL_USERNAME: " . config('mail.mailers.smtp.username') . "\n";
echo "MAIL_ENCRYPTION: " . config('mail.mailers.smtp.encryption') . "\n";
echo "MAIL_FROM_ADDRESS: " . config('mail.from.address') . "\n";
echo "MAIL_FROM_NAME: " . config('mail.from.name') . "\n\n";

// 2. Verificar si existe la tabla email_configurations
echo "2. VERIFICAR TABLA EMAIL_CONFIGURATIONS:\n";
try {
    $tableExists = Schema::hasTable('email_configurations');
    echo "Tabla existe: " . ($tableExists ? 'SÍ' : 'NO') . "\n";
    
    if ($tableExists) {
        $count = DB::table('email_configurations')->count();
        echo "Registros en tabla: $count\n";
        
        if ($count > 0) {
            $configs = DB::table('email_configurations')->get();
            foreach ($configs as $config) {
                echo "ID: {$config->id}, Active: " . ($config->is_active ? 'SÍ' : 'NO') . 
                     ", Host: {$config->smtp_host}, Username: {$config->smtp_username}\n";
            }
        }
    }
} catch (Exception $e) {
    echo "Error verificando tabla: " . $e->getMessage() . "\n";
}

echo "\n";

// 3. Verificar EmailConfiguration::getActive()
echo "3. VERIFICAR EmailConfiguration::getActive():\n";
try {
    $emailConfig = App\Shared\Models\EmailConfiguration::getActive();
    
    if ($emailConfig) {
        echo "Configuración activa encontrada:\n";
        echo "ID: {$emailConfig->id}\n";
        echo "Host: {$emailConfig->smtp_host}\n";
        echo "Port: {$emailConfig->smtp_port}\n";
        echo "Username: {$emailConfig->smtp_username}\n";
        echo "Encryption: {$emailConfig->smtp_encryption}\n";
        echo "From Email: {$emailConfig->from_email}\n";
        echo "From Name: {$emailConfig->from_name}\n";
        echo "Is Complete: " . ($emailConfig->isComplete() ? 'SÍ' : 'NO') . "\n";
        echo "Has Password: " . (!empty($emailConfig->smtp_password) ? 'SÍ' : 'NO') . "\n";
        
        if (!empty($emailConfig->smtp_password)) {
            echo "Password Length: " . strlen($emailConfig->smtp_password) . " chars\n";
        }
    } else {
        echo "NO se encontró configuración activa\n";
    }
} catch (Exception $e) {
    echo "Error obteniendo configuración: " . $e->getMessage() . "\n";
}

echo "\n";

// 4. Probar applyToMail()
echo "4. PROBAR applyToMail():\n";
try {
    $emailConfig = App\Shared\Models\EmailConfiguration::getActive();
    
    if ($emailConfig && $emailConfig->isComplete()) {
        echo "Configuración ANTES de applyToMail():\n";
        echo "Host: " . config('mail.mailers.smtp.host') . "\n";
        echo "Username: " . config('mail.mailers.smtp.username') . "\n";
        
        $result = $emailConfig->applyToMail();
        echo "applyToMail() result: " . ($result ? 'SUCCESS' : 'FAILED') . "\n";
        
        echo "Configuración DESPUÉS de applyToMail():\n";
        echo "Host: " . config('mail.mailers.smtp.host') . "\n";
        echo "Username: " . config('mail.mailers.smtp.username') . "\n";
        echo "From Address: " . config('mail.from.address') . "\n";
    } else {
        echo "No se puede probar applyToMail() - configuración incompleta o no existe\n";
    }
} catch (Exception $e) {
    echo "Error probando applyToMail(): " . $e->getMessage() . "\n";
}

echo "\n=== FIN DIAGNÓSTICO ===\n";