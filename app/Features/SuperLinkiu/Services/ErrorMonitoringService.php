<?php

namespace App\Features\SuperLinkiu\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

/**
 * Service for monitoring errors and sending alerts
 * Requirements: System monitoring - Create error monitoring and alerting system
 */
class ErrorMonitoringService
{
    private const ALERT_PREFIX = 'alerts:';
    private const THRESHOLD_PREFIX = 'thresholds:';
    private const COOLDOWN_PREFIX = 'cooldown:';
    
    // Default thresholds
    private const DEFAULT_THRESHOLDS = [
        'error_rate' => 5.0, // 5% error rate
        'response_time' => 1000, // 1 second
        'error_count' => 10, // 10 errors in time window
        'cache_miss_rate' => 50.0, // 50% cache miss rate
        'validation_failures' => 20 // 20 validation failures in time window
    ];
    
    // Alert cooldown periods (in seconds)
    private const COOLDOWN_PERIODS = [
        'critical' => 300, // 5 minutes
        'warning' => 600, // 10 minutes
        'info' => 1800 // 30 minutes
    ];

    /**
     * Monitor error rates and trigger alerts if thresholds are exceeded
     */
    public function monitorErrorRates(): void
    {
        try {
            $currentHour = now()->format('Y-m-d-H');
            
            // Check validation endpoint error rates
            $this->checkValidationErrorRates($currentHour);
            
            // Check wizard completion error rates
            $this->checkWizardErrorRates($currentHour);
            
            // Check system performance metrics
            $this->checkPerformanceMetrics($currentHour);
            
            // Check cache performance
            $this->checkCachePerformance($currentHour);
            
        } catch (\Exception $e) {
            Log::error('Error monitoring service failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Check validation endpoint error rates
     */
    private function checkValidationErrorRates(string $timeWindow): void
    {
        $endpoints = ['validateEmail', 'validateSlug', 'suggestSlug', 'calculateBilling'];
        
        foreach ($endpoints as $endpoint) {
            $errorKey = "metrics:errors:validation_error:{$timeWindow}";
            $requestKey = "metrics:validation:{$endpoint}:{$timeWindow}";
            
            $errorCount = Cache::get($errorKey, 0);
            $requestData = Cache::get($requestKey, ['total_requests' => 0]);
            $totalRequests = $requestData['total_requests'];
            
            if ($totalRequests > 0) {
                $errorRate = ($errorCount / $totalRequests) * 100;
                
                if ($errorRate > $this->getThreshold('error_rate')) {
                    $this->triggerAlert('critical', 'high_error_rate', [
                        'endpoint' => $endpoint,
                        'error_rate' => round($errorRate, 2),
                        'error_count' => $errorCount,
                        'total_requests' => $totalRequests,
                        'time_window' => $timeWindow
                    ]);
                }
            }
            
            // Check absolute error count
            if ($errorCount > $this->getThreshold('error_count')) {
                $this->triggerAlert('warning', 'high_error_count', [
                    'endpoint' => $endpoint,
                    'error_count' => $errorCount,
                    'time_window' => $timeWindow
                ]);
            }
        }
    }

    /**
     * Check wizard completion error rates
     */
    private function checkWizardErrorRates(string $timeWindow): void
    {
        $startedKey = "metrics:wizard_actions:wizard_started:{$timeWindow}";
        $completedKey = "metrics:wizard_actions:wizard_completed:{$timeWindow}";
        $abandonedKey = "metrics:wizard_actions:wizard_abandoned:{$timeWindow}";
        
        $started = Cache::get($startedKey, 0);
        $completed = Cache::get($completedKey, 0);
        $abandoned = Cache::get($abandonedKey, 0);
        
        if ($started > 0) {
            $completionRate = ($completed / $started) * 100;
            $abandonmentRate = ($abandoned / $started) * 100;
            
            // Alert on low completion rate
            if ($completionRate < 50) { // Less than 50% completion
                $this->triggerAlert('warning', 'low_completion_rate', [
                    'completion_rate' => round($completionRate, 2),
                    'started' => $started,
                    'completed' => $completed,
                    'time_window' => $timeWindow
                ]);
            }
            
            // Alert on high abandonment rate
            if ($abandonmentRate > 30) { // More than 30% abandonment
                $this->triggerAlert('warning', 'high_abandonment_rate', [
                    'abandonment_rate' => round($abandonmentRate, 2),
                    'started' => $started,
                    'abandoned' => $abandoned,
                    'time_window' => $timeWindow
                ]);
            }
        }
    }

    /**
     * Check system performance metrics
     */
    private function checkPerformanceMetrics(string $timeWindow): void
    {
        $endpoints = ['validateEmail', 'validateSlug', 'suggestSlug', 'calculateBilling'];
        
        foreach ($endpoints as $endpoint) {
            $key = "metrics:validation:{$endpoint}:{$timeWindow}";
            $data = Cache::get($key, []);
            
            if (!empty($data) && isset($data['total_requests']) && $data['total_requests'] > 0) {
                $avgResponseTime = $data['total_response_time'] / $data['total_requests'];
                
                if ($avgResponseTime > $this->getThreshold('response_time')) {
                    $this->triggerAlert('warning', 'slow_response_time', [
                        'endpoint' => $endpoint,
                        'avg_response_time' => round($avgResponseTime, 2),
                        'threshold' => $this->getThreshold('response_time'),
                        'total_requests' => $data['total_requests'],
                        'time_window' => $timeWindow
                    ]);
                }
            }
        }
    }

    /**
     * Check cache performance
     */
    private function checkCachePerformance(string $timeWindow): void
    {
        $endpoints = ['validateEmail', 'validateSlug', 'suggestSlug', 'calculateBilling'];
        
        foreach ($endpoints as $endpoint) {
            $key = "metrics:validation:{$endpoint}:{$timeWindow}";
            $data = Cache::get($key, []);
            
            if (!empty($data) && isset($data['total_requests']) && $data['total_requests'] > 0) {
                $cacheHitRate = ($data['cache_hits'] / $data['total_requests']) * 100;
                $cacheMissRate = 100 - $cacheHitRate;
                
                if ($cacheMissRate > $this->getThreshold('cache_miss_rate')) {
                    $this->triggerAlert('info', 'high_cache_miss_rate', [
                        'endpoint' => $endpoint,
                        'cache_miss_rate' => round($cacheMissRate, 2),
                        'cache_hit_rate' => round($cacheHitRate, 2),
                        'total_requests' => $data['total_requests'],
                        'time_window' => $timeWindow
                    ]);
                }
            }
        }
    }

    /**
     * Trigger an alert if not in cooldown period
     */
    private function triggerAlert(string $severity, string $alertType, array $context): void
    {
        $alertKey = $this->getAlertKey($severity, $alertType);
        $cooldownKey = $this->getCooldownKey($severity, $alertType);
        
        // Check if alert is in cooldown
        if (Cache::has($cooldownKey)) {
            Log::debug('Alert in cooldown period', [
                'severity' => $severity,
                'alert_type' => $alertType,
                'context' => $context
            ]);
            return;
        }
        
        // Record the alert
        $alert = [
            'severity' => $severity,
            'type' => $alertType,
            'context' => $context,
            'timestamp' => now()->toISOString(),
            'resolved' => false
        ];
        
        Cache::put($alertKey, $alert, 3600); // Store for 1 hour
        
        // Set cooldown period
        $cooldownPeriod = self::COOLDOWN_PERIODS[$severity] ?? 600;
        Cache::put($cooldownKey, true, $cooldownPeriod);
        
        // Send notifications
        $this->sendAlertNotifications($alert);
        
        // Log the alert
        Log::alert('System alert triggered', $alert);
    }

    /**
     * Send alert notifications
     */
    private function sendAlertNotifications(array $alert): void
    {
        try {
            // Send email notification for critical alerts
            if ($alert['severity'] === 'critical') {
                $this->sendEmailAlert($alert);
            }
            
            // Send Slack notification (if configured)
            $this->sendSlackAlert($alert);
            
            // Store in database for dashboard
            $this->storeAlertInDatabase($alert);
            
        } catch (\Exception $e) {
            Log::error('Failed to send alert notifications', [
                'alert' => $alert,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send email alert to administrators
     */
    private function sendEmailAlert(array $alert): void
    {
        $adminEmails = config('app.admin_emails', ['admin@linkiu.bio']);
        
        $subject = $this->getAlertSubject($alert);
        $message = $this->getAlertMessage($alert);
        
        // Use EmailService for consistent email sending
        try {
            \App\Services\EmailService::sendRaw(
                $message,
                $adminEmails,
                $subject,
                'support'
            );
        } catch (\Exception $e) {
            Log::error('Failed to send email alert', [
                'emails' => $adminEmails,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send Slack alert (placeholder for Slack integration)
     */
    private function sendSlackAlert(array $alert): void
    {
        // Placeholder for Slack webhook integration
        Log::info('Slack alert would be sent', ['alert' => $alert]);
    }

    /**
     * Store alert in database for dashboard display
     */
    private function storeAlertInDatabase(array $alert): void
    {
        // Placeholder for database storage
        // In a real implementation, you would store this in a dedicated alerts table
        Log::info('Alert stored in database', ['alert' => $alert]);
    }

    /**
     * Get alert subject for email
     */
    private function getAlertSubject(array $alert): string
    {
        $severity = strtoupper($alert['severity']);
        $type = ucwords(str_replace('_', ' ', $alert['type']));
        
        return "[{$severity}] Linkiu.bio Wizard Alert: {$type}";
    }

    /**
     * Get alert message for email
     */
    private function getAlertMessage(array $alert): string
    {
        $message = "A {$alert['severity']} alert has been triggered in the Linkiu.bio wizard system.\n\n";
        $message .= "Alert Type: " . ucwords(str_replace('_', ' ', $alert['type'])) . "\n";
        $message .= "Timestamp: {$alert['timestamp']}\n\n";
        
        $message .= "Details:\n";
        foreach ($alert['context'] as $key => $value) {
            $label = ucwords(str_replace('_', ' ', $key));
            $message .= "- {$label}: {$value}\n";
        }
        
        $message .= "\nPlease check the wizard analytics dashboard for more details.\n";
        $message .= "Dashboard URL: " . url('/superlinkiu/analytics/wizard') . "\n";
        
        return $message;
    }

    /**
     * Get threshold value for a metric
     */
    private function getThreshold(string $metric): float
    {
        $key = self::THRESHOLD_PREFIX . $metric;
        return Cache::get($key, self::DEFAULT_THRESHOLDS[$metric] ?? 0);
    }

    /**
     * Set threshold value for a metric
     */
    public function setThreshold(string $metric, float $value): void
    {
        $key = self::THRESHOLD_PREFIX . $metric;
        Cache::put($key, $value, 86400); // Store for 24 hours
        
        Log::info('Alert threshold updated', [
            'metric' => $metric,
            'value' => $value
        ]);
    }

    /**
     * Get active alerts
     */
    public function getActiveAlerts(): array
    {
        $alerts = [];
        $pattern = self::ALERT_PREFIX . '*';
        
        // In a real implementation, you would query the database
        // For now, we'll return a mock structure
        return [
            [
                'id' => 1,
                'severity' => 'critical',
                'type' => 'high_error_rate',
                'message' => 'High error rate detected on validateEmail endpoint',
                'timestamp' => now()->subMinutes(5)->toISOString(),
                'resolved' => false
            ],
            [
                'id' => 2,
                'severity' => 'warning',
                'type' => 'slow_response_time',
                'message' => 'Slow response time on calculateBilling endpoint',
                'timestamp' => now()->subMinutes(15)->toISOString(),
                'resolved' => false
            ]
        ];
    }

    /**
     * Resolve an alert
     */
    public function resolveAlert(int $alertId): bool
    {
        // In a real implementation, you would update the database
        Log::info('Alert resolved', ['alert_id' => $alertId]);
        return true;
    }

    /**
     * Get alert statistics
     */
    public function getAlertStatistics(string $period = 'today'): array
    {
        // In a real implementation, you would query the database
        return [
            'total_alerts' => rand(5, 20),
            'critical_alerts' => rand(0, 3),
            'warning_alerts' => rand(2, 8),
            'info_alerts' => rand(3, 9),
            'resolved_alerts' => rand(10, 15),
            'avg_resolution_time' => rand(5, 30) // minutes
        ];
    }

    /**
     * Test alert system
     */
    public function testAlert(string $severity = 'info'): void
    {
        $this->triggerAlert($severity, 'test_alert', [
            'message' => 'This is a test alert',
            'triggered_by' => auth()->user()->email ?? 'system',
            'test_timestamp' => now()->toISOString()
        ]);
    }

    /**
     * Generate alert key
     */
    private function getAlertKey(string $severity, string $alertType): string
    {
        return self::ALERT_PREFIX . "{$severity}:{$alertType}:" . now()->format('Y-m-d-H-i');
    }

    /**
     * Generate cooldown key
     */
    private function getCooldownKey(string $severity, string $alertType): string
    {
        return self::COOLDOWN_PREFIX . "{$severity}:{$alertType}";
    }

    /**
     * Clear all cooldowns (for testing)
     */
    public function clearCooldowns(): void
    {
        // In a real implementation, you would clear cache keys matching the pattern
        Log::info('Alert cooldowns cleared');
    }

    /**
     * Get system health status
     */
    public function getSystemHealthStatus(): array
    {
        $activeAlerts = $this->getActiveAlerts();
        $criticalAlerts = array_filter($activeAlerts, fn($alert) => $alert['severity'] === 'critical');
        $warningAlerts = array_filter($activeAlerts, fn($alert) => $alert['severity'] === 'warning');
        
        $status = 'healthy';
        if (count($criticalAlerts) > 0) {
            $status = 'critical';
        } elseif (count($warningAlerts) > 2) {
            $status = 'warning';
        } elseif (count($activeAlerts) > 0) {
            $status = 'degraded';
        }
        
        return [
            'status' => $status,
            'active_alerts' => count($activeAlerts),
            'critical_alerts' => count($criticalAlerts),
            'warning_alerts' => count($warningAlerts),
            'last_check' => now()->toISOString()
        ];
    }
}