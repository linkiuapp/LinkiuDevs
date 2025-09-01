<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\EmailService;
use App\Shared\Models\EmailConfiguration;

class TestEmailSystemCommand extends Command
{
    protected $signature = 'email:test-system {email}';
    protected $description = 'Probar el sistema de email unificado';

    public function handle()
    {
        $email = $this->argument('email');
        
        $this->info("🧪 Probando sistema de email unificado...");
        $this->info("📧 Email destino: {$email}");
        $this->newLine();
        
        // Test 1: EmailConfiguration (método que funciona)
        $this->info("1️⃣ Probando EmailConfiguration (método que funciona)...");
        try {
            $emailConfig = EmailConfiguration::getActive();
            if ($emailConfig) {
                $result = $emailConfig->testConnection($email);
                
                if ($result['success']) {
                    $this->info("✅ EmailConfiguration: " . $result['message']);
                } else {
                    $this->error("❌ EmailConfiguration: " . $result['message']);
                }
            } else {
                $this->error("❌ No hay configuración activa");
            }
        } catch (\Exception $e) {
            $this->error("❌ EmailConfiguration Exception: " . $e->getMessage());
        }
        
        $this->newLine();
        
        // Test 2: EmailService (ahora usa EmailConfiguration internamente)
        $this->info("2️⃣ Probando EmailService...");
        try {
            $result = EmailService::sendTestEmail($email);
            
            if ($result['success']) {
                $this->info("✅ EmailService: " . $result['message']);
            } else {
                $this->error("❌ EmailService: " . $result['message']);
            }
        } catch (\Exception $e) {
            $this->error("❌ EmailService Exception: " . $e->getMessage());
        }
        
        $this->newLine();
        
        // Mostrar configuración actual
        $this->info("📋 Configuración actual:");
        try {
            $emailConfig = EmailConfiguration::getActive();
            if ($emailConfig) {
                $this->table(
                    ['Parámetro', 'Valor'],
                    [
                        ['Host', $emailConfig->smtp_host],
                        ['Puerto', $emailConfig->smtp_port],
                        ['Usuario', $emailConfig->smtp_username],
                        ['Encriptación', $emailConfig->smtp_encryption],
                        ['From Email', $emailConfig->from_email],
                        ['From Name', $emailConfig->from_name],
                    ]
                );
            } else {
                $this->error("No hay configuración activa");
            }
        } catch (\Exception $e) {
            $this->error("Error obteniendo configuración: " . $e->getMessage());
        }
        
        return 0;
    }
}