@extends('superlinkiu::layouts.app')

@section('title', 'Wizard Analytics Dashboard')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">📊 Wizard Analytics Dashboard</h1>
                    <p class="text-muted mb-0">Monitor wizard performance and user behavior</p>
                </div>
                <div class="d-flex gap-2">
                    <select id="periodSelector" class="form-select" style="width: auto;">
                        <option value="today">Today</option>
                        <option value="yesterday">Yesterday</option>
                        <option value="week">This Week</option>
                        <option value="month">This Month</option>
                    </select>
                    <button id="refreshBtn" class="btn btn-outline-primary">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                    <button id="exportBtn" class="btn btn-success">
                        <i class="fas fa-download"></i> Export
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Overview Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-primary bg-opacity-10 rounded-3 p-3">
                                <i class="fas fa-rocket text-primary fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Avg Response Time</h6>
                            <h4 class="mb-0" id="avgResponseTime">--</h4>
                            <small class="text-success" id="responseTimeChange">--</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-success bg-opacity-10 rounded-3 p-3">
                                <i class="fas fa-memory text-success fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Cache Hit Rate</h6>
                            <h4 class="mb-0" id="cacheHitRate">--</h4>
                            <small class="text-info" id="cacheHitChange">--</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-warning bg-opacity-10 rounded-3 p-3">
                                <i class="fas fa-chart-line text-warning fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Completion Rate</h6>
                            <h4 class="mb-0" id="completionRate">--</h4>
                            <small class="text-warning" id="completionChange">--</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-danger bg-opacity-10 rounded-3 p-3">
                                <i class="fas fa-exclamation-triangle text-danger fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Error Rate</h6>
                            <h4 class="mb-0" id="errorRate">--</h4>
                            <small class="text-danger" id="errorChange">--</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-accent border-0 pb-0">
                    <h5 class="card-title mb-0">📈 Performance Trends</h5>
                </div>
                <div class="card-body">
                    <canvas id="performanceChart" height="300"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-accent border-0 pb-0">
                    <h5 class="card-title mb-0">🎯 Validation Endpoints</h5>
                </div>
                <div class="card-body">
                    <canvas id="endpointsChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Analytics -->
    <div class="row mb-4">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-accent border-0 pb-0">
                    <h5 class="card-title mb-0">🚀 Wizard Flow Analytics</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Step</th>
                                    <th>Completions</th>
                                    <th>Avg Time</th>
                                    <th>Drop Rate</th>
                                </tr>
                            </thead>
                            <tbody id="stepAnalytics">
                                <tr>
                                    <td colspan="4" class="text-center text-muted">Loading...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-accent border-0 pb-0">
                    <h5 class="card-title mb-0">⚠️ Error Analysis</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Error Type</th>
                                    <th>Count</th>
                                    <th>Rate</th>
                                    <th>Trend</th>
                                </tr>
                            </thead>
                            <tbody id="errorAnalytics">
                                <tr>
                                    <td colspan="4" class="text-center text-muted">Loading...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Real-time Monitoring -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-accent border-0 pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">🔴 Real-time Activity</h5>
                        <div class="d-flex align-items-center">
                            <span class="badge bg-success me-2">
                                <i class="fas fa-circle pulse"></i> Live
                            </span>
                            <small class="text-muted">Last updated: <span id="lastUpdate">--</span></small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <h6 class="text-muted mb-3">Active Sessions</h6>
                            <div id="activeSessions" class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span>Current Users</span>
                                    <span class="badge bg-primary" id="currentUsers">0</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span>Active Wizards</span>
                                    <span class="badge bg-info" id="activeWizards">0</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>Pending Validations</span>
                                    <span class="badge bg-warning" id="pendingValidations">0</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <h6 class="text-muted mb-3">Recent Activity</h6>
                            <div id="recentActivity" style="max-height: 200px; overflow-y: auto;">
                                <div class="text-center text-muted py-3">
                                    <i class="fas fa-spinner fa-spin"></i> Loading activity...
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div id="loadingOverlay" class="position-fixed top-0 start-0 w-100 h-100 d-none" style="background: rgba(255,255,255,0.8); z-index: 9999;">
    <div class="d-flex justify-content-center align-items-center h-100">
        <div class="text-center">
            <div class="spinner-border text-primary mb-3" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="text-muted">Loading analytics data...</p>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.pulse {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.5; }
    100% { opacity: 1; }
}

.card {
    transition: transform 0.2s ease-in-out;
}

