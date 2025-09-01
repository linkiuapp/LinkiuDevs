<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== CREAR CONFIGURACIÓN DE EMAIL EN BASE DE DATOS ===\n\n";

try {
    // Crear configuración de email en la base de datos
    $emailConfig = App\Shared\Models\EmailConfiguration::create([
        'smtp_host' => 'mail.linkiu.email',
        'smtp_port' => 587,
        'smtp_username' => 'no-responder@linkiu.email',
        'smtp_password' => 't1fChP1pYbDYVt80e6', // Se encriptará automáticamente
        'smtp_encryption' => 'tls',
        'from_email' => 'no-responder@linkiu.email',
        'from_name' => 'LinkiuBio',
        'is_active' => true,
        'send_on_ticket_created' => true,
        'send_on_ticket_response' => true,
        'send_on_status_change' => true,
        'send_on_ticket_assigned' => true,
    ]);

    echo "✅ Configuración de email creada exitosamente!\n";
    echo "ID: {$emailConfig->id}\n";
    echo "Host: {$emailConfig->smtp_host}\n";
    echo "Username: {$emailConfig->smtp_username}\n";
    echo "From: {$emailConfig->from_email}\n";
    echo "Active: " . ($emailConfig->is_active ? 'SÍ' : 'NO') . "\n";
    echo "Complete: " . ($emailConfig->isComplete() ? 'SÍ' : 'NO') . "\n\n";

    // Probar la configuración
    echo "🧪 Probando configuración...\n";
    $testResult = $emailConfig->testConnection('mrgrafista@gmail.com');
    
    if ($testResult['success']) {
        echo "✅ " . $testResult['message'] . "\n";
    } else {
        echo "❌ " . $testResult['message'] . "\n";
    }

} catch (Exception $e) {
    echo "❌ Error creando configuración: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== FIN ===\n";