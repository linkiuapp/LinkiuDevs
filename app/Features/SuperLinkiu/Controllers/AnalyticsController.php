<?php

namespace App\Features\SuperLinkiu\Controllers;

use App\Http\Controllers\Controller;
use App\Features\SuperLinkiu\Services\PerformanceMonitoringService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Controller for wizard analytics and monitoring dashboard
 * Requirements: System monitoring - Build wizard usage analytics dashboard
 */
class AnalyticsController extends Controller
{
    protected PerformanceMonitoringService $performanceService;

    public function __construct(PerformanceMonitoringService $performanceService)
    {
        $this->performanceService = $performanceService;
    }

    /**
     * Show the wizard analytics dashboard
     */
    public function wizardDashboard()
    {
        return view('superlinkiu::analytics.wizard-dashboard');
    }

    /**
     * Get wizard dashboard data
     */
    public function getWizardDashboardData(Request $request)
    {
        try {
            $period = $request->get('period', 'today');
            
            // Generate comprehensive performance report
            $report = $this->performanceService->generatePerformanceReport($period);
            
            // Format data for dashboard
            $dashboardData = [
                'overview' => $this->formatOverviewData($report),
                'performance' => $this->formatPerformanceData($report, $period),
                'endpoints' => $this->formatEndpointsData($report),
                'steps' => $this->formatStepData($period),
                'errors' => $this->formatErrorData($report),
                'realtime' => $this->getRealTimeData()
            ];

            return response()->json([
                'success' => true,
                'data' => $dashboardData
            ]);

        } catch (\Exception $e) {
            Log::error('Error loading wizard dashboard data', [
                'period' => $request->get('period'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error loading dashboard data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get real-time wizard data
     */
    public function getRealTimeWizardData()
    {
        try {
            $realTimeData = $this->getRealTimeData();

            return response()->json([
                'success' => true,
                'data' => $realTimeData
            ]);

        } catch (\Exception $e) {
            Log::error('Error loading real-time wizard data', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error loading real-time data'
            ], 500);
        }
    }

    /**
     * Export wizard analytics data
     */
    public function exportWizardData(Request $request)
    {
        try {
            $period = $request->get('period', 'today');
            $format = $request->get('format', 'xlsx');
            
            $report = $this->performanceService->generatePerformanceReport($period);
            
            // Create export data
            $exportData = $this->prepareExportData($report, $period);
            
            $filename = "wizard-analytics-{$period}-" . date('Y-m-d');
            
            if ($format === 'csv') {
                return Excel::download(new WizardAnalyticsExport($exportData), "{$filename}.csv");
            } else {
                return Excel::download(new WizardAnalyticsExport($exportData), "{$filename}.xlsx");
            }

        } catch (\Exception $e) {
            Log::error('Error exporting wizard data', [
                'period' => $request->get('period'),
                'format' => $request->get('format'),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error exporting data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get performance metrics for specific endpoint
     */
    public function getEndpointMetrics(Request $request, string $endpoint)
    {
        try {
            $period = $request->get('period', 'today');
            $metrics = $this->performanceService->getValidationMetrics($endpoint, $period);

            return response()->json([
                'success' => true,
                'data' => $metrics
            ]);

        } catch (\Exception $e) {
            Log::error('Error loading endpoint metrics', [
                'endpoint' => $endpoint,
                'period' => $request->get('period'),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error loading endpoint metrics'
            ], 500);
        }
    }

    /**
     * Get wizard step analytics
     */
    public function getStepAnalytics(Request $request)
    {
        try {
            $period = $request->get('period', 'today');
            $stepData = $this->formatStepData($period);

            return response()->json([
                'success' => true,
                'data' => $stepData
            ]);

        } catch (\Exception $e) {
            Log::error('Error loading step analytics', [
                'period' => $request->get('period'),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error loading step analytics'
            ], 500);
        }
    }

    /**
     * Format overview data for dashboard cards
     */
    private function formatOverviewData(array $report): array
    {
        $validationEndpoints = $report['validation_endpoints'];
        $wizardAnalytics = $report['wizard_analytics'];
        $errorStats = $report['error_statistics'];

        // Calculate average response time across all endpoints
        $totalRequests = 0;
        $totalResponseTime = 0;
        $totalCacheHits = 0;

        foreach ($validationEndpoints as $endpoint => $data) {
            if (!empty($data) && isset($data['total_requests'])) {
                $totalRequests += $data['total_requests'];
                $totalResponseTime += $data['total_response_time'] ?? 0;
                $totalCacheHits += $data['cache_hits'] ?? 0;
            }
        }

        $avgResponseTime = $totalRequests > 0 ? round($totalResponseTime / $totalRequests, 2) : 0;
        $cacheHitRate = $totalRequests > 0 ? round(($totalCacheHits / $totalRequests) * 100, 1) : 0;
        $completionRate = $wizardAnalytics['completion_rate'] ?? 0;
        $errorRate = $totalRequests > 0 ? round(($errorStats['total_errors'] / $totalRequests) * 100, 2) : 0;

        return [
            'avgResponseTime' => $avgResponseTime,
            'cacheHitRate' => $cacheHitRate,
            'completionRate' => round($completionRate, 1),
            'errorRate' => $errorRate,
            // Mock change indicators (in real implementation, compare with previous period)
            'responseTimeChange' => rand(-10, 10),
            'cacheHitChange' => rand(-5, 15),
            'completionChange' => rand(-8, 12),
            'errorChange' => rand(-20, 5)
        ];
    }

    /**
     * Format performance data for charts
     */
    private function formatPerformanceData(array $report, string $period): array
    {
        // Generate hourly data for today, or daily data for longer periods
        $labels = [];
        $responseTimes = [];
        $cacheHitRates = [];

        if ($period === 'today') {
            // Generate hourly data for today
            for ($hour = 0; $hour < 24; $hour++) {
                $labels[] = sprintf('%02d:00', $hour);
                
                // Mock data - in real implementation, fetch from cache/database
                $responseTimes[] = rand(50, 200);
                $cacheHitRates[] = rand(60, 95);
            }
        } else {
            // Generate daily data for longer periods
            $days = match($period) {
                'week' => 7,
                'month' => 30,
                default => 7
            };

            for ($i = $days - 1; $i >= 0; $i--) {
                $date = now()->subDays($i);
                $labels[] = $date->format('M j');
                
                // Mock data - in real implementation, fetch from cache/database
                $responseTimes[] = rand(80, 250);
                $cacheHitRates[] = rand(70, 90);
            }
        }

        return [
            'labels' => $labels,
            'responseTimes' => $responseTimes,
            'cacheHitRates' => $cacheHitRates
        ];
    }

    /**
     * Format endpoints data for pie chart
     */
    private function formatEndpointsData(array $report): array
    {
        $validationEndpoints = $report['validation_endpoints'];
        $data = [];

        foreach (['validateEmail', 'validateSlug', 'suggestSlug', 'calculateBilling'] as $endpoint) {
            $endpointData = $validationEndpoints[$endpoint] ?? [];
            $data[] = $endpointData['total_requests'] ?? 0;
        }

        return [
            'data' => $data
        ];
    }

    /**
     * Format step analytics data
     */
    private function formatStepData(string $period): array
    {
        $steps = [
            'Template Selection',
            'Owner Information',
            'Store Configuration',
            'Fiscal Information',
            'SEO Settings',
            'Review & Confirm'
        ];

        $stepData = [];
        foreach ($steps as $step) {
            $stepData[] = [
                'name' => $step,
                'completions' => rand(50, 200),
                'avgTime' => rand(30, 180),
                'dropRate' => rand(5, 25)
            ];
        }

        return $stepData;
    }

    /**
     * Format error analytics data
     */
    private function formatErrorData(array $report): array
    {
        $errorStats = $report['error_statistics'];
        $errorData = [];

        foreach ($errorStats as $type => $count) {
            if ($type !== 'total_errors') {
                $errorData[] = [
                    'type' => ucfirst(str_replace('_', ' ', $type)),
                    'count' => $count,
                    'rate' => $count > 0 ? rand(1, 10) : 0,
                    'trend' => rand(-50, 20)
                ];
            }
        }

        return $errorData;
    }

    /**
     * Get real-time monitoring data
     */
    private function getRealTimeData(): array
    {
        // In a real implementation, this would fetch from:
        // - Active sessions from session storage
        // - WebSocket connections
        // - Recent activity logs
        // - Cache statistics

        return [
            'currentUsers' => rand(5, 25),
            'activeWizards' => rand(2, 10),
            'pendingValidations' => rand(0, 5),
            'recentActivity' => [
                [
                    'type' => 'success',
                    'message' => 'Store "Mi Tienda Online" created successfully',
                    'time' => '2 minutes ago'
                ],
                [
                    'type' => 'warning',
                    'message' => 'Slow response time detected on validateSlug endpoint',
                    'time' => '5 minutes ago'
                ],
                [
                    'type' => 'success',
                    'message' => 'Cache hit rate improved to 85%',
                    'time' => '8 minutes ago'
                ],
                [
                    'type' => 'error',
                    'message' => 'Validation error in fiscal information step',
                    'time' => '12 minutes ago'
                ],
                [
                    'type' => 'success',
                    'message' => 'Template "Enterprise" loaded successfully',
                    'time' => '15 minutes ago'
                ]
            ]
        ];
    }

    /**
     * Prepare data for export
     */
    private function prepareExportData(array $report, string $period): array
    {
        return [
            'summary' => [
                'period' => $period,
                'generated_at' => $report['generated_at'],
                'total_requests' => array_sum(array_column($report['validation_endpoints'], 'total_requests')),
                'total_errors' => $report['error_statistics']['total_errors'],
                'wizard_completions' => $report['wizard_analytics']['wizard_completed']
            ],
            'validation_endpoints' => $report['validation_endpoints'],
            'wizard_analytics' => $report['wizard_analytics'],
            'error_statistics' => $report['error_statistics'],
            'database_metrics' => $report['database_metrics']
        ];
    }
}

/**
 * Export class for wizard analytics data
 */
class WizardAnalyticsExport implements \Maatwebsite\Excel\Concerns\FromArray
{
    protected array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        $export = [];
        
        // Summary sheet
        $export[] = ['Wizard Analytics Report'];
        $export[] = ['Period', $this->data['summary']['period']];
        $export[] = ['Generated At', $this->data['summary']['generated_at']];
        $export[] = ['Total Requests', $this->data['summary']['total_requests']];
        $export[] = ['Total Errors', $this->data['summary']['total_errors']];
        $export[] = ['Wizard Completions', $this->data['summary']['wizard_completions']];
        $export[] = [];
        
        // Validation endpoints
        $export[] = ['Validation Endpoints'];
        $export[] = ['Endpoint', 'Total Requests', 'Avg Response Time', 'Cache Hit Rate', 'Cache Misses'];
        
        foreach ($this->data['validation_endpoints'] as $endpoint => $data) {
            $export[] = [
                $endpoint,
                $data['total_requests'] ?? 0,
                isset($data['avg_response_time']) ? round($data['avg_response_time'], 2) : 0,
                isset($data['cache_hit_rate']) ? round($data['cache_hit_rate'], 2) : 0,
                $data['cache_misses'] ?? 0
            ];
        }
        
        $export[] = [];
        
        // Error statistics
        $export[] = ['Error Statistics'];
        $export[] = ['Error Type', 'Count'];
        
        foreach ($this->data['error_statistics'] as $type => $count) {
            $export[] = [ucfirst(str_replace('_', ' ', $type)), $count];
        }
        
        return $export;
    }
}