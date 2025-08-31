@extends('superlinkiu::layouts.app')

@section('title', 'Dashboard de Importaciones Masivas')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">Dashboard de Importaciones Masivas</h1>
                    <p class="text-muted">Monitoreo y estadísticas de importaciones de tiendas</p>
                </div>
                <div>
                    <a href="{{ route('superlinkiu.stores.bulk.import') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Nueva Importación
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-primary text-accent">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0" id="totalImports">{{ $statistics['total_imports'] ?? 0 }}</h4>
                            <small>Total Importaciones</small>
                        </div>
                        <div class="text-accent-50">
                            <i class="fas fa-upload fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-success text-accent">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0" id="successfulImports">{{ $statistics['successful_imports'] ?? 0 }}</h4>
                            <small>Exitosas</small>
                        </div>
                        <div class="text-accent-50">
                            <i class="fas fa-check-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-warning text-accent">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0" id="processingImports">{{ $statistics['processing_imports'] ?? 0 }}</h4>
                            <small>En Proceso</small>
                        </div>
                        <div class="text-accent-50">
                            <i class="fas fa-cog fa-2x fa-spin"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-danger text-accent">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0" id="failedImports">{{ $statistics['failed_imports'] ?? 0 }}</h4>
                            <small>Fallidas</small>
                        </div>
                        <div class="text-accent-50">
                            <i class="fas fa-exclamation-triangle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-line me-2"></i>Importaciones por Día
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="importsChart" height="100"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-pie me-2"></i>Estado de Importaciones
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="statusChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Imports Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-history me-2"></i>Importaciones Recientes
                    </h5>
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="refreshImports()">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="exportImportHistory()">
                            <i class="fas fa-download"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="importsTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Fecha</th>
                                    <th>Usuario</th>
                                    <th>Total Filas</th>
                                    <th>Exitosas</th>
                                    <th>Errores</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="importsTableBody">
                                @forelse($recentImports ?? [] as $import)
                                <tr>
                                    <td><code>{{ Str::limit($import['batch_id'], 8) }}</code></td>
                                    <td>{{ $import['created_at'] }}</td>
                                    <td>{{ $import['user_name'] ?? 'N/A' }}</td>
                                    <td>{{ $import['total_rows'] }}</td>
                                    <td><span class="badge bg-success">{{ $import['success_count'] }}</span></td>
                                    <td><span class="badge bg-danger">{{ $import['error_count'] }}</span></td>
                                    <td>
                                        @switch($import['status'])
                                            @case('completed')
                                                <span class="badge bg-success">Completada</span>
                                                @break
                                            @case('processing')
                                                <span class="badge bg-warning">Procesando</span>
                                                @break
                                            @case('failed')
                                                <span class="badge bg-danger">Fallida</span>
                                                @break
                                            @case('cancelled')
                                                <span class="badge bg-secondary">Cancelada</span>
                                                @break
                                            @default
                                                <span class="badge bg-info">{{ ucfirst($import['status']) }}</span>
                                        @endswitch
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button type="button" class="btn btn-outline-primary" 
                                                    onclick="viewImportDetails('{{ $import['batch_id'] }}')"
                                                    title="Ver Detalles">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            @if($import['status'] === 'completed' && $import['error_count'] === 0)
                                                <button type="button" class="btn btn-outline-success" 
                                                        onclick="downloadResults('{{ $import['batch_id'] }}')"
                                                        title="Descargar Resultados">
                                                    <i class="fas fa-download"></i>
                                                </button>
                                            @endif
                                            @if($import['status'] === 'failed')
                                                <button type="button" class="btn btn-outline-warning" 
                                                        onclick="retryImport('{{ $import['batch_id'] }}')"
                                                        title="Reintentar">
                                                    <i class="fas fa-redo"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">
                                        <i class="fas fa-inbox fa-2x mb-2"></i><br>
                                        No hay importaciones recientes
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Queue Health Status -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-server me-2"></i>Estado del Sistema
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <div class="status-indicator" id="queueStatus"></div>
                                </div>
                                <div>
                                    <h6 class="mb-0">Cola de Procesamiento</h6>
                                    <small class="text-muted" id="queueDetails">Verificando estado...</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <i class="fas fa-clock text-info"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">Trabajos Pendientes</h6>
                                    <small class="text-muted" id="pendingJobs">0</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <i class="fas fa-exclamation-triangle text-warning"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">Trabajos Fallidos</h6>
                                    <small class="text-muted" id="failedJobs">0</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Import Details Modal -->
<div class="modal fade" id="importDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalles de Importación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="importDetailsContent">
                <!-- Content will be loaded dynamically -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.status-indicator {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background-color: #28a745;
    animation: pulse 2s infinite;
}

.status-indicator.warning {
    background-color: #ffc107;
}

.status-indicator.danger {
    background-color: #dc3545;
}

@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.5; }
    100% { opacity: 1; }
}

.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid rgba(0, 0, 0, 0.125);
}

