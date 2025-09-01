<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Shared\Models\EmailConfiguration;

class SendTestEmailCommand extends Command
{
    protected $signature = 'email:send-test {email}';
    protected $description = 'Send test email using SMTP configuration';

    public function handle()
    {
        $email = $this->argument('email');
        
        try {
            $emailConfig = EmailConfiguration::getActive();
            
            if (!$emailConfig || !$emailConfig->isComplete()) {
                $this->error('No hay configuración SMTP completa disponible');
                return 1;
            }
            
            // Usar testConnection que sabemos que funciona
            $result = $emailConfig->testConnection($email);
            
            if ($result['success']) {
                $this->info('SUCCESS: ' . $result['message']);
                return 0;
            } else {
                $this->error('FAILED: ' . $result['message']);
                return 1;
            }
            
        } catch (\Exception $e) {
            $this->error('ERROR: ' . $e->getMessage());
            return 1;
        }
    }
}