<?php

namespace App\Shared\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;

class EmailConfiguration extends Model
{
    use HasFactory;

    protected $fillable = [
        'smtp_host',
        'smtp_port', 
        'smtp_username',
        'smtp_password',
        'smtp_encryption',
        'from_email',
        'from_name',
        'ticket_created_template',
        'ticket_response_template', 
        'ticket_status_changed_template',
        'ticket_assigned_template',
        'send_on_ticket_created',
        'send_on_ticket_response',
        'send_on_status_change',
        'send_on_ticket_assigned',
        'is_active',
        'last_test_at',
        'last_test_result'
    ];

    protected $casts = [
        'send_on_ticket_created' => 'boolean',
        'send_on_ticket_response' => 'boolean', 
        'send_on_status_change' => 'boolean',
        'send_on_ticket_assigned' => 'boolean',
        'is_active' => 'boolean',
        'last_test_at' => 'datetime'
    ];

    protected $hidden = [
        'smtp_password'
    ];

    /**
     * Encriptar la contraseña SMTP antes de guardar
     */
    public function setSmtpPasswordAttribute($value)
    {
        if ($value) {
            $this->attributes['smtp_password'] = Crypt::encryptString($value);
        }
    }

    /**
     * Desencriptar la contraseña SMTP al acceder
     */
    public function getSmtpPasswordAttribute($value)
    {
        if ($value) {
            try {
                return Crypt::decryptString($value);
            } catch (\Exception $e) {
                return null;
            }
        }
        return null;
    }

    /**
     * Obtener la configuración activa
     */
    public static function getActive()
    {
        return static::where('is_active', true)->first();
    }

    /**
     * Configurar Laravel Mail con esta configuración
     */
    public function applyToMail()
    {
        if (!$this->isComplete()) {
            return false;
        }

        Config::set([
            'mail.mailers.smtp.host' => $this->smtp_host,
            'mail.mailers.smtp.port' => $this->smtp_port,
            'mail.mailers.smtp.username' => $this->smtp_username,
            'mail.mailers.smtp.password' => $this->smtp_password,
            'mail.mailers.smtp.encryption' => $this->smtp_encryption === 'none' ? null : $this->smtp_encryption,
            'mail.from.address' => $this->from_email,
            'mail.from.name' => $this->from_name,
        ]);

        return true;
    }

    /**
     * Verificar si la configuración está completa
     */
    public function isComplete()
    {
        return !empty($this->smtp_host) && 
               !empty($this->smtp_username) && 
               !empty($this->smtp_password) && 
               !empty($this->from_email);
    }

    /**
     * Probar la configuración enviando un email de prueba
     */
    public function testConnection($testEmail = null)
    {
        if (!$this->isComplete()) {
            return [
                'success' => false,
                'message' => 'Configuración incompleta. Faltan datos obligatorios.'
            ];
        }

        try {
            // Crear configuración para MailManager
            $config = [
                'host' => $this->smtp_host,
                'port' => $this->smtp_port,
                'username' => $this->smtp_username,
                'password' => $this->smtp_password,
                'encryption' => $this->smtp_encryption,
                'from_email' => $this->from_email,
                'from_name' => $this->from_name,
            ];
            
            // Usar CLIMailManager que ejecuta el comando que funciona
            $cliMailer = new \App\Mail\CLIMailManager($config);
            $result = $cliMailer->testConnection($testEmail);

            // Actualizar resultado de la prueba
            $this->update([
                'last_test_at' => now(),
                'last_test_result' => $result['success'] ? 'Conexión exitosa' : $result['message']
            ]);

            return $result;

        } catch (\Exception $e) {
            // Actualizar resultado de la prueba con error
            $this->update([
                'last_test_at' => now(),
                'last_test_result' => 'Error: ' . $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Error al enviar email: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Activar esta configuración (desactivando las demás)
     */
    public function activate()
    {
        // Desactivar todas las configuraciones
        static::query()->update(['is_active' => false]);
        
        // Activar esta configuración
        $this->update(['is_active' => true]);
    }

    /**
     * Obtener plantillas por defecto
     */
    public static function getDefaultTemplates()
    {
        return [
            'ticket_created' => 'Se ha creado un nuevo ticket #{{ticket_number}}: {{title}}. Puedes ver los detalles en: {{url}}',
            'ticket_response' => 'Hay una nueva respuesta en el ticket #{{ticket_number}}. {{response_preview}}. Ver: {{url}}',
            'ticket_status_changed' => 'El ticket #{{ticket_number}} cambió de estado de "{{old_status}}" a "{{new_status}}". Ver: {{url}}',
            'ticket_assigned' => 'Se te ha asignado el ticket #{{ticket_number}}: {{title}}. Ver: {{url}}'
        ];
    }
}
