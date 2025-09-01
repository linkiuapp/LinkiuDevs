<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;
use Exception;

class TestEmailConfiguration extends Command
{
    protected $signature = 'email:test {email} {--config=default}';
    protected $description = 'Test email configuration with different settings';

    public function handle()
    {
        $email = $this->argument('email');
        $configType = $this->option('config');
        
        $this->info("Testing email configuration: {$configType}");
        $this->info("Sending test email to: {$email}");
        
        // Test different configurations
        $configurations = [
            'default' => [
                'host' => 'smtp.office365.com',
                'port' => 587,
                'encryption' => 'tls',
                'verify_peer' => true,
                'verify_peer_name' => true,
                'allow_self_signed' => false,
            ],
            'no-verify' => [
                'host' => 'smtp.office365.com',
                'port' => 587,
                'encryption' => 'tls',
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true,
            ],
            'ssl-465' => [
                'host' => 'smtp.office365.com',
                'port' => 465,
                'encryption' => 'ssl',
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true,
            ],
            'no-encryption' => [
                'host' => 'smtp.office365.com',
                'port' => 587,
                'encryption' => null,
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true,
            ],
        ];
        
        $config = $configurations[$configType] ?? $configurations['default'];
        
        // Apply configuration temporarily
        Config::set('mail.mailers.smtp.host', $config['host']);
        Config::set('mail.mailers.smtp.port', $config['port']);
        Config::set('mail.mailers.smtp.encryption', $config['encryption']);
        Config::set('mail.mailers.smtp.verify_peer', $config['verify_peer']);
        Config::set('mail.mailers.smtp.verify_peer_name', $config['verify_peer_name']);
        Config::set('mail.mailers.smtp.allow_self_signed', $config['allow_self_signed']);
        
        $this->table(['Setting', 'Value'], [
            ['Host', $config['host']],
            ['Port', $config['port']],
            ['Encryption', $config['encryption'] ?? 'none'],
            ['Verify Peer', $config['verify_peer'] ? 'true' : 'false'],
            ['Verify Peer Name', $config['verify_peer_name'] ? 'true' : 'false'],
            ['Allow Self Signed', $config['allow_self_signed'] ? 'true' : 'false'],
        ]);
        
        try {
            Mail::raw('Este es un email de prueba desde el comando artisan. Configuración: ' . $configType, function ($message) use ($email) {
                $message->to($email)
                        ->subject('Test Email - Configuración: ' . $this->option('config'))
                        ->from(config('mail.from.address'), config('mail.from.name'));
            });
            
            $this->info('✅ Email enviado exitosamente!');
            return 0;
            
        } catch (Exception $e) {
            $this->error('❌ Error al enviar email:');
            $this->error($e->getMessage());
            
            // Additional debugging info
            $this->warn('Información adicional de debug:');
            $this->line('- Verificar que el App Password de Microsoft 365 sea correcto');
            $this->line('- Verificar que la cuenta tenga permisos SMTP');
            $this->line('- Verificar conectividad del servidor a smtp.office365.com:587');
            
            return 1;
        }
    }
}