<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Features\SuperLinkiu\Services\ErrorMonitoringService;
use App\Features\SuperLinkiu\Services\PerformanceMonitoringService;

class MonitorWizardErrors extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wizard:monitor-errors 
                            {--test : Run in test mode}
                            {--severity=info : Test alert severity (info|warning|critical)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitor wizard errors and performance metrics, trigger alerts when thresholds are exceeded';

    protected ErrorMonitoringService $errorService;
    protected PerformanceMonitoringService $performanceService;

    public function __construct(
        ErrorMonitoringService $errorService,
        PerformanceMonitoringService $performanceService
    ) {
        parent::__construct();
        $this->errorService = $errorService;
        $this->performanceService = $performanceService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Starting wizard error monitoring...');
        
        if ($this->option('test')) {
            return $this->runTestMode();
        }
        
        try {
            // Monitor error rates and performance
            $this->errorService->monitorErrorRates();
            
            // Get system health status
            $healthStatus = $this->errorService->getSystemHealthStatus();
            
            $this->displayHealthStatus($healthStatus);
            
            // Display active alerts
            $activeAlerts = $this->errorService->getActiveAlerts();
            if (!empty($activeAlerts)) {
                $this->displayActiveAlerts($activeAlerts);
            } else {
                $this->info('✅ No active alerts');
            }
            
            $this->info('✅ Error monitoring completed successfully');
            
        } catch (\Exception $e) {
            $this->error('❌ Error monitoring failed: ' . $e->getMessage());
            $this->line('Stack trace:');
            $this->line($e->getTraceAsString());
            return 1;
        }
        
        return 0;
    }
    
    /**
     * Run in test mode
     */
    private function runTestMode(): int
    {
        $this->warn('🧪 Running in test mode');
        
        $severity = $this->option('severity');
        
        if (!in_array($severity, ['info', 'warning', 'critical'])) {
            $this->error('Invalid severity. Use: info, warning, or critical');
            return 1;
        }
        
        $this->info("Triggering test alert with severity: {$severity}");
        
        try {
            $this->errorService->testAlert($severity);
            $this->info('✅ Test alert triggered successfully');
            
            // Show what would happen
            $this->line('');
            $this->info('Test alert details:');
            $this->table(
                ['Property', 'Value'],
                [
                    ['Severity', $severity],
                    ['Type', 'test_alert'],
                    ['Triggered by', auth()->user()->email ?? 'console'],
                    ['Timestamp', now()->toISOString()]
                ]
            );
            
            if ($severity === 'critical') {
                $this->warn('📧 Email notification would be sent to administrators');
            }
            
            $this->info('💬 Slack notification would be sent (if configured)');
            $this->info('💾 Alert would be stored in database');
            
        } catch (\Exception $e) {
            $this->error('❌ Test alert failed: ' . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
    
    /**
     * Display system health status
     */
    private function displayHealthStatus(array $status): void
    {
        $this->line('');
        $this->info('🏥 System Health Status');
        
        $statusIcon = match($status['status']) {
            'healthy' => '✅',
            'degraded' => '⚠️',
            'warning' => '🟡',
            'critical' => '🔴',
            default => '❓'
        };
        
        $statusColor = match($status['status']) {
            'healthy' => 'info',
            'degraded' => 'comment',
            'warning' => 'warn',
            'critical' => 'error',
            default => 'line'
        };
        
        $this->{$statusColor}("{$statusIcon} Status: " . strtoupper($status['status']));
        
        $this->table(
            ['Metric', 'Count'],
            [
                ['Active Alerts', $status['active_alerts']],
                ['Critical Alerts', $status['critical_alerts']],
                ['Warning Alerts', $status['warning_alerts']],
                ['Last Check', $status['last_check']]
            ]
        );
    }
    
    /**
     * Display active alerts
     */
    private function displayActiveAlerts(array $alerts): void
    {
        $this->line('');
        $this->warn('⚠️ Active Alerts');
        
        $tableData = [];
        foreach ($alerts as $alert) {
            $severityIcon = match($alert['severity']) {
                'critical' => '🔴',
                'warning' => '🟡',
                'info' => '🔵',
                default => '⚪'
            };
            
            $tableData[] = [
                $alert['id'],
                $severityIcon . ' ' . strtoupper($alert['severity']),
                ucwords(str_replace('_', ' ', $alert['type'])),
                $alert['message'],
                $alert['timestamp']
            ];
        }
        
        $this->table(
            ['ID', 'Severity', 'Type', 'Message', 'Timestamp'],
            $tableData
        );
        
        if ($this->confirm('Would you like to see alert resolution options?')) {
            $this->showAlertResolutionOptions($alerts);
        }
    }
    
    /**
     * Show alert resolution options
     */
    private function showAlertResolutionOptions(array $alerts): void
    {
        $this->line('');
        $this->info('🔧 Alert Resolution Options');
        
        $choices = [
            'view' => 'View alert details',
            'resolve' => 'Mark alert as resolved',
            'thresholds' => 'Adjust alert thresholds',
            'test' => 'Send test alert',
            'clear' => 'Clear alert cooldowns',
            'exit' => 'Exit'
        ];
        
        $choice = $this->choice('What would you like to do?', array_values($choices));
        
        switch (array_search($choice, $choices)) {
            case 'view':
                $this->viewAlertDetails($alerts);
                break;
                
            case 'resolve':
                $this->resolveAlert($alerts);
                break;
                
            case 'thresholds':
                $this->adjustThresholds();
                break;
                
            case 'test':
                $severity = $this->choice('Test alert severity?', ['info', 'warning', 'critical']);
                $this->errorService->testAlert($severity);
                $this->info('✅ Test alert sent');
                break;
                
            case 'clear':
                $this->errorService->clearCooldowns();
                $this->info('✅ Alert cooldowns cleared');
                break;
                
            case 'exit':
                return;
        }
    }
    
    /**
     * View alert details
     */
    private function viewAlertDetails(array $alerts): void
    {
        $alertIds = array_column($alerts, 'id');
        $selectedId = $this->choice('Select alert ID to view:', $alertIds);
        
        $alert = collect($alerts)->first(fn($a) => $a['id'] == $selectedId);
        
        if ($alert) {
            $this->line('');
            $this->info("Alert Details - ID: {$alert['id']}");
            $this->table(
                ['Property', 'Value'],
                [
                    ['Severity', strtoupper($alert['severity'])],
                    ['Type', ucwords(str_replace('_', ' ', $alert['type']))],
                    ['Message', $alert['message']],
                    ['Timestamp', $alert['timestamp']],
                    ['Resolved', $alert['resolved'] ? 'Yes' : 'No']
                ]
            );
        }
    }
    
    /**
     * Resolve an alert
     */
    private function resolveAlert(array $alerts): void
    {
        $unresolvedAlerts = array_filter($alerts, fn($a) => !$a['resolved']);
        
        if (empty($unresolvedAlerts)) {
            $this->info('No unresolved alerts to resolve');
            return;
        }
        
        $alertIds = array_column($unresolvedAlerts, 'id');
        $selectedId = $this->choice('Select alert ID to resolve:', $alertIds);
        
        if ($this->confirm("Are you sure you want to resolve alert ID {$selectedId}?")) {
            $this->errorService->resolveAlert($selectedId);
            $this->info("✅ Alert ID {$selectedId} has been resolved");
        }
    }
    
    /**
     * Adjust alert thresholds
     */
    private function adjustThresholds(): void
    {
        $this->line('');
        $this->info('🎛️ Adjust Alert Thresholds');
        
        $thresholds = [
            'error_rate' => 'Error Rate (%)',
            'response_time' => 'Response Time (ms)',
            'error_count' => 'Error Count (per hour)',
            'cache_miss_rate' => 'Cache Miss Rate (%)',
            'validation_failures' => 'Validation Failures (per hour)'
        ];
        
        $metric = $this->choice('Select metric to adjust:', array_values($thresholds));
        $metricKey = array_search($metric, $thresholds);
        
        $currentValue = $this->errorService->getThreshold($metricKey);
        $this->info("Current threshold for {$metric}: {$currentValue}");
        
        $newValue = $this->ask("Enter new threshold value:");
        
        if (is_numeric($newValue) && $newValue > 0) {
            $this->errorService->setThreshold($metricKey, (float)$newValue);
            $this->info("✅ Threshold for {$metric} updated to {$newValue}");
        } else {
            $this->error('Invalid threshold value. Must be a positive number.');
        }
    }
}
