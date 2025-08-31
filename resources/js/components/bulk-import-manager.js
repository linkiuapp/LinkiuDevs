/**
 * Bulk Import Manager
 * Handles the complete bulk import workflow for stores
 */
class BulkImportManager {
    constructor(config) {
        this.config = config;
        this.currentStep = 1;
        this.uploadedFile = null;
        this.fileData = null;
        this.columnMapping = {};
        this.validationResults = null;
        this.batchId = null;
        this.processingInterval = null;
        this.websocketChannel = null;
        
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.setupDragAndDrop();
    }

    setupEventListeners() {
        // File input change
        document.getElementById('bulkFileInput').addEventListener('change', (e) => {
            this.handleFileSelect(e.target.files[0]);
        });

        // Template type change
        document.querySelectorAll('input[name="templateType"]').forEach(radio => {
            radio.addEventListener('change', () => {
                this.updateTemplateInfo();
            });
        });
    }

    setupDragAndDrop() {
        const uploadArea = document.getElementById('fileUploadArea');
        
        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });

        uploadArea.addEventListener('dragleave', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
        });

        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                this.handleFileSelect(files[0]);
            }
        });

        uploadArea.addEventListener('click', () => {
            document.getElementById('bulkFileInput').click();
        });
    }

    handleFileSelect(file) {
        if (!file) return;

        // Validate file type
        const allowedTypes = [
            'text/csv',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ];

        if (!allowedTypes.includes(file.type) && !file.name.match(/\.(csv|xlsx|xls)$/i)) {
            this.showError('Tipo de archivo no válido. Solo se permiten archivos CSV y Excel.');
            return;
        }

        // Validate file size (10MB max)
        if (file.size > 10 * 1024 * 1024) {
            this.showError('El archivo es demasiado grande. Tamaño máximo: 10MB.');
            return;
        }

        this.uploadedFile = file;
        this.showFileInfo(file);
        document.getElementById('uploadBtn').disabled = false;
    }

    showFileInfo(file) {
        const fileInfo = document.getElementById('fileInfo');
        const fileName = document.getElementById('fileName');
        const fileSize = document.getElementById('fileSize');

        fileName.textContent = file.name;
        fileSize.textContent = this.formatFileSize(file.size);
        fileInfo.style.display = 'block';
    }

    formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    async uploadFile() {
        if (!this.uploadedFile) return;

        this.showLoading('Subiendo archivo...');
        
        const formData = new FormData();
        formData.append('file', this.uploadedFile);
        formData.append('_token', this.config.csrfToken);

        try {
            const response = await fetch(this.config.uploadUrl, {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                this.fileData = result.data;
                this.goToStep(2);
                this.generateColumnMapping();
            } else {
                this.showError(result.message || 'Error al subir el archivo');
            }
        } catch (error) {
            console.error('Upload error:', error);
            this.showError('Error de conexión al subir el archivo');
        } finally {
            this.hideLoading();
        }
    }

    generateColumnMapping() {
        const container = document.getElementById('columnMappingContainer');
        const columns = this.fileData.columns || [];
        const sampleData = this.fileData.sample_data || [];

        // Available target fields
        const targetFields = {
            '': 'Seleccionar campo...',
            'owner_name': 'Nombre del Propietario *',
            'admin_email': 'Email del Administrador *',
            'owner_document_type': 'Tipo de Documento',
            'owner_document_number': 'Número de Documento',
            'owner_country': 'País del Propietario',
            'owner_department': 'Departamento del Propietario',
            'owner_city': 'Ciudad del Propietario',
            'name': 'Nombre de la Tienda *',
            'plan_id': 'ID del Plan *',
            'slug': 'URL de la Tienda',
            'email': 'Email de la Tienda',
            'phone': 'Teléfono',
            'description': 'Descripción',
            'document_type': 'Tipo de Documento Fiscal',
            'document_number': 'Número de Documento Fiscal',
            'country': 'País de la Tienda',
            'department': 'Departamento de la Tienda',
            'city': 'Ciudad de la Tienda',
            'address': 'Dirección',
            'billing_period': 'Período de Facturación',
            'initial_payment_status': 'Estado de Pago Inicial',
            'meta_title': 'Meta Título',
            'meta_description': 'Meta Descripción',
            'meta_keywords': 'Meta Keywords'
        };

        let html = '<div class="row mb-3">';
        html += '<div class="col-md-4"><strong>Columna del Archivo</strong></div>';
        html += '<div class="col-md-1"></div>';
        html += '<div class="col-md-4"><strong>Campo del Sistema</strong></div>';
        html += '<div class="col-md-3"><strong>Datos de Ejemplo</strong></div>';
        html += '</div>';

        columns.forEach((column, index) => {
            const sampleValue = sampleData[0] ? sampleData[0][index] || '' : '';
            const suggestedField = this.suggestFieldMapping(column);

            html += `
                <div class="column-mapping-item">
                    <div class="column-source">${column}</div>
                    <div class="column-arrow">
                        <i class="fas fa-arrow-right"></i>
                    </div>
                    <div class="column-target">
                        <select class="form-select" data-column="${column}" onchange="bulkImportManager.updateMapping('${column}', this.value)">
                            ${Object.entries(targetFields).map(([value, label]) => 
                                `<option value="${value}" ${value === suggestedField ? 'selected' : ''}>${label}</option>`
                            ).join('')}
                        </select>
                    </div>
                    <div class="column-preview">${sampleValue}</div>
                </div>
            `;

            // Set initial mapping
            this.columnMapping[column] = suggestedField;
        });

        container.innerHTML = html;
    }

    suggestFieldMapping(columnName) {
        const suggestions = {
            'nombre': 'owner_name',
            'name': 'owner_name',
            'propietario': 'owner_name',
            'owner': 'owner_name',
            'email': 'admin_email',
            'correo': 'admin_email',
            'mail': 'admin_email',
            'tienda': 'name',
            'store': 'name',
            'negocio': 'name',
            'plan': 'plan_id',
            'telefono': 'phone',
            'phone': 'phone',
            'celular': 'phone',
            'descripcion': 'description',
            'description': 'description',
            'pais': 'country',
            'country': 'country',
            'departamento': 'department',
            'department': 'department',
            'ciudad': 'city',
            'city': 'city',
            'direccion': 'address',
            'address': 'address'
        };

        const normalized = columnName.toLowerCase().trim();
        return suggestions[normalized] || '';
    }

    updateMapping(column, field) {
        this.columnMapping[column] = field;
    }

    async validateMapping() {
        // Check required fields
        const requiredFields = ['owner_name', 'admin_email', 'name', 'plan_id'];
        const mappedFields = Object.values(this.columnMapping).filter(field => field !== '');
        const missingRequired = requiredFields.filter(field => !mappedFields.includes(field));

        if (missingRequired.length > 0) {
            this.showError(`Campos requeridos faltantes: ${missingRequired.join(', ')}`);
            return;
        }

        this.showLoading('Validando mapeo de columnas...');

        try {
            const response = await fetch(this.config.validateUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.config.csrfToken
                },
                body: JSON.stringify({
                    file_data: this.fileData,
                    column_mapping: this.columnMapping
                })
            });

            const result = await response.json();

            if (result.success) {
                this.validationResults = result.data;
                this.goToStep(3);
                this.showValidationResults();
            } else {
                this.showError(result.message || 'Error en la validación');
            }
        } catch (error) {
            console.error('Validation error:', error);
            this.showError('Error de conexión durante la validación');
        } finally {
            this.hideLoading();
        }
    }

    showValidationResults() {
        const container = document.getElementById('validationResults');
        const results = this.validationResults;

        let html = `
            <div class="validation-summary">
                <div class="validation-stat success">
                    <h4>${results.valid_count}</h4>
                    <small>Registros Válidos</small>
                </div>
                <div class="validation-stat error">
                    <h4>${results.error_count}</h4>
                    <small>Registros con Errores</small>
                </div>
                <div class="validation-stat warning">
                    <h4>${results.warning_count || 0}</h4>
                    <small>Advertencias</small>
                </div>
            </div>
        `;

        if (results.errors && results.errors.length > 0) {
            html += `
                <div class="validation-errors">
                    <h6>Errores Encontrados:</h6>
                    ${results.errors.map(error => `
                        <div class="validation-error-item">
                            <div class="error-row">Fila ${error.row}</div>
                            <div class="error-message">${error.message}</div>
                        </div>
                    `).join('')}
                </div>
            `;
        }

        container.innerHTML = html;

        // Enable/disable proceed button
        const proceedBtn = document.getElementById('proceedToProcessBtn');
        proceedBtn.disabled = results.error_count > 0;
    }

    async processBulkImport() {
        this.showLoading('Iniciando procesamiento...');

        try {
            const response = await fetch(this.config.processUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.config.csrfToken
                },
                body: JSON.stringify({
                    file_data: this.fileData,
                    column_mapping: this.columnMapping,
                    validation_results: this.validationResults
                })
            });

            const result = await response.json();

            if (result.success) {
                this.batchId = result.data.batch_id;
                this.goToStep(4);
                this.startProgressTracking();
            } else {
                this.showError(result.message || 'Error al iniciar el procesamiento');
            }
        } catch (error) {
            console.error('Process error:', error);
            this.showError('Error de conexión durante el procesamiento');
        } finally {
            this.hideLoading();
        }
    }

    startProgressTracking() {
        // Try to use WebSocket first, fallback to polling
        if (this.setupWebSocketTracking()) {
            console.log('Using WebSocket for real-time progress tracking');
        } else {
            console.log('Falling back to polling for progress tracking');
            this.startPollingTracking();
        }
    }

    setupWebSocketTracking() {
        try {
            // Check if Laravel Echo is available
            if (typeof Echo !== 'undefined') {
                this.websocketChannel = Echo.private(`bulk-import.${window.userId}`)
                    .listen('.progress.updated', (event) => {
                        if (event.batch_id === this.batchId) {
                            this.updateProcessingStatus(event.progress);
                        }
                    })
                    .listen('.import.completed', (event) => {
                        if (event.batch_id === this.batchId) {
                            this.updateProcessingStatus(event.results);
                            this.showFinalResults();
                        }
                    });
                
                return true;
            }
        } catch (error) {
            console.error('WebSocket setup error:', error);
        }
        
        return false;
    }

    startPollingTracking() {
        const statusUrl = this.config.statusUrl.replace(':batchId', this.batchId);
        
        this.processingInterval = setInterval(async () => {
            try {
                const response = await fetch(statusUrl);
                const result = await response.json();

                if (result.success) {
                    this.updateProcessingStatus(result.data);

                    if (result.data.status === 'completed' || result.data.status === 'failed') {
                        clearInterval(this.processingInterval);
                        this.showFinalResults();
                    }
                }
            } catch (error) {
                console.error('Status check error:', error);
            }
        }, 2000); // Check every 2 seconds
    }

    updateProcessingStatus(status) {
        document.getElementById('processingStatus').textContent = status.message || 'Procesando...';
        document.getElementById('processingDetails').textContent = status.details || '';
        
        const progress = Math.round((status.processed / status.total) * 100);
        document.getElementById('processingProgress').style.width = `${progress}%`;
        document.getElementById('progressText').textContent = `${progress}%`;
        
        document.getElementById('successCount').textContent = status.success_count || 0;
        document.getElementById('errorCount').textContent = status.error_count || 0;
        document.getElementById('processedCount').textContent = status.processed || 0;
        document.getElementById('totalCount').textContent = status.total || 0;
    }

    async showFinalResults() {
        this.goToStep(5);
        
        const resultsUrl = this.config.resultsUrl.replace(':batchId', this.batchId);
        
        try {
            const response = await fetch(resultsUrl);
            const result = await response.json();

            if (result.success) {
                this.displayFinalResults(result.data);
            }
        } catch (error) {
            console.error('Results fetch error:', error);
        }
    }

    displayFinalResults(results) {
        const container = document.getElementById('importResults');
        
        let html = `
            <div class="import-summary">
                <div class="summary-card success">
                    <h3>${results.success_count}</h3>
                    <p>Tiendas Creadas</p>
                </div>
                <div class="summary-card error">
                    <h3>${results.error_count}</h3>
                    <p>Errores</p>
                </div>
                <div class="summary-card info">
                    <h3>${results.total_processed}</h3>
                    <p>Total Procesadas</p>
                </div>
            </div>
        `;

        if (results.created_stores && results.created_stores.length > 0) {
            html += `
                <div class="results-table">
                    <h6>Tiendas Creadas Exitosamente:</h6>
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Email</th>
                                <th>URL</th>
                                <th>Plan</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${results.created_stores.map(store => `
                                <tr>
                                    <td>${store.name}</td>
                                    <td>${store.admin_email}</td>
                                    <td><code>${store.slug}</code></td>
                                    <td>${store.plan_name}</td>
                                    <td><span class="badge bg-success">Creada</span></td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            `;
        }

        if (results.errors && results.errors.length > 0) {
            html += `
                <div class="results-table mt-4">
                    <h6>Errores:</h6>
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Fila</th>
                                <th>Error</th>
                                <th>Datos</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${results.errors.map(error => `
                                <tr>
                                    <td>${error.row}</td>
                                    <td class="text-danger">${error.message}</td>
                                    <td><small>${JSON.stringify(error.data)}</small></td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            `;
        }

        container.innerHTML = html;
    }

    goToStep(step) {
        // Hide all steps
        document.querySelectorAll('.bulk-import-step').forEach(stepEl => {
            stepEl.style.display = 'none';
        });

        // Show target step
        document.getElementById(`step-${step}`).style.display = 'block';

        // Update step indicators
        document.querySelectorAll('.step').forEach((stepEl, index) => {
            stepEl.classList.remove('active', 'completed');
            
            if (index + 1 === step) {
                stepEl.classList.add('active');
            } else if (index + 1 < step) {
                stepEl.classList.add('completed');
            }
        });

        this.currentStep = step;

        // Special handling for step 4 (processing)
        if (step === 4) {
            this.processBulkImport();
        }
    }

    downloadResults() {
        if (this.batchId) {
            const downloadUrl = this.config.downloadResultsUrl.replace(':batchId', this.batchId);
            window.open(downloadUrl, '_blank');
        }
    }

    clearFile() {
        this.uploadedFile = null;
        this.fileData = null;
        this.columnMapping = {};
        this.validationResults = null;
        this.batchId = null;
        
        if (this.processingInterval) {
            clearInterval(this.processingInterval);
        }
        
        if (this.websocketChannel) {
            this.websocketChannel.stopListening('.progress.updated');
            this.websocketChannel.stopListening('.import.completed');
            this.websocketChannel = null;
        }
    }

    showLoading(message = 'Cargando...') {
        document.getElementById('loadingMessage').textContent = message;
        const modal = new bootstrap.Modal(document.getElementById('loadingModal'));
        modal.show();
    }

    hideLoading() {
        const modal = bootstrap.Modal.getInstance(document.getElementById('loadingModal'));
        if (modal) {
            modal.hide();
        }
    }

    showError(message) {
        // You can implement a toast notification system here
        alert(message); // Simple fallback
    }

    updateTemplateInfo() {
        // Update template information based on selected type
        const selectedType = document.querySelector('input[name="templateType"]:checked').value;
        // This could update help text or other UI elements
    }

    /**
     * Cancel current import process
     */
    async cancelImport() {
        if (!this.batchId) return;

        try {
            const response = await fetch(this.config.cancelUrl.replace(':batchId', this.batchId), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.config.csrfToken
                }
            });

            const result = await response.json();

            if (result.success) {
                this.showError('Importación cancelada exitosamente');
                this.goToStep(1);
            } else {
                this.showError(result.message || 'Error al cancelar la importación');
            }
        } catch (error) {
            console.error('Cancel error:', error);
            this.showError('Error de conexión al cancelar la importación');
        }
    }

    /**
     * Retry failed import
     */
    async retryImport() {
        if (!this.batchId) return;

        try {
            const response = await fetch(this.config.retryUrl.replace(':batchId', this.batchId), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.config.csrfToken
                }
            });

            const result = await response.json();

            if (result.success) {
                this.batchId = result.data.batch_id;
                this.goToStep(4);
                this.startProgressTracking();
            } else {
                this.showError(result.message || 'Error al reintentar la importación');
            }
        } catch (error) {
            console.error('Retry error:', error);
            this.showError('Error de conexión al reintentar la importación');
        }
    }

    /**
     * Handle partial import failures
     */
    handlePartialFailure(results) {
        if (results.error_count > 0 && results.success_count > 0) {
            // Show option to retry only failed rows
            const retryButton = document.createElement('button');
            retryButton.className = 'btn btn-warning me-2';
            retryButton.innerHTML = '<i class="fas fa-redo me-2"></i>Reintentar Errores';
            retryButton.onclick = () => this.retryFailedRows(results.errors);

            const actionsContainer = document.querySelector('#importResults').parentNode.querySelector('.mt-4');
            if (actionsContainer) {
                actionsContainer.insertBefore(retryButton, actionsContainer.firstChild);
            }
        }
    }

    /**
     * Retry only failed rows
     */
    async retryFailedRows(errors) {
        // This would create a new import with only the failed rows
        // Implementation would depend on your specific requirements
        console.log('Retrying failed rows:', errors);
        this.showError('Funcionalidad de reintento de errores en desarrollo');
    }

    /**
     * Get queue health status
     */
    async getQueueHealth() {
        try {
            const response = await fetch(this.config.queueHealthUrl);
            const result = await response.json();

            if (result.success) {
                return result.data;
            }
        } catch (error) {
            console.error('Queue health check error:', error);
        }

        return null;
    }
}

// Make it available globally
window.BulkImportManager = BulkImportManager;