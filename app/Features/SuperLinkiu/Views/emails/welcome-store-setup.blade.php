{{--
    Welcome Store Setup Email Template
    
    Email template for welcoming new store owners with setup guide and checklist
    Requirements: 4.5, 4.6 - Welcome email automation and setup checklist
--}}

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>¡Bienvenido a SuperLinkiu! - {{ $store['name'] }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f8fafc;
        }
        .container {
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: accent;
            padding: 40px 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 700;
        }
        .header p {
            margin: 12px 0 0 0;
            opacity: 0.9;
            font-size: 18px;
        }
        .content {
            padding: 30px;
        }
        .welcome-section {
            text-align: center;
            margin-bottom: 40px;
            padding: 30px;
            background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);
            border-radius: 12px;
            border: 2px solid #10b981;
        }
        .welcome-section h2 {
            color: #065f46;
            font-size: 24px;
            margin: 0 0 15px 0;
        }
        .welcome-section p {
            color: #047857;
            font-size: 16px;
            margin: 0;
        }
        .section {
            margin-bottom: 30px;
            padding: 25px;
            background-color: #f8fafc;
            border-radius: 12px;
            border-left: 4px solid #10b981;
        }
        .section h3 {
            margin: 0 0 20px 0;
            color: #1f2937;
            font-size: 20px;
            font-weight: 600;
        }
        .quick-links {
            display: grid;
            gap: 15px;
            margin: 20px 0;
        }
        .quick-link {
            display: block;
            padding: 15px 20px;
            background-color: #ffffff;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            text-decoration: none;
            color: #1f2937;
            transition: all 0.2s;
        }
        .quick-link:hover {
            border-color: #10b981;
            background-color: #f0fdf4;
            transform: translateY(-2px);
        }
        .quick-link strong {
            color: #10b981;
            font-size: 16px;
            display: block;
            margin-bottom: 5px;
        }
        .quick-link small {
            color: #6b7280;
            font-size: 14px;
        }
        .checklist {
            background-color: #ffffff;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
        }
        .checklist h4 {
            margin: 0 0 15px 0;
            color: #1f2937;
            font-size: 18px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .task-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 12px 0;
            border-bottom: 1px solid #f3f4f6;
        }
        .task-item:last-child {
            border-bottom: none;
        }
        .task-checkbox {
            width: 20px;
            height: 20px;
            border: 2px solid #d1d5db;
            border-radius: 4px;
            margin-top: 2px;
            flex-shrink: 0;
        }
        .task-content {
            flex: 1;
        }
        .task-title {
            font-weight: 600;
            color: #374151;
            margin-bottom: 4px;
        }
        .task-description {
            font-size: 14px;
            color: #6b7280;
        }
        .task-priority {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            margin-top: 4px;
        }
        .priority-high {
            background-color: #fef2f2;
            color: #dc2626;
        }
        .priority-medium {
            background-color: #fef3c7;
            color: #d97706;
        }
        .priority-low {
            background-color: #f0f9ff;
            color: #2563eb;
        }
        .credentials-box {
            background: linear-gradient(135deg, #faf5ff 0%, #f3e8ff 100%);
            border: 2px solid #a855f7;
            border-radius: 12px;
            padding: 20px;
            margin: 20px 0;
        }
        .credential-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .credential-item:last-child {
            border-bottom: none;
        }
        .credential-label {
            font-weight: 600;
            color: #7c3aed;
        }
        .credential-value {
            font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
            background-color: #ffffff;
            padding: 8px 12px;
            border-radius: 6px;
            border: 1px solid #d1d5db;
            font-size: 14px;
            color: #1f2937;
        }
        .tips-section {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            border: 2px solid #3b82f6;
            border-radius: 12px;
            padding: 25px;
            margin: 30px 0;
        }
        .tips-section h4 {
            margin: 0 0 15px 0;
            color: #1e40af;
            font-size: 18px;
        }
        .tips-list {
            margin: 0;
            padding-left: 20px;
        }
        .tips-list li {
            color: #1e40af;
            margin: 8px 0;
            font-size: 14px;
        }
        .footer {
            background-color: #f9fafb;
            padding: 30px;
            text-align: center;
            border-top: 1px solid #e5e7eb;
        }
        .footer p {
            margin: 0;
            color: #6b7280;
            font-size: 14px;
        }
        .cta-button {
            display: inline-block;
            padding: 15px 30px;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: accent;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 600;
            margin: 20px 10px;
            transition: transform 0.2s;
        }
        .cta-button:hover {
            transform: translateY(-2px);
            color: accent;
        }
        .progress-bar {
            width: 100%;
            height: 8px;
            background-color: #e5e7eb;
            border-radius: 4px;
            overflow: hidden;
            margin: 15px 0;
        }
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #10b981 0%, #059669 100%);
            width: 0%;
            transition: width 0.3s ease;
        }
    </style>
