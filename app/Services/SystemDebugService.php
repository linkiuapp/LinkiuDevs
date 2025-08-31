<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class SystemDebugService
{
    /**
     * Get recent error logs
     */
    public static function getRecentErrors(int $limit = 100, string $level = null): array
    {
        $logPath = storage_path('logs/laravel.log');
        
        if (!File::exists($logPath)) {
            return [];
        }

        $logs = [];
        $currentEntry = null;
        $lines = array_reverse(file($logPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));
        
        foreach ($lines as $line) {
            // Check if this is a new log entry (starts with timestamp)
            if (preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] \w+\.(\w+):/', $line, $matches)) {
                // Save previous entry if exists
                if ($currentEntry && (!$level || $currentEntry['level'] === $level)) {
                    $logs[] = $currentEntry;
                    if (count($logs) >= $limit) {
                        break;
                    }
                }
                
                // Start new entry
                $currentEntry = [
                    'timestamp' => $matches[1],
                    'level' => strtoupper($matches[2]),
                    'message' => $line,
                    'stack_trace' => '',
                    'context' => []
                ];
            } else {
                // Add to current entry's stack trace
                if ($currentEntry) {
                    $currentEntry['stack_trace'] = $line . "\n" . $currentEntry['stack_trace'];
                }
            }
        }
        
        // Add the last entry
        if ($currentEntry && (!$level || $currentEntry['level'] === $level)) {
            $logs[] = $currentEntry;
        }
        
        return array_slice($logs, 0, $limit);
    }

    /**
     * Get error statistics
     */
    public static function getErrorStats(): array
    {
        $errors = self::getRecentErrors(1000);
        $stats = [
            'total' => count($errors),
            'by_level' => [],
            'by_hour' => [],
            'recent_24h' => 0
        ];

        $now = Carbon::now();
        
        foreach ($errors as $error) {
            $level = $error['level'];
            $timestamp = Carbon::parse($error['timestamp']);
            
            // Count by level
            $stats['by_level'][$level] = ($stats['by_level'][$level] ?? 0) + 1;
            
            // Count by hour (last 24 hours)
            $hourKey = $timestamp->format('H:00');
            $stats['by_hour'][$hourKey] = ($stats['by_hour'][$hourKey] ?? 0) + 1;
            
            // Count recent (last 24 hours)
            if ($timestamp->diffInHours($now) <= 24) {
                $stats['recent_24h']++;
            }
        }

        return $stats;
    }

    /**
     * Get system information
     */
    public static function getSystemInfo(): array
    {
        return [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'environment' => config('app.env'),
            'debug_mode' => config('app.debug'),
            'timezone' => config('app.timezone'),
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'disk_free_space' => self::formatBytes(disk_free_space(storage_path())),
            'log_file_size' => self::formatBytes(File::size(storage_path('logs/laravel.log'))),
            'last_error' => self::getLastError()
        ];
    }

    /**
     * Get the most recent error
     */
    private static function getLastError(): ?array
    {
        $errors = self::getRecentErrors(1, 'ERROR');
        return $errors[0] ?? null;
    }

    /**
     * Format bytes to human readable format
     */
    private static function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * Clear old logs
     */
    public static function clearOldLogs(int $daysToKeep = 7): bool
    {
        try {
            $logPath = storage_path('logs');
            $files = File::files($logPath);
            $cutoffDate = Carbon::now()->subDays($daysToKeep);
            
            foreach ($files as $file) {
                if (Carbon::createFromTimestamp($file->getMTime())->lt($cutoffDate)) {
                    File::delete($file->getPathname());
                }
            }
            
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Send error notification email
     */
    public static function sendErrorNotification(array $errorData): bool
    {
        try {
            $notificationEmail = env('DEBUG_NOTIFICATION_EMAIL');
            
            if (!$notificationEmail) {
                return false;
            }

            // Check rate limiting (max 1 email per 5 minutes for same error type)
            $cacheKey = 'error_notification_' . md5($errorData['message'] ?? '');
            $lastSent = cache($cacheKey);
            
            if ($lastSent && Carbon::parse($lastSent)->diffInMinutes(Carbon::now()) < 5) {
                return false; // Rate limited
            }

            \Mail::raw(self::formatErrorEmail($errorData), function ($message) use ($notificationEmail) {
                $message->to($notificationEmail)
                        ->subject('🚨 Error Crítico en ' . config('app.name'))
                        ->from(config('mail.from.address'), config('mail.from.name'));
            });

            // Update rate limiting cache
            cache([$cacheKey => Carbon::now()], Carbon::now()->addMinutes(10));
            
            return true;
        } catch (\Exception $e) {
            // Log the notification failure but don't throw
            \Log::error('Failed to send error notification', [
                'error' => $e->getMessage(),
                'original_error' => $errorData
            ]);
            return false;
        }
    }

    /**
     * Format error data for email
     */
    private static function formatErrorEmail(array $errorData): string
    {
        $url = request()->fullUrl() ?? 'N/A';
        $userAgent = request()->userAgent() ?? 'N/A';
        $ip = request()->ip() ?? 'N/A';
        
        return "🚨 ERROR CRÍTICO DETECTADO\n\n" .
               "Timestamp: " . ($errorData['timestamp'] ?? Carbon::now()) . "\n" .
               "Nivel: " . ($errorData['level'] ?? 'UNKNOWN') . "\n" .
               "URL: {$url}\n" .
               "IP: {$ip}\n" .
               "User Agent: {$userAgent}\n\n" .
               "MENSAJE:\n" . ($errorData['message'] ?? 'No message') . "\n\n" .
               "STACK TRACE:\n" . ($errorData['stack_trace'] ?? 'No stack trace') . "\n\n" .
               "---\n" .
               "Sistema: " . config('app.name') . "\n" .
               "Entorno: " . config('app.env') . "\n" .
               "Acceso directo: " . url('/system-debug');
    }
}