.card:hover {
    transform: translateY(-2px);
}

.table th {
    font-weight: 600;
    font-size: 0.875rem;
    color: #6c757d;
    border-bottom: 2px solid #dee2e6;
}

.badge {
    font-size: 0.75rem;
}

#recentActivity .activity-item {
    padding: 0.5rem;
    border-left: 3px solid #e9ecef;
    margin-bottom: 0.5rem;
    background: #f8f9fa;
    border-radius: 0.25rem;
}

#recentActivity .activity-item.success {
    border-left-color: #198754;
}

#recentActivity .activity-item.warning {
    border-left-color: #ffc107;
}

#recentActivity .activity-item.error {
    border-left-color: #dc3545;
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const dashboard = new WizardAnalyticsDashboard();
    dashboard.init();
});

class WizardAnalyticsDashboard {
    constructor() {
        this.charts = {};
        this.refreshInterval = null;
        this.currentPeriod = 'today';
        this.isLoading = false;
    }

    init() {
        this.setupEventListeners();
        this.initializeCharts();
        this.loadDashboardData();
        this.startRealTimeUpdates();
    }

    setupEventListeners() {
        // Period selector
        document.getElementById('periodSelector').addEventListener('change', (e) => {
            this.currentPeriod = e.target.value;
            this.loadDashboardData();
        });

        // Refresh button
        document.getElementById('refreshBtn').addEventListener('click', () => {
            this.loadDashboardData();
        });

        // Export button
        document.getElementById('exportBtn').addEventListener('click', () => {
            this.exportData();
        });
    }

