<?php

namespace App\Features\SuperLinkiu\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

/**
 * Service for monitoring wizard performance and usage analytics
 * Requirements: System monitoring
 */
class PerformanceMonitoringService
{
    private const METRICS_PREFIX = 'metrics:';
    private const DAILY_TTL = 86400; // 24 hours
    private const HOURLY_TTL = 3600; // 1 hour

    /**
     * Record validation endpoint performance
     */
    public function recordValidationPerformance(string $endpoint, float $responseTime, bool $cacheHit = false): void
    {
        $timestamp = now();
        $hour = $timestamp->format('Y-m-d-H');
        $day = $timestamp->format('Y-m-d');

        // Record hourly metrics
        $hourlyKey = $this->getMetricKey('validation', $endpoint, $hour);
        $hourlyData = Cache::get($hourlyKey, [
            'total_requests' => 0,
            'total_response_time' => 0,
            'cache_hits' => 0,
            'cache_misses' => 0,
            'min_response_time' => null,
            'max_response_time' => null
        ]);

        $hourlyData['total_requests']++;
        $hourlyData['total_response_time'] += $responseTime;
        
        if ($cacheHit) {
            $hourlyData['cache_hits']++;
        } else {
            $hourlyData['cache_misses']++;
        }

        $hourlyData['min_response_time'] = $hourlyData['min_response_time'] === null 
            ? $responseTime 
            : min($hourlyData['min_response_time'], $responseTime);
        
        $hourlyData['max_response_time'] = $hourlyData['max_response_time'] === null 
            ? $responseTime 
            : max($hourlyData['max_response_time'], $responseTime);

        Cache::put($hourlyKey, $hourlyData, self::HOURLY_TTL);

        // Record daily aggregates
        $dailyKey = $this->getMetricKey('validation_daily', $endpoint, $day);
        $dailyData = Cache::get($dailyKey, [
            'total_requests' => 0,
            'total_response_time' => 0,
            'cache_hits' => 0,
            'cache_misses' => 0
        ]);

        $dailyData['total_requests']++;
        $dailyData['total_response_time'] += $responseTime;
        
        if ($cacheHit) {
            $dailyData['cache_hits']++;
        } else {
            $dailyData['cache_misses']++;
        }

        Cache::put($dailyKey, $dailyData, self::DAILY_TTL);

        // Log performance issues
        if ($responseTime > 1000) { // > 1 second
            Log::warning('Slow validation endpoint response', [
                'endpoint' => $endpoint,
                'response_time_ms' => $responseTime,
                'cache_hit' => $cacheHit,
                'timestamp' => $timestamp->toISOString()
            ]);
        }
    }

    /**
     * Record wizard usage analytics
     */
    public function recordWizardUsage(string $action, array $context = []): void
    {
        $timestamp = now();
        $day = $timestamp->format('Y-m-d');
        $hour = $timestamp->format('Y-m-d-H');

        // Record action counts
        $actionKey = $this->getMetricKey('wizard_actions', $action, $day);
        $currentCount = Cache::get($actionKey, 0);
        Cache::put($actionKey, $currentCount + 1, self::DAILY_TTL);

        // Record hourly distribution
        $hourlyKey = $this->getMetricKey('wizard_hourly', $action, $hour);
        $hourlyCount = Cache::get($hourlyKey, 0);
        Cache::put($hourlyKey, $hourlyCount + 1, self::HOURLY_TTL);

        // Log detailed context for analysis
        Log::info('Wizard usage recorded', [
            'action' => $action,
            'context' => $context,
            'timestamp' => $timestamp->toISOString(),
            'user_id' => auth()->id()
        ]);
    }

    /**
     * Record wizard step completion time
     */
    public function recordStepCompletionTime(string $stepName, float $timeSpent): void
    {
        $day = now()->format('Y-m-d');
        $key = $this->getMetricKey('step_times', $stepName, $day);
        
        $stepData = Cache::get($key, [
            'total_completions' => 0,
            'total_time' => 0,
            'min_time' => null,
            'max_time' => null
        ]);

        $stepData['total_completions']++;
        $stepData['total_time'] += $timeSpent;
        $stepData['min_time'] = $stepData['min_time'] === null 
            ? $timeSpent 
            : min($stepData['min_time'], $timeSpent);
        $stepData['max_time'] = $stepData['max_time'] === null 
            ? $timeSpent 
            : max($stepData['max_time'], $timeSpent);

        Cache::put($key, $stepData, self::DAILY_TTL);

        // Alert on unusually long completion times
        if ($timeSpent > 300000) { // > 5 minutes
            Log::warning('Long wizard step completion time', [
                'step' => $stepName,
                'time_spent_ms' => $timeSpent,
                'user_id' => auth()->id()
            ]);
        }
    }

    /**
     * Record error occurrence
     */
    public function recordError(string $errorType, string $context, array $details = []): void
    {
        $day = now()->format('Y-m-d');
        $hour = now()->format('Y-m-d-H');

        // Daily error count
        $dailyKey = $this->getMetricKey('errors', $errorType, $day);
        $dailyCount = Cache::get($dailyKey, 0);
        Cache::put($dailyKey, $dailyCount + 1, self::DAILY_TTL);

        // Hourly error count
        $hourlyKey = $this->getMetricKey('errors_hourly', $errorType, $hour);
        $hourlyCount = Cache::get($hourlyKey, 0);
        Cache::put($hourlyKey, $hourlyCount + 1, self::HOURLY_TTL);

        // Log error details
        Log::error('Wizard error recorded', [
            'error_type' => $errorType,
            'context' => $context,
            'details' => $details,
            'user_id' => auth()->id(),
            'timestamp' => now()->toISOString()
        ]);

        // Alert on error spikes
        if ($hourlyCount > 10) {
            Log::alert('High error rate detected', [
                'error_type' => $errorType,
                'hourly_count' => $hourlyCount,
                'hour' => $hour
            ]);
        }
    }

