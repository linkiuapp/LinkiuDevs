<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Services\EmailService;

class SendEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3; // 3 reintentos máximo
    public $timeout = 60; // 60 segundos timeout

    public function __construct(
        public string $type,
        public string $recipientEmail,
        public array $data = []
    ) {}

    /**
     * Execute the job usando CLI directo (que funciona)
     */
    public function handle(): void
    {
        try {
            Log::info("Procesando email en cola", [
                'type' => $this->type,
                'recipient' => $this->recipientEmail,
                'attempt' => $this->attempts()
            ]);

            if ($this->type === 'test') {
                // Email de prueba usando CLI directo
                $result = $this->sendTestEmailViaCLI($this->recipientEmail);
            } elseif ($this->type === 'template') {
                // Emails con plantilla usando CLI directo
                $result = $this->sendTemplateEmailViaCLI(
                    $this->data['template_key'],
                    $this->recipientEmail,
                    $this->data['variables'] ?? []
                );
            } else {
                throw new \Exception("Tipo de email no soportado: {$this->type}");
            }

            if ($result['success']) {
                Log::info("Email enviado exitosamente desde cola", [
                    'type' => $this->type,
                    'recipient' => $this->recipientEmail,
                    'attempt' => $this->attempts()
                ]);
            } else {
                Log::error("Email falló en cola", [
                    'type' => $this->type,
                    'recipient' => $this->recipientEmail,
                    'error' => $result['message'],
                    'attempt' => $this->attempts()
                ]);
                
                // Fallar el job para que se reintente
                throw new \Exception($result['message']);
            }

        } catch (\Exception $e) {
            Log::error("Error en SendEmailJob", [
                'type' => $this->type,
                'recipient' => $this->recipientEmail,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts()
            ]);
            
            throw $e; // Re-lanzar para reintentos
        }
    }

    /**
     * Enviar email de prueba usando comando CLI directo
     */
    private function sendTestEmailViaCLI(string $email): array
    {
        $command = "cd /home/wwlink/linkiubio_app && php artisan email:send-test " . escapeshellarg($email) . " 2>&1";
        $output = shell_exec($command);
        
        $lines = explode("\n", trim($output));
        $jsonLine = end($lines);
        $result = json_decode($jsonLine, true);
        
        return $result ?: ['success' => false, 'message' => 'Error parsing CLI output'];
    }

    /**
     * Enviar email con plantilla usando CLI directo
     */
    private function sendTemplateEmailViaCLI(string $templateKey, string $email, array $variables): array
    {
        try {
            // Usar EmailService directamente ya que está diseñado para CLI
            $result = EmailService::sendWithTemplate($templateKey, $email, $variables);
            
            return $result;
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error enviando plantilla via CLI: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Handle job failure
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("SendEmailJob falló completamente", [
            'type' => $this->type,
            'recipient' => $this->recipientEmail,
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts()
        ]);
    }
}