    initializeCharts() {
        // Performance trends chart
        const performanceCtx = document.getElementById('performanceChart').getContext('2d');
        this.charts.performance = new Chart(performanceCtx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                    label: 'Response Time (ms)',
                    data: [],
                    borderColor: '#0d6efd',
                    backgroundColor: 'rgba(13, 110, 253, 0.1)',
                    tension: 0.4
                }, {
                    label: 'Cache Hit Rate (%)',
                    data: [],
                    borderColor: '#198754',
                    backgroundColor: 'rgba(25, 135, 84, 0.1)',
                    tension: 0.4,
                    yAxisID: 'y1'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Response Time (ms)'
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Cache Hit Rate (%)'
                        },
                        grid: {
                            drawOnChartArea: false,
                        },
                    }
                }
            }
        });

        // Endpoints chart
        const endpointsCtx = document.getElementById('endpointsChart').getContext('2d');
        this.charts.endpoints = new Chart(endpointsCtx, {
            type: 'doughnut',
            data: {
                labels: ['validateEmail', 'validateSlug', 'suggestSlug', 'calculateBilling'],
                datasets: [{
                    data: [],
                    backgroundColor: [
                        '#0d6efd',
                        '#198754',
                        '#ffc107',
                        '#dc3545'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }

    async loadDashboardData() {
        if (this.isLoading) return;
        
        this.isLoading = true;
        this.showLoading(true);

        try {
            const response = await fetch(`/api/analytics/wizard-dashboard?period=${this.currentPeriod}`, {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();
            
            if (data.success) {
                this.updateDashboard(data.data);
            } else {
                throw new Error(data.message || 'Failed to load dashboard data');
            }
        } catch (error) {
            console.error('Failed to load dashboard data:', error);
            this.showError('Failed to load dashboard data: ' + error.message);
        } finally {
            this.isLoading = false;
            this.showLoading(false);
        }
    }

    updateDashboard(data) {
        // Update overview cards
        this.updateOverviewCards(data.overview);
        
        // Update charts
        this.updatePerformanceChart(data.performance);
        this.updateEndpointsChart(data.endpoints);
        
        // Update tables
        this.updateStepAnalytics(data.steps);
        this.updateErrorAnalytics(data.errors);
        
        // Update real-time data
        this.updateRealTimeData(data.realtime);
        
        // Update last update time
        document.getElementById('lastUpdate').textContent = new Date().toLocaleTimeString();
    }

    updateOverviewCards(overview) {
        document.getElementById('avgResponseTime').textContent = overview.avgResponseTime + 'ms';
        document.getElementById('cacheHitRate').textContent = overview.cacheHitRate + '%';
        document.getElementById('completionRate').textContent = overview.completionRate + '%';
        document.getElementById('errorRate').textContent = overview.errorRate + '%';
        
        // Update change indicators
        this.updateChangeIndicator('responseTimeChange', overview.responseTimeChange);
        this.updateChangeIndicator('cacheHitChange', overview.cacheHitChange);
        this.updateChangeIndicator('completionChange', overview.completionChange);
        this.updateChangeIndicator('errorChange', overview.errorChange);
    }

    updateChangeIndicator(elementId, change) {
        const element = document.getElementById(elementId);
        const isPositive = change > 0;
        const icon = isPositive ? '↗' : '↘';
        const className = isPositive ? 'text-success' : 'text-danger';
        
        element.textContent = `${icon} ${Math.abs(change)}%`;
        element.className = className;
    }

    updatePerformanceChart(performance) {
        this.charts.performance.data.labels = performance.labels;
        this.charts.performance.data.datasets[0].data = performance.responseTimes;
        this.charts.performance.data.datasets[1].data = performance.cacheHitRates;
        this.charts.performance.update();
    }

    updateEndpointsChart(endpoints) {
        this.charts.endpoints.data.datasets[0].data = endpoints.data;
        this.charts.endpoints.update();
    }

    updateStepAnalytics(steps) {
        const tbody = document.getElementById('stepAnalytics');
        tbody.innerHTML = '';
        
        steps.forEach(step => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${step.name}</td>
                <td><span class="badge bg-primary">${step.completions}</span></td>
                <td>${step.avgTime}s</td>
                <td><span class="badge bg-${step.dropRate > 20 ? 'danger' : 'success'}">${step.dropRate}%</span></td>
            `;
            tbody.appendChild(row);
        });
    }

    updateErrorAnalytics(errors) {
        const tbody = document.getElementById('errorAnalytics');
        tbody.innerHTML = '';
        
        errors.forEach(error => {
            const row = document.createElement('tr');
            const trendIcon = error.trend > 0 ? '📈' : '📉';
            row.innerHTML = `
                <td>${error.type}</td>
                <td><span class="badge bg-danger">${error.count}</span></td>
                <td>${error.rate}%</td>
                <td>${trendIcon} ${Math.abs(error.trend)}%</td>
            `;
            tbody.appendChild(row);
        });
    }

    updateRealTimeData(realtime) {
        document.getElementById('currentUsers').textContent = realtime.currentUsers;
        document.getElementById('activeWizards').textContent = realtime.activeWizards;
        document.getElementById('pendingValidations').textContent = realtime.pendingValidations;
        
        // Update recent activity
        const activityContainer = document.getElementById('recentActivity');
        activityContainer.innerHTML = '';
        
        realtime.recentActivity.forEach(activity => {
            const item = document.createElement('div');
            item.className = `activity-item ${activity.type}`;
            item.innerHTML = `
                <div class="d-flex justify-content-between align-items-center">
                    <span>${activity.message}</span>
                    <small class="text-muted">${activity.time}</small>
                </div>
            `;
            activityContainer.appendChild(item);
        });
    }

    startRealTimeUpdates() {
        // Update real-time data every 30 seconds
        this.refreshInterval = setInterval(() => {
            this.loadRealTimeData();
        }, 30000);
    }

    async loadRealTimeData() {
        try {
            const response = await fetch('/api/analytics/wizard-realtime', {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            if (response.ok) {
                const data = await response.json();
                if (data.success) {
                    this.updateRealTimeData(data.data);
                }
            }
        } catch (error) {
            console.error('Failed to load real-time data:', error);
        }
    }

    async exportData() {
        try {
            const response = await fetch(`/api/analytics/wizard-export?period=${this.currentPeriod}`, {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            if (response.ok) {
                const blob = await response.blob();
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `wizard-analytics-${this.currentPeriod}-${new Date().toISOString().split('T')[0]}.xlsx`;
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
                document.body.removeChild(a);
            }
        } catch (error) {
            console.error('Failed to export data:', error);
            this.showError('Failed to export data');
        }
    }

    showLoading(show) {
        const overlay = document.getElementById('loadingOverlay');
        if (show) {
            overlay.classList.remove('d-none');
        } else {
            overlay.classList.add('d-none');
        }
    }

    showError(message) {
        // You could implement a toast notification here
        console.error(message);
        alert(message);
    }

    destroy() {
        if (this.refreshInterval) {
            clearInterval(this.refreshInterval);
        }
        
        Object.values(this.charts).forEach(chart => {
            chart.destroy();
        });
    }
}

// Cleanup on page unload
window.addEventListener('beforeunload', () => {
    if (window.dashboard) {
        window.dashboard.destroy();
    }
});
</script>
@endpush