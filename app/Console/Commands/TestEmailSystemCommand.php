<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Mail\MailManager;
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
        
        // Test 1: MailManager directo (Symfony)
        $this->info("1️⃣ Probando MailManager (Symfony)...");
        try {
            $mailManager = new MailManager();
            $result = $mailManager->testConnection($email);
            
            if ($result['success']) {
                $this->info("✅ MailManager: " . $result['message']);
            } else {
                $this->error("❌ MailManager: " . $result['message']);
            }
        } catch (\Exception $e) {
            $this->error("❌ MailManager Exception: " . $e->getMessage());
        }
        
        $this->newLine();
        
        // Test 1.5: PHPMailerManager (PHP Nativo)
        $this->info("1️⃣.5 Probando PHPMailerManager (PHP Nativo)...");
        try {
            $phpMailer = new \App\Mail\PHPMailerManager();
            $result = $phpMailer->testConnection($email);
            
            if ($result['success']) {
                $this->info("✅ PHPMailerManager: " . $result['message']);
            } else {
                $this->error("❌ PHPMailerManager: " . $result['message']);
            }
        } catch (\Exception $e) {
            $this->error("❌ PHPMailerManager Exception: " . $e->getMessage());
        }
        
        $this->newLine();
        
        // Test 2: EmailService
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
        
        // Test 3: EmailConfiguration
        $this->info("3️⃣ Probando EmailConfiguration...");
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
        
        // Mostrar configuración actual
        $this->info("📋 Configuración actual:");
        try {
            $mailManager = new MailManager();
            $config = $mailManager->getConfigInfo();
            
            $this->table(
                ['Parámetro', 'Valor'],
                [
                    ['Host', $config['host']],
                    ['Puerto', $config['port']],
                    ['Usuario', $config['username']],
                    ['Encriptación', $config['encryption']],
                    ['From Email', $config['from_email']],
                    ['From Name', $config['from_name']],
                ]
            );
        } catch (\Exception $e) {
            $this->error("Error obteniendo configuración: " . $e->getMessage());
        }
        
        return 0;
    }
}