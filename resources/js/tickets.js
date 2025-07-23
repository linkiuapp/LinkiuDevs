// Tickets Management JavaScript - Alpine.js Components

// Funciones globales para tickets
window.ticketsUtils = {
    formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    },

    validateFile(file) {
        const allowedTypes = [
            'image/jpeg', 
            'image/jpg', 
            'image/png', 
            'application/pdf', 
            'application/msword', 
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 
            'text/plain'
        ];
        const maxSize = 5 * 1024 * 1024; // 5MB

        return allowedTypes.includes(file.type) && file.size <= maxSize;
    },

    showNotification(message, type = 'info') {
        // Crear notificación temporal
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg transition-all duration-300 transform translate-x-full`;
        
        // Colores según tipo
        const colors = {
            success: 'bg-success-300 text-white-50',
            error: 'bg-error-300 text-white-50',
            warning: 'bg-warning-300 text-black-500',
            info: 'bg-info-300 text-white-50'
        };
        
        notification.className += ` ${colors[type] || colors.info}`;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        // Animación de entrada
        setTimeout(() => {
            notification.classList.remove('translate-x-full');
        }, 100);
        
        // Auto-ocultar después de 5 segundos
        setTimeout(() => {
            notification.classList.add('translate-x-full');
            setTimeout(() => {
                document.body.removeChild(notification);
            }, 300);
        }, 5000);
    }
};

// Componente para el índice de tickets
window.ticketsIndex = function() {
    return {
        filters: {
            status: '',
            priority: '',
            category: '',
            search: ''
        },
        
        init() {
            // Inicializar filtros desde URL
            const urlParams = new URLSearchParams(window.location.search);
            this.filters.status = urlParams.get('status') || '';
            this.filters.priority = urlParams.get('priority') || '';
            this.filters.category = urlParams.get('category') || '';
            this.filters.search = urlParams.get('search') || '';
        },

        applyFilters() {
            const params = new URLSearchParams();
            
            Object.keys(this.filters).forEach(key => {
                if (this.filters[key]) {
                    params.set(key, this.filters[key]);
                }
            });
            
            window.location.search = params.toString();
        },

        clearFilters() {
            Object.keys(this.filters).forEach(key => {
                this.filters[key] = '';
            });
            window.location.search = '';
        }
    }
};

// Componente para crear ticket
window.createTicket = function() {
    return {
        selectedFiles: [],
        isSubmitting: false,
        
        init() {
            this.$watch('selectedFiles', () => {
                this.updateFileInput();
            });
        },

        handleFileSelect(event) {
            const files = Array.from(event.target.files);
            this.processFiles(files);
        },

        handleFileDrop(event) {
            const files = Array.from(event.dataTransfer.files);
            this.processFiles(files);
        },

        processFiles(files) {
            const maxFiles = 3;
            const availableSlots = maxFiles - this.selectedFiles.length;
            const validFiles = files.slice(0, availableSlots);
            
            validFiles.forEach(file => {
                if (window.ticketsUtils.validateFile(file)) {
                    this.selectedFiles.push(file);
                } else {
                    const fileType = file.type || 'desconocido';
                    const fileSize = window.ticketsUtils.formatFileSize(file.size);
                    window.ticketsUtils.showNotification(
                        `Archivo "${file.name}" no válido (${fileType}, ${fileSize}). Solo se permiten JPG, PNG, PDF, DOC, TXT hasta 5MB.`,
                        'error'
                    );
                }
            });
        },

        removeFile(index) {
            this.selectedFiles.splice(index, 1);
        },

        updateFileInput() {
            if (this.$refs.fileInput) {
                const dt = new DataTransfer();
                this.selectedFiles.forEach(file => dt.items.add(file));
                this.$refs.fileInput.files = dt.files;
            }
        },

        formatFileSize(bytes) {
            return window.ticketsUtils.formatFileSize(bytes);
        },

        submitForm() {
            if (this.isSubmitting) return;
            
            this.isSubmitting = true;
            
            // El formulario se enviará normalmente
            setTimeout(() => {
                // Esto se ejecutará si hay un error y no se redirige
                this.isSubmitting = false;
            }, 5000);
        }
    }
};

// Componente para detalle de ticket
window.ticketDetail = function() {
    return {
        showStatusModal: false,
        isUpdatingStatus: false,
        
        async updateStatus(status) {
            if (this.isUpdatingStatus) return;
            
            this.isUpdatingStatus = true;
            
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                if (!csrfToken) {
                    throw new Error('CSRF token no encontrado');
                }

                const response = await fetch(window.location.pathname + '/update-status', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ status })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    window.ticketsUtils.showNotification(data.message, 'success');
                    this.showStatusModal = false;
                    
                    // Recargar página después de 1 segundo
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    throw new Error(data.message || 'Error al actualizar el estado');
                }
            } catch (error) {
                console.error('Error:', error);
                window.ticketsUtils.showNotification(
                    error.message || 'Error al actualizar el estado',
                    'error'
                );
            } finally {
                this.isUpdatingStatus = false;
            }
        },

        scrollToResponse() {
            const responseElement = document.getElementById('response');
            if (responseElement) {
                responseElement.scrollIntoView({ 
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        }
    }
};

// Componente para respuesta de ticket
window.ticketResponse = function() {
    return {
        message: '',
        selectedFiles: [],
        isSubmitting: false,
        
        init() {
            // Auto-resize del textarea
            this.$nextTick(() => {
                const textarea = this.$refs.messageTextarea;
                if (textarea) {
                    textarea.addEventListener('input', this.autoResize.bind(this));
                }
            });
        },

        autoResize(event) {
            const textarea = event.target;
            textarea.style.height = 'auto';
            textarea.style.height = textarea.scrollHeight + 'px';
        },

        handleFileSelect(event) {
            const files = Array.from(event.target.files);
            this.processFiles(files);
        },

        processFiles(files) {
            const maxFiles = 3;
            const availableSlots = maxFiles - this.selectedFiles.length;
            const validFiles = files.slice(0, availableSlots);
            
            validFiles.forEach(file => {
                if (window.ticketsUtils.validateFile(file)) {
                    this.selectedFiles.push(file);
                } else {
                    window.ticketsUtils.showNotification(
                        `Archivo "${file.name}" no válido. Solo se permiten JPG, PNG, PDF, DOC, TXT hasta 5MB.`,
                        'error'
                    );
                }
            });
        },

        removeFile(index) {
            this.selectedFiles.splice(index, 1);
        },

        formatFileSize(bytes) {
            return window.ticketsUtils.formatFileSize(bytes);
        },

        submitResponse() {
            if (this.isSubmitting || !this.message.trim()) return;
            
            this.isSubmitting = true;
            
            // El formulario se enviará normalmente
            setTimeout(() => {
                this.isSubmitting = false;
            }, 5000);
        }
    }
};

// Funciones de utilidad específicas para tickets
window.ticketHelpers = {
    previewAttachment(url, type) {
        if (type.startsWith('image/')) {
            // Crear modal de preview para imágenes
            const modal = document.createElement('div');
            modal.className = 'fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50';
            modal.innerHTML = `
                <div class="max-w-4xl max-h-4xl p-4">
                    <img src="${url}" class="max-w-full max-h-full object-contain rounded">
                    <button class="absolute top-4 right-4 text-white text-2xl" onclick="this.parentElement.parentElement.remove()">
                        ×
                    </button>
                </div>
            `;
            document.body.appendChild(modal);
            
            // Cerrar con ESC
            const closeOnEsc = (e) => {
                if (e.key === 'Escape') {
                    modal.remove();
                    document.removeEventListener('keydown', closeOnEsc);
                }
            };
            document.addEventListener('keydown', closeOnEsc);
            
            // Cerrar al hacer click fuera
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    modal.remove();
                    document.removeEventListener('keydown', closeOnEsc);
                }
            });
        } else {
            // Para otros tipos, abrir en nueva pestaña
            window.open(url, '_blank');
        }
    },

    downloadAttachment(url, filename) {
        const link = document.createElement('a');
        link.href = url;
        link.download = filename;
        link.target = '_blank';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
};

// Auto-inicialización cuando se carga el DOM
document.addEventListener('DOMContentLoaded', function() {
    console.log('Tickets JavaScript loaded');
}); 