    /**
     * Get validation performance metrics
     */
    public function getValidationMetrics(string $endpoint, string $period = 'today'): array
    {
        $date = $period === 'today' ? now()->format('Y-m-d') : $period;
        
        if (strlen($date) === 10) { // Daily metrics
            $key = $this->getMetricKey('validation_daily', $endpoint, $date);
        } else { // Hourly metrics
            $key = $this->getMetricKey('validation', $endpoint, $date);
        }

        $data = Cache::get($key, []);
        
        if (!empty($data) && $data['total_requests'] > 0) {
            $data['avg_response_time'] = $data['total_response_time'] / $data['total_requests'];
            $data['cache_hit_rate'] = ($data['cache_hits'] / $data['total_requests']) * 100;
        }

        return $data;
    }

    /**
     * Get wizard usage analytics
     */
    public function getWizardAnalytics(string $period = 'today'): array
    {
        $date = $period === 'today' ? now()->format('Y-m-d') : $period;
        
        $actions = ['wizard_started', 'step_completed', 'wizard_completed', 'wizard_abandoned'];
        $analytics = [];

        foreach ($actions as $action) {
            $key = $this->getMetricKey('wizard_actions', $action, $date);
            $analytics[$action] = Cache::get($key, 0);
        }

        // Calculate completion rate
        if ($analytics['wizard_started'] > 0) {
            $analytics['completion_rate'] = ($analytics['wizard_completed'] / $analytics['wizard_started']) * 100;
            $analytics['abandonment_rate'] = ($analytics['wizard_abandoned'] / $analytics['wizard_started']) * 100;
        } else {
            $analytics['completion_rate'] = 0;
            $analytics['abandonment_rate'] = 0;
        }

        return $analytics;
    }

    /**
     * Get step performance metrics
     */
    public function getStepMetrics(string $stepName, string $period = 'today'): array
    {
        $date = $period === 'today' ? now()->format('Y-m-d') : $period;
        $key = $this->getMetricKey('step_times', $stepName, $date);
        
        $data = Cache::get($key, []);
        
        if (!empty($data) && $data['total_completions'] > 0) {
            $data['avg_completion_time'] = $data['total_time'] / $data['total_completions'];
        }

        return $data;
    }

    /**
     * Get error statistics
     */
    public function getErrorStatistics(string $period = 'today'): array
    {
        $date = $period === 'today' ? now()->format('Y-m-d') : $period;
        
        $errorTypes = ['validation_error', 'network_error', 'server_error', 'client_error'];
        $statistics = [];

        foreach ($errorTypes as $errorType) {
            $key = $this->getMetricKey('errors', $errorType, $date);
            $statistics[$errorType] = Cache::get($key, 0);
        }

        $statistics['total_errors'] = array_sum($statistics);
        
        return $statistics;
    }

    /**
     * Get database query performance metrics
     */
    public function getDatabaseMetrics(): array
    {
        // Get query log if enabled
        $queries = DB::getQueryLog();
        
        if (empty($queries)) {
            return [
                'total_queries' => 0,
                'avg_query_time' => 0,
                'slow_queries' => 0,
                'note' => 'Query logging not enabled'
            ];
        }

        $totalTime = 0;
        $slowQueries = 0;
        
        foreach ($queries as $query) {
            $totalTime += $query['time'];
            if ($query['time'] > 100) { // > 100ms
                $slowQueries++;
            }
        }

        return [
            'total_queries' => count($queries),
            'avg_query_time' => count($queries) > 0 ? $totalTime / count($queries) : 0,
            'total_query_time' => $totalTime,
            'slow_queries' => $slowQueries,
            'slow_query_percentage' => count($queries) > 0 ? ($slowQueries / count($queries)) * 100 : 0
        ];
    }

    /**
     * Generate performance report
     */
    public function generatePerformanceReport(string $period = 'today'): array
    {
        $endpoints = ['validateEmail', 'validateSlug', 'suggestSlug', 'calculateBilling'];
        $report = [
            'period' => $period,
            'generated_at' => now()->toISOString(),
            'validation_endpoints' => [],
            'wizard_analytics' => $this->getWizardAnalytics($period),
            'error_statistics' => $this->getErrorStatistics($period),
            'database_metrics' => $this->getDatabaseMetrics()
        ];

        foreach ($endpoints as $endpoint) {
            $report['validation_endpoints'][$endpoint] = $this->getValidationMetrics($endpoint, $period);
        }

        return $report;
    }

    /**
     * Clear old metrics (for maintenance)
     */
    public function clearOldMetrics(int $daysToKeep = 7): void
    {
        $cutoffDate = now()->subDays($daysToKeep);
        
        Log::info('Clearing old performance metrics', [
            'cutoff_date' => $cutoffDate->toDateString(),
            'days_to_keep' => $daysToKeep
        ]);

        // This would require implementing a way to enumerate and delete old cache keys
        // For now, we'll just log the maintenance request
    }

    /**
     * Generate cache key for metrics
     */
    private function getMetricKey(string $type, string $identifier, string $period): string
    {
        return self::METRICS_PREFIX . "{$type}:{$identifier}:{$period}";
    }
}