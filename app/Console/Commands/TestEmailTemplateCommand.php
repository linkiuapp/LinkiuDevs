<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\EmailTemplate;
use App\Services\EmailService;

class TestEmailTemplateCommand extends Command
{
    protected $signature = 'email:test-template {template_key} {recipient}';
    protected $description = 'Envía un email de prueba de una plantilla específica con variables de ejemplo';

    public function handle()
    {
        $templateKey = $this->argument('template_key');
        $recipient = $this->argument('recipient');
        
        try {
            $template = EmailTemplate::getByKey($templateKey);
            
            if (!$template) {
                $this->error("Plantilla '{$templateKey}' no encontrada");
                return 1;
            }
            
            // Variables de ejemplo según el contexto
            $variables = $this->getExampleVariables($templateKey);
            
            $this->info("Enviando plantilla '{$templateKey}' a {$recipient}...");
            $this->info("Variables: " . json_encode($variables, JSON_PRETTY_PRINT));
            
            EmailService::sendWithTemplate($templateKey, $recipient, $variables);
            
            $this->info("✅ Email enviado exitosamente!");
            return 0;
            
        } catch (\Exception $e) {
            $this->error("❌ Error al enviar email: " . $e->getMessage());
            return 1;
        }
    }
    
    private function getExampleVariables($templateKey)
    {
        $baseVariables = [
            'app_name' => 'LinkiuBio',
            'support_email' => 'soporte@linkiu.email',
            'current_year' => date('Y')
        ];
        
        switch ($templateKey) {
            case 'store_welcome':
                return array_merge($baseVariables, [
                    'store_name' => 'Mi Tienda Ejemplo',
                    'admin_name' => 'Juan Pérez',
                    'store_url' => 'https://linkiu.bio/mi-tienda-ejemplo',
                    'plan_name' => 'Plan Premium'
                ]);
                
            case 'store_credentials':
                return array_merge($baseVariables, [
                    'store_name' => 'Mi Tienda Ejemplo',
                    'admin_name' => 'Juan Pérez',
                    'admin_email' => 'admin@ejemplo.com',
                    'password' => 'MiPassword123!',
                    'login_url' => 'https://linkiu.bio/mi-tienda-ejemplo/admin',
                    'store_url' => 'https://linkiu.bio/mi-tienda-ejemplo'
                ]);
                
            case 'store_status_changed':
                return array_merge($baseVariables, [
                    'store_name' => 'Mi Tienda Ejemplo',
                    'admin_name' => 'Juan Pérez',
                    'old_value' => 'Activa',
                    'new_value' => 'Suspendida',
                    'change_type' => 'estado',
                    'changed_by' => 'Super Admin',
                    'change_date' => date('d/m/Y H:i'),
                    'login_url' => 'https://linkiu.bio/mi-tienda-ejemplo/admin',
                    'store_url' => 'https://linkiu.bio/mi-tienda-ejemplo'
                ]);
                
            case 'store_plan_changed':
                return array_merge($baseVariables, [
                    'store_name' => 'Mi Tienda Ejemplo',
                    'admin_name' => 'Juan Pérez',
                    'old_value' => 'Plan Básico',
                    'new_value' => 'Plan Premium',
                    'change_type' => 'plan',
                    'changed_by' => 'Super Admin',
                    'change_date' => date('d/m/Y H:i'),
                    'login_url' => 'https://linkiu.bio/mi-tienda-ejemplo/admin',
                    'store_url' => 'https://linkiu.bio/mi-tienda-ejemplo'
                ]);
                
            case 'store_verified':
                return array_merge($baseVariables, [
                    'store_name' => 'Mi Tienda Ejemplo',
                    'admin_name' => 'Juan Pérez',
                    'verification_status' => 'Verificada',
                    'changed_by' => 'Super Admin',
                    'change_date' => date('d/m/Y H:i'),
                    'login_url' => 'https://linkiu.bio/mi-tienda-ejemplo/admin',
                    'store_url' => 'https://linkiu.bio/mi-tienda-ejemplo'
                ]);
                
            case 'store_unverified':
                return array_merge($baseVariables, [
                    'store_name' => 'Mi Tienda Ejemplo',
                    'admin_name' => 'Juan Pérez',
                    'verification_status' => 'No verificada',
                    'changed_by' => 'Super Admin',
                    'change_date' => date('d/m/Y H:i'),
                    'login_url' => 'https://linkiu.bio/mi-tienda-ejemplo/admin',
                    'store_url' => 'https://linkiu.bio/mi-tienda-ejemplo'
                ]);
                
            case 'ticket_updated':
                return array_merge($baseVariables, [
                    'ticket_id' => '#TK-001',
                    'user_name' => 'Juan Pérez',
                    'update_type' => 'Estado actualizado',
                    'old_value' => 'Pendiente',
                    'new_value' => 'En progreso',
                    'changed_by' => 'Agente de Soporte',
                    'admin_url' => 'https://linkiu.bio/superlinkiu/tickets/1'
                ]);
                
            default:
                return $baseVariables;
        }
    }
}
