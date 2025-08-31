@extends('shared::layouts.admin')

@section('title', 'Importación Masiva de Tiendas')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">Importación Masiva de Tiendas</h1>
                    <p class="text-muted">Crea múltiples tiendas desde un archivo CSV o Excel</p>
                </div>
                <div>
                    <a href="{{ route('superlinkiu.stores.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Volver a Tiendas
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Progress Steps -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="bulk-import-steps">
                        <div class="step active" data-step="1">
                            <div class="step-number">1</div>
                            <div class="step-title">Subir Archivo</div>
                        </div>
                        <div class="step" data-step="2">
                            <div class="step-number">2</div>
                            <div class="step-title">Mapear Columnas</div>
                        </div>
                        <div class="step" data-step="3">
                            <div class="step-number">3</div>
                            <div class="step-title">Validar Datos</div>
                        </div>
                        <div class="step" data-step="4">
                            <div class="step-number">4</div>
                            <div class="step-title">Procesar</div>
                        </div>
                        <div class="step" data-step="5">
                            <div class="step-number">5</div>
                            <div class="step-title">Resultados</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Step 1: File Upload -->
    <div class="bulk-import-step" id="step-1">
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-upload me-2"></i>Subir Archivo de Tiendas
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- File Upload Area -->
                        <div class="file-upload-area" id="fileUploadArea">
                            <div class="upload-content">
                                <i class="fas fa-cloud-upload-alt fa-3x text-primary mb-3"></i>
                                <h5>Arrastra tu archivo aquí o haz clic para seleccionar</h5>
                                <p class="text-muted">Formatos soportados: CSV, Excel (.xlsx, .xls)</p>
                                <p class="text-muted">Tamaño máximo: 10MB</p>
                                <input type="file" id="bulkFileInput" accept=".csv,.xlsx,.xls" style="display: none;">
                                <button type="button" class="btn btn-primary" onclick="document.getElementById('bulkFileInput').click()">
                                    Seleccionar Archivo
                                </button>
                            </div>
                        </div>

                        <!-- File Info -->
                        <div class="file-info mt-3" id="fileInfo" style="display: none;">
                            <div class="alert alert-info">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="fas fa-file me-2"></i>
                                        <span id="fileName"></span>
                                        <small class="text-muted ms-2">(<span id="fileSize"></span>)</small>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="clearFile()">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Upload Progress -->
                        <div class="upload-progress mt-3" id="uploadProgress" style="display: none;">
                            <div class="progress">
                                <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                            </div>
                            <small class="text-muted mt-1">Subiendo archivo...</small>
                        </div>

                        <!-- Action Buttons -->
                        <div class="mt-4">
                            <button type="button" class="btn btn-primary" id="uploadBtn" disabled onclick="uploadFile()">
                                <i class="fas fa-upload me-2"></i>Subir y Continuar
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-download me-2"></i>Plantilla de Importación
                        </h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">Descarga la plantilla con el formato correcto para importar tiendas.</p>
                        
                        <div class="template-options">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="templateType" id="templateBasic" value="basic" checked>
                                <label class="form-check-label" for="templateBasic">
                                    <strong>Básica</strong><br>
                                    <small class="text-muted">Campos esenciales únicamente</small>
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="templateType" id="templateComplete" value="complete">
                                <label class="form-check-label" for="templateComplete">
                                    <strong>Completa</strong><br>
                                    <small class="text-muted">Todos los campos disponibles</small>
                                </label>
                            </div>
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="radio" name="templateType" id="templateEnterprise" value="enterprise">
                                <label class="form-check-label" for="templateEnterprise">
                                    <strong>Empresarial</strong><br>
                                    <small class="text-muted">Incluye información fiscal</small>
                                </label>
                            </div>
                        </div>

                        <button type="button" class="btn btn-outline-primary w-100" onclick="downloadTemplate()">
                            <i class="fas fa-download me-2"></i>Descargar Plantilla
                        </button>
                    </div>
                </div>

                <!-- Help Card -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-info-circle me-2"></i>Información Importante
                        </h6>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2">
                                <i class="fas fa-check text-success me-2"></i>
                                <small>Los emails deben ser únicos</small>
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check text-success me-2"></i>
                                <small>Los slugs se generan automáticamente</small>
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check text-success me-2"></i>
                                <small>Las contraseñas se generan automáticamente</small>
                            </li>
                            <li class="mb-0">
                                <i class="fas fa-check text-success me-2"></i>
                                <small>Se envían credenciales por email</small>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Step 2: Column Mapping -->
    <div class="bulk-import-step" id="step-2" style="display: none;">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-columns me-2"></i>Mapear Columnas del Archivo
                        </h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">Asigna las columnas de tu archivo a los campos correspondientes del sistema.</p>
                        
                        <div id="columnMappingContainer">
                            <!-- Column mapping will be generated dynamically -->
                        </div>

                        <div class="mt-4">
                            <button type="button" class="btn btn-outline-secondary me-2" onclick="goToStep(1)">
                                <i class="fas fa-arrow-left me-2"></i>Anterior
                            </button>
                            <button type="button" class="btn btn-primary" onclick="validateMapping()">
                                <i class="fas fa-check me-2"></i>Validar Mapeo
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Step 3: Data Validation -->
    <div class="bulk-import-step" id="step-3" style="display: none;">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-check-circle me-2"></i>Validación de Datos
                        </h5>
                    </div>
                    <div class="card-body">
                        <div id="validationResults">
                            <!-- Validation results will be shown here -->
                        </div>

                        <div class="mt-4">
                            <button type="button" class="btn btn-outline-secondary me-2" onclick="goToStep(2)">
                                <i class="fas fa-arrow-left me-2"></i>Anterior
                            </button>
                            <button type="button" class="btn btn-primary" id="proceedToProcessBtn" onclick="goToStep(4)" disabled>
                                <i class="fas fa-arrow-right me-2"></i>Procesar Importación
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Step 4: Processing -->
    <div class="bulk-import-step" id="step-4" style="display: none;">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-cog fa-spin me-2"></i>Procesando Importación
                        </h5>
                    </div>
                    <div class="card-body text-center">
                        <div class="processing-animation mb-4">
                            <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                                <span class="visually-hidden">Procesando...</span>
                            </div>
                        </div>

                        <h5 id="processingStatus">Iniciando procesamiento...</h5>
                        <p class="text-muted" id="processingDetails">Preparando datos para importación</p>

                        <div class="progress mb-3" style="height: 20px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                 id="processingProgress" role="progressbar" style="width: 0%">
                                <span id="progressText">0%</span>
                            </div>
                        </div>

                        <div class="processing-stats row text-center">
                            <div class="col-md-3">
                                <div class="stat-item">
                                    <h4 class="text-success mb-0" id="successCount">0</h4>
                                    <small class="text-muted">Exitosas</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-item">
                                    <h4 class="text-danger mb-0" id="errorCount">0</h4>
                                    <small class="text-muted">Errores</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-item">
                                    <h4 class="text-info mb-0" id="processedCount">0</h4>
                                    <small class="text-muted">Procesadas</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-item">
                                    <h4 class="text-secondary mb-0" id="totalCount">0</h4>
                                    <small class="text-muted">Total</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Step 5: Results -->
    <div class="bulk-import-step" id="step-5" style="display: none;">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-flag-checkered me-2"></i>Resultados de la Importación
                        </h5>
                    </div>
                    <div class="card-body">
                        <div id="importResults">
                            <!-- Results will be shown here -->
                        </div>

                        <div class="mt-4">
                            <button type="button" class="btn btn-success me-2" onclick="downloadResults()">
                                <i class="fas fa-download me-2"></i>Descargar Reporte
                            </button>
                            <button type="button" class="btn btn-primary me-2" onclick="startNewImport()">
                                <i class="fas fa-plus me-2"></i>Nueva Importación
                            </button>
                            <a href="{{ route('superlinkiu.stores.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-list me-2"></i>Ver Tiendas
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loading Modal -->
<div class="modal fade" id="loadingModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center py-4">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
                <p class="mb-0" id="loadingMessage">Procesando...</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/bulk-import.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('js/components/bulk-import-manager.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    window.bulkImportManager = new BulkImportManager({
        uploadUrl: '{{ route("superlinkiu.stores.bulk.upload") }}',
        validateUrl: '{{ route("superlinkiu.stores.bulk.validate") }}',
        previewUrl: '{{ route("superlinkiu.stores.bulk.preview") }}',
        processUrl: '{{ route("superlinkiu.stores.bulk.process") }}',
        statusUrl: '{{ route("superlinkiu.stores.bulk.status", ":batchId") }}',
        resultsUrl: '{{ route("superlinkiu.stores.bulk.results", ":batchId") }}',
        downloadResultsUrl: '{{ route("superlinkiu.stores.bulk.download-results", ":batchId") }}',
        templateUrl: '{{ route("superlinkiu.stores.bulk.template.download") }}',
        csrfToken: '{{ csrf_token() }}'
    });
});

function downloadTemplate() {
    const templateType = document.querySelector('input[name="templateType"]:checked').value;
    window.open(`{{ route('superlinkiu.stores.bulk.template.download') }}?type=${templateType}`, '_blank');
}

function clearFile() {
    document.getElementById('bulkFileInput').value = '';
    document.getElementById('fileInfo').style.display = 'none';
    document.getElementById('uploadBtn').disabled = true;
    window.bulkImportManager.clearFile();
}

function uploadFile() {
    window.bulkImportManager.uploadFile();
}

function validateMapping() {
    window.bulkImportManager.validateMapping();
}

function goToStep(step) {
    window.bulkImportManager.goToStep(step);
}

function downloadResults() {
    window.bulkImportManager.downloadResults();
}

function startNewImport() {
    window.location.reload();
}
</script>
@endpush