</head>
<body>
    <div class="container">
        {{-- Header --}}
        <div class="header">
            <h1>🎉 ¡Bienvenido a SuperLinkiu!</h1>
            <p>Tu tienda {{ $store['name'] }} está lista para configurar</p>
        </div>

        {{-- Content --}}
        <div class="content">
            {{-- Welcome Message --}}
            <div class="welcome-section">
                <h2>¡Felicitaciones por crear tu tienda online!</h2>
                <p>Estás a solo unos pasos de tener tu negocio funcionando en internet. Esta guía te ayudará a configurar todo lo necesario para empezar a vender.</p>
            </div>

            {{-- Quick Access Links --}}
            <div class="section">
                <h3>🚀 Acceso Rápido</h3>
                <div class="quick-links">
                    <a href="{{ $credentials['admin_url'] }}" class="quick-link" target="_blank">
                        <strong>⚙️ Panel de Administración</strong>
                        <small>Configura y gestiona tu tienda</small>
                    </a>
                    <a href="{{ $credentials['frontend_url'] }}" class="quick-link" target="_blank">
                        <strong>🌐 Ver tu Tienda</strong>
                        <small>Así la verán tus clientes</small>
                    </a>
                </div>
                
                <div class="text-center">
                    <a href="{{ $credentials['admin_url'] }}" class="cta-button" target="_blank">
                        Comenzar Configuración
                    </a>
                </div>
            </div>

            {{-- Credentials --}}
            <div class="section">
                <h3>🔑 Tus Credenciales de Acceso</h3>
                <div class="credentials-box">
                    <div class="credential-item">
                        <span class="credential-label">Email:</span>
                        <span class="credential-value">{{ $credentials['email'] }}</span>
                    </div>
                    <div class="credential-item">
                        <span class="credential-label">Contraseña:</span>
                        <span class="credential-value">{{ $credentials['password'] }}</span>
                    </div>
                </div>
                <p style="color: #dc2626; font-size: 14px; margin-top: 15px;">
                    <strong>⚠️ Importante:</strong> Cambia tu contraseña inmediatamente después del primer acceso por seguridad.
                </p>
            </div>

            {{-- Setup Checklist --}}
            <div class="section">
                <h3>📋 Lista de Configuración</h3>
                <p>Sigue estos pasos para tener tu tienda completamente configurada:</p>
                
                <div class="progress-bar">
                    <div class="progress-fill" style="width: 0%"></div>
                </div>
                <p style="text-align: center; font-size: 14px; color: #6b7280; margin-top: 5px;">
                    Progreso: 0 de {{ count($setupTasks) }} tareas completadas
                </p>

                <div class="checklist">
                    @foreach($setupTasks as $index => $task)
                    <div class="task-item">
                        <div class="task-checkbox"></div>
                        <div class="task-content">
                            <div class="task-title">{{ $index + 1 }}. {{ $task['title'] }}</div>
                            <div class="task-description">{{ $task['description'] }}</div>
                            <span class="task-priority priority-{{ $task['priority'] }}">
                                {{ strtoupper($task['priority']) }}
                            </span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Next Steps --}}
            <div class="section">
                <h3>🎯 Próximos Pasos Recomendados</h3>
                <ol style="padding-left: 20px; color: #374151;">
                    <li style="margin: 12px 0;"><strong>Accede al panel de administración</strong> usando las credenciales proporcionadas</li>
                    <li style="margin: 12px 0;"><strong>Cambia tu contraseña</strong> por una más segura y personal</li>
                    <li style="margin: 12px 0;"><strong>Configura la información básica</strong> de tu tienda (nombre, descripción, logo)</li>
                    <li style="margin: 12px 0;"><strong>Añade tu primer producto</strong> para probar el sistema</li>
                    <li style="margin: 12px 0;"><strong>Configura métodos de pago</strong> para recibir pagos</li>
                    <li style="margin: 12px 0;"><strong>Personaliza el diseño</strong> según tu marca</li>
                    <li style="margin: 12px 0;"><strong>Configura envíos</strong> y políticas de la tienda</li>
                </ol>
            </div>

            {{-- Tips Section --}}
            <div class="tips-section">
                <h4>💡 Consejos para el Éxito</h4>
                <ul class="tips-list">
                    <li>Usa imágenes de alta calidad para tus productos</li>
                    <li>Escribe descripciones detalladas y atractivas</li>
                    <li>Configura múltiples métodos de pago para mayor comodidad</li>
                    <li>Mantén actualizada la información de contacto</li>
                    <li>Revisa regularmente las estadísticas de tu tienda</li>
                    <li>Responde rápidamente a las consultas de los clientes</li>
                    <li>Mantén tu inventario actualizado</li>
                </ul>
            </div>

            {{-- Support Section --}}
            <div class="section">
                <h3>🆘 ¿Necesitas Ayuda?</h3>
                <p>Nuestro equipo está aquí para ayudarte en cada paso del camino:</p>
                <ul style="padding-left: 20px; color: #374151;">
                    <li>📧 Email de soporte: soporte@superlinkiu.com</li>
                    <li>📱 WhatsApp: +57 300 123 4567</li>
                    <li>🌐 Centro de ayuda: https://ayuda.superlinkiu.com</li>
                    <li>📚 Documentación: https://docs.superlinkiu.com</li>
                </ul>
            </div>
        </div>

        {{-- Footer --}}
        <div class="footer">
            <p>
                <strong>SuperLinkiu</strong> - Plataforma de E-commerce<br>
                Email enviado el {{ $generatedAt }}<br>
                <small>Este email contiene información importante sobre tu nueva tienda.</small>
            </p>
        </div>
    </div>
</body>
</html>