.table th {
    border-top: none;
    font-weight: 600;
    color: #495057;
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    initializeCharts();
    checkQueueHealth();
    
    // Refresh data every 30 seconds
    setInterval(() => {
        refreshStatistics();
        checkQueueHealth();
    }, 30000);
});

function initializeCharts() {
    // Imports per day chart
    const importsCtx = document.getElementById('importsChart').getContext('2d');
    new Chart(importsCtx, {
        type: 'line',
        data: {
            labels: @json($chartData['dates'] ?? []),
            datasets: [{
                label: 'Importaciones',
                data: @json($chartData['imports'] ?? []),
                borderColor: '#007bff',
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Status pie chart
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Exitosas', 'Fallidas', 'En Proceso'],
            datasets: [{
                data: @json($chartData['status'] ?? [0, 0, 0]),
                backgroundColor: ['#28a745', '#dc3545', '#ffc107']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
}

async function checkQueueHealth() {
    try {
        const response = await fetch('{{ route("superlinkiu.stores.bulk.queue-health") }}');
        const result = await response.json();
        
        if (result.success) {
            updateQueueStatus(result.data);
        }
    } catch (error) {
        console.error('Error checking queue health:', error);
    }
}

function updateQueueStatus(health) {
    const statusIndicator = document.getElementById('queueStatus');
    const queueDetails = document.getElementById('queueDetails');
    const pendingJobs = document.getElementById('pendingJobs');
    const failedJobs = document.getElementById('failedJobs');
    
    if (health.is_healthy) {
        statusIndicator.className = 'status-indicator';
        queueDetails.textContent = 'Sistema funcionando correctamente';
    } else {
        statusIndicator.className = 'status-indicator danger';
        queueDetails.textContent = 'Sistema con problemas';
    }
    
    pendingJobs.textContent = health.pending_jobs || 0;
    failedJobs.textContent = health.failed_jobs || 0;
}

async function refreshStatistics() {
    try {
        const response = await fetch('{{ route("superlinkiu.stores.bulk.dashboard") }}?ajax=1');
        const result = await response.json();
        
        if (result.success) {
            updateStatistics(result.data.statistics);
            updateRecentImports(result.data.recentImports);
        }
    } catch (error) {
        console.error('Error refreshing statistics:', error);
    }
}

function updateStatistics(stats) {
    document.getElementById('totalImports').textContent = stats.total_imports || 0;
    document.getElementById('successfulImports').textContent = stats.successful_imports || 0;
    document.getElementById('processingImports').textContent = stats.processing_imports || 0;
    document.getElementById('failedImports').textContent = stats.failed_imports || 0;
}

function updateRecentImports(imports) {
    const tbody = document.getElementById('importsTableBody');
    // Update table content with new imports data
    // Implementation would depend on your specific needs
}

async function viewImportDetails(batchId) {
    try {
        const response = await fetch(`{{ route("superlinkiu.stores.bulk.results", ":batchId") }}`.replace(':batchId', batchId));
        const result = await response.json();
        
        if (result.success) {
            showImportDetailsModal(result.data);
        }
    } catch (error) {
        console.error('Error loading import details:', error);
    }
}

function showImportDetailsModal(data) {
    const content = document.getElementById('importDetailsContent');
    
    content.innerHTML = `
        <div class="row">
            <div class="col-md-6">
                <h6>Estadísticas</h6>
                <ul class="list-unstyled">
                    <li><strong>Total Procesadas:</strong> ${data.total_processed}</li>
                    <li><strong>Exitosas:</strong> <span class="text-success">${data.success_count}</span></li>
                    <li><strong>Errores:</strong> <span class="text-danger">${data.error_count}</span></li>
                </ul>
            </div>
            <div class="col-md-6">
                <h6>Información</h6>
                <ul class="list-unstyled">
                    <li><strong>Completado:</strong> ${data.completed_at}</li>
                    <li><strong>Duración:</strong> ${calculateDuration(data)}</li>
                </ul>
            </div>
        </div>
        ${data.errors && data.errors.length > 0 ? `
            <div class="mt-3">
                <h6>Errores Encontrados</h6>
                <div class="table-responsive" style="max-height: 300px;">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Fila</th>
                                <th>Error</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${data.errors.map(error => `
                                <tr>
                                    <td>${error.row}</td>
                                    <td>${error.message}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            </div>
        ` : ''}
    `;
    
    const modal = new bootstrap.Modal(document.getElementById('importDetailsModal'));
    modal.show();
}

function calculateDuration(data) {
    // Calculate duration between created_at and completed_at
    // This is a placeholder implementation
    return 'N/A';
}

function downloadResults(batchId) {
    window.open(`{{ route("superlinkiu.stores.bulk.download-results", ":batchId") }}`.replace(':batchId', batchId), '_blank');
}

function retryImport(batchId) {
    if (confirm('¿Estás seguro de que quieres reintentar esta importación?')) {
        // Implementation for retry
        console.log('Retrying import:', batchId);
    }
}

function refreshImports() {
    location.reload();
}

function exportImportHistory() {
    // Implementation for exporting import history
    console.log('Exporting import history');
}
</script>
@endpush