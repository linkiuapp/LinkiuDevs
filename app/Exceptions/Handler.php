<?php

namespace App\Exceptions;

use App\Services\SystemDebugService;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Log;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            // Send automatic error notifications for critical errors
            $this->sendErrorNotification($e);
        });
    }

    /**
     * Send error notification for critical errors
     */
    protected function sendErrorNotification(Throwable $exception): void
    {
        // Only send notifications for critical errors and if enabled
        if (!env('DEBUG_NOTIFICATIONS_ENABLED', false)) {
            return;
        }

        // Skip certain types of exceptions
        $skipExceptions = [
            \Illuminate\Http\Exceptions\HttpResponseException::class,
            \Illuminate\Auth\AuthenticationException::class,
            \Illuminate\Validation\ValidationException::class,
            \Symfony\Component\HttpKernel\Exception\HttpException::class,
        ];

        foreach ($skipExceptions as $skipException) {
            if ($exception instanceof $skipException) {
                return;
            }
        }

        // Only send for 500-level errors or specific critical exceptions
        $criticalExceptions = [
            \ErrorException::class,
            \ParseError::class,
            \TypeError::class,
            \ArgumentCountError::class,
            \ArithmeticError::class,
            \AssertionError::class,
        ];

        $isCritical = false;
        foreach ($criticalExceptions as $criticalException) {
            if ($exception instanceof $criticalException) {
                $isCritical = true;
                break;
            }
        }

        if (!$isCritical) {
            return;
        }

        try {
            $errorData = [
                'timestamp' => now()->toDateTimeString(),
                'level' => 'ERROR',
                'message' => $exception->getMessage(),
                'stack_trace' => $exception->getTraceAsString(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'url' => request()->fullUrl(),
                'method' => request()->method(),
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'user_id' => auth()->id(),
            ];

            SystemDebugService::sendErrorNotification($errorData);
        } catch (\Exception $e) {
            // Don't let notification failures break the application
            Log::error('Failed to send error notification', [
                'notification_error' => $e->getMessage(),
                'original_error' => $exception->getMessage()
            ]);
        }
    }
}