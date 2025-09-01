<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Shared\Models\EmailConfiguration;

class SendTestEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $email;
    protected $jobId;

    public function __construct(string $email, string $jobId)
    {
        $this->email = $email;
        $this->jobId = $jobId;
    }

    public function handle()
    {
        try {
            $emailConfig = EmailConfiguration::getActive();
            
            if (!$emailConfig || !$emailConfig->isComplete()) {
                $this->logResult(false, 'No hay configuración SMTP completa');
                return;
            }
            
            // Usar testConnection que sabemos que funciona
            $result = $emailConfig->testConnection($this->email);
            
            $this->logResult($result['success'], $result['message']);
            
        } catch (\Exception $e) {
            $this->logResult(false, 'Error: ' . $e->getMessage());
        }
    }
    
    private function logResult(bool $success, string $message)
    {
        Log::info('TestEmailJob result', [
            'job_id' => $this->jobId,
            'email' => $this->email,
            'success' => $success,
            'message' => $message
        ]);
    }
}