/**
 * Post-Creation Success Flow Component
 * 
 * Comprehensive success modal with next steps, direct links to store admin panel,
 * welcome email automation, and store setup checklist generation
 * Requirements: 4.5, 4.6 - Post-creation success flow
 */

document.addEventListener('alpine:init', () => {
    Alpine.data('postCreationSuccessFlow', (config = {}) => ({
        // Configuration
        store: config.store || {},
        credentials: config.credentials || {},
        showModal: config.show || false,
        
        // State
        showPassword: false,
        emailSending: false,
        welcomeEmailSent: false,
        
        // Setup Tasks Checklist
        setupTasks: [
            {
                title: 'Cambiar contraseña por defecto',
                description: 'Actualiza tu contraseña por una más segura',
                completed: false,
                link: null, // Will be set dynamically
                priority: 'high'
            },
            {
                title: 'Configurar información de la tienda',
                description: 'Nombre, descripción, logo y datos de contacto',
                completed: false,
                link: null,
                priority: 'high'
            },
            {
                title: 'Configurar métodos de pago',
                description: 'PayPal, Stripe, transferencias bancarias, etc.',
                completed: false,
                link: null,
                priority: 'high'
            },
            {
                title: 'Añadir primer producto',
                description: 'Crea tu primer producto o servicio',
                completed: false,
                link: null,
                priority: 'medium'
            },
            {
                title: 'Personalizar diseño',
                description: 'Colores, fuentes y estilo de tu tienda',
                completed: false,
                link: null,
                priority: 'medium'
            },
            {
                title: 'Configurar envíos',
                description: 'Zonas de envío, costos y tiempos',
                completed: false,
                link: null,
                priority: 'medium'
            },
            {
                title: 'Configurar SEO básico',
                description: 'Meta títulos, descripciones y palabras clave',
                completed: false,
                link: null,
                priority: 'low'
            },
            {
                title: 'Configurar políticas',
                description: 'Términos, privacidad y devoluciones',
                completed: false,
                link: null,
                priority: 'low'
            }
        ],

        init() {
            console.log('🚀 Post-Creation Success Flow: Initialized', {
                store: this.store,
                credentials: this.credentials
            });
            
            // Set up dynamic links for tasks
            this.setupTaskLinks();
            
            // Auto-send welcome email
            this.sendWelcomeEmailAutomatically();
            
            // Load saved progress if exists
            this.loadSavedProgress();
            
            // Auto-save progress when tasks change
            this.$watch('setupTasks', () => {
                this.saveProgress();
            }, { deep: true });
        },

        /**
         * Setup Methods
         */
        setupTaskLinks() {
            const adminUrl = this.credentials.admin_url;
            if (!adminUrl) return;
            
            // Set up links for each task
            this.setupTasks.forEach((task, index) => {
                switch (index) {
                    case 0: // Change password
                        task.link = `${adminUrl}/profile/security`;
                        break;
                    case 1: // Store information
                        task.link = `${adminUrl}/settings/general`;
                        break;
                    case 2: // Payment methods
                        task.link = `${adminUrl}/settings/payments`;
                        break;
                    case 3: // Add product
                        task.link = `${adminUrl}/products/create`;
                        break;
                    case 4: // Customize design
                        task.link = `${adminUrl}/appearance/themes`;
                        break;
                    case 5: // Configure shipping
                        task.link = `${adminUrl}/settings/shipping`;
                        break;
                    case 6: // SEO
                        task.link = `${adminUrl}/settings/seo`;
                        break;
                    case 7: // Policies
                        task.link = `${adminUrl}/settings/policies`;
                        break;
                }
            });
        },

        /**
         * Progress Management
         */
        toggleTask(index) {
            if (index >= 0 && index < this.setupTasks.length) {
                this.setupTasks[index].completed = !this.setupTasks[index].completed;
                console.log('📋 Task toggled:', this.setupTasks[index].title, this.setupTasks[index].completed);
            }
        },

        getCompletedTasksCount() {
            return this.setupTasks.filter(task => task.completed).length;
        },

        getProgressPercentage() {
            const completed = this.getCompletedTasksCount();
            const total = this.setupTasks.length;
            return Math.round((completed / total) * 100);
        },

        saveProgress() {
            const progressData = {
                storeId: this.store.id,
                tasks: this.setupTasks.map(task => ({
                    title: task.title,
                    completed: task.completed
                })),
                lastUpdated: new Date().toISOString()
            };
            
            localStorage.setItem(`store_setup_progress_${this.store.id}`, JSON.stringify(progressData));
        },

        loadSavedProgress() {
            const saved = localStorage.getItem(`store_setup_progress_${this.store.id}`);
            if (saved) {
                try {
                    const progressData = JSON.parse(saved);
                    
                    // Update task completion status
                    progressData.tasks.forEach((savedTask, index) => {
                        if (this.setupTasks[index] && savedTask.title === this.setupTasks[index].title) {
                            this.setupTasks[index].completed = savedTask.completed;
                        }
                    });
                    
                    console.log('📋 Loaded saved progress:', progressData);
                } catch (error) {
                    console.error('Error loading saved progress:', error);
                }
            }
        },

        /**
         * Email Methods
         */
        async sendWelcomeEmailAutomatically() {
            // Auto-send welcome email after 2 seconds
            setTimeout(() => {
                this.sendWelcomeEmail();
            }, 2000);
        },

        async sendWelcomeEmail() {
            try {
                this.emailSending = true;
                
                const response = await fetch('/superlinkiu/api/stores/send-welcome-email', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    },
                    body: JSON.stringify({
                        store_id: this.store.id,
                        email: this.credentials.email,
                        store_data: this.store,
                        credentials: this.credentials,
                        setup_tasks: this.setupTasks
                    })
                });

                const result = await response.json();

                if (result.success) {
                    this.welcomeEmailSent = true;
                    console.log('✅ Welcome email sent successfully');
                } else {
                    console.error('❌ Welcome email failed:', result.message);
                }

            } catch (error) {
                console.error('Welcome email error:', error);
            } finally {
                this.emailSending = false;
            }
        },

        async resendWelcomeEmail() {
            await this.sendWelcomeEmail();
        },

        /**
         * UI Methods
         */
        togglePasswordVisibility() {
            this.showPassword = !this.showPassword;
        },

        closeModal() {
            this.showModal = false;
            
            // Emit close event
            this.$dispatch('success-flow-closed', {
                store: this.store,
                progress: this.getProgressPercentage()
            });
        },

        goToAdminPanel() {
            if (this.credentials.admin_url) {
                window.open(this.credentials.admin_url, '_blank');
            }
        },

        /**
         * Copy Methods
         */
        async copyToClipboard(text, label = 'Texto') {
            try {
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    await navigator.clipboard.writeText(text);
                    this.showNotification(`${label} copiado al portapapeles`, 'success');
                } else {
                    this.fallbackCopy(text);
                    this.showNotification(`${label} copiado al portapapeles`, 'success');
                }
            } catch (error) {
                console.error('Copy failed:', error);
                this.showNotification('Error al copiar al portapapeles', 'error');
            }
        },

        fallbackCopy(text) {
            const textArea = document.createElement('textarea');
            textArea.value = text;
            textArea.style.position = 'fixed';
            textArea.style.left = '-999999px';
            textArea.style.top = '-999999px';
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            
            try {
                document.execCommand('copy');
            } catch (error) {
                console.error('Fallback copy failed:', error);
                throw error;
            } finally {
                document.body.removeChild(textArea);
            }
        },

        /**
         * Download Methods
         */
        downloadSetupGuide() {
            const guideContent = this.generateSetupGuide();
            const blob = new Blob([guideContent], { type: 'text/plain' });
            const url = window.URL.createObjectURL(blob);
            
            const link = document.createElement('a');
            link.href = url;
            link.download = `guia-configuracion-${this.store.slug}-${new Date().toISOString().split('T')[0]}.txt`;
            
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            
            window.URL.revokeObjectURL(url);
            
            this.showNotification('Guía de configuración descargada', 'success');
        },

        generateSetupGuide() {
            return `
GUÍA DE CONFIGURACIÓN - ${this.store.name}
=============================================

INFORMACIÓN DE LA TIENDA:
- Nombre: ${this.store.name}
- URL: ${this.store.slug}
- Panel de Admin: ${this.credentials.admin_url}
- Vista Pública: ${this.credentials.frontend_url}

CREDENCIALES DE ACCESO:
- Email: ${this.credentials.email}
- Contraseña: ${this.credentials.password}

LISTA DE CONFIGURACIÓN:
${this.setupTasks.map((task, index) => 
    `${index + 1}. ${task.completed ? '✅' : '⏳'} ${task.title}
   ${task.description}
   ${task.link ? `   Enlace: ${task.link}` : ''}
   Prioridad: ${task.priority.toUpperCase()}
`).join('\n')}

PRÓXIMOS PASOS RECOMENDADOS:
1. Accede al panel de administración y cambia la contraseña
2. Configura la información básica de tu tienda
3. Añade tus primeros productos o servicios
4. Personaliza el diseño según tu marca
5. Configura métodos de pago y envío
6. Configura SEO básico
7. Establece políticas de la tienda

CONSEJOS DE SEGURIDAD:
- Cambia la contraseña inmediatamente
- Usa contraseñas fuertes y únicas
- Mantén el software actualizado
- Realiza copias de seguridad regulares
- Revisa los accesos periódicamente

SOPORTE:
Si necesitas ayuda, contacta a nuestro equipo de soporte.

Generado el: ${new Date().toLocaleString()}
            `.trim();
        },

        /**
         * Notification Methods
         */
        showNotification(message, type = 'info') {
            // Try to use Alpine store for notifications if available
            if (window.Alpine && Alpine.store && Alpine.store('notifications')) {
                Alpine.store('notifications').show(message, type);
            } else {
                // Fallback to simple alert
                alert(message);
            }
        },

        /**
         * Analytics Methods
         */
        trackEvent(eventName, data = {}) {
            // Track user interactions for analytics
            console.log('📊 Event tracked:', eventName, data);
            
            // Here you could integrate with analytics services
            // Example: gtag('event', eventName, data);
        },

        /**
         * Utility Methods
         */
        formatDate(date) {
            return new Date(date).toLocaleString('es-ES', {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        },

        getTasksByPriority(priority) {
            return this.setupTasks.filter(task => task.priority === priority);
        },

        getHighPriorityTasksRemaining() {
            return this.getTasksByPriority('high').filter(task => !task.completed).length;
        }
    }));
});

console.log('🚀 Post-Creation Success Flow Component: Loaded');