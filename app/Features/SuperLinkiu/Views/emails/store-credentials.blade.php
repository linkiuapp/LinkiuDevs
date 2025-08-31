{{--
    Store Credentials Email Template
    
    Email template for sending store credentials to administrators
    Requirements: 4.2 - Credential email delivery option
--}}

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Credenciales de Acceso - {{ $credentials['store_name'] }}</title>
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
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
        }
        .header p {
            margin: 8px 0 0 0;
            opacity: 0.9;
            font-size: 16px;
        }
        .content {
            padding: 30px;
        }
        .section {
            margin-bottom: 30px;
            padding: 20px;
            background-color: #f8fafc;
            border-radius: 8px;
            border-left: 4px solid #10b981;
        }
        .section h2 {
            margin: 0 0 15px 0;
            color: #1f2937;
            font-size: 18px;
            font-weight: 600;
        }
        .credential-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .credential-item:last-child {
            border-bottom: none;
        }
        .credential-label {
            font-weight: 600;
            color: #374151;
        }
        .credential-value {
            font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
            background-color: #ffffff;
            padding: 6px 12px;
            border-radius: 6px;
            border: 1px solid #d1d5db;
            font-size: 14px;
            color: #1f2937;
        }
        .password-value {
            background-color: #fef3c7;
            border-color: #f59e0b;
            color: #92400e;
            font-weight: 600;
        }
        .links {
            margin: 20px 0;
        }
        .link-item {
            display: block;
            padding: 12px 16px;
            margin: 8px 0;
            background-color: #ffffff;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            text-decoration: none;
            color: #1f2937;
            transition: all 0.2s;
        }
        .link-item:hover {
            border-color: #10b981;
            background-color: #f0fdf4;
        }
        .link-item strong {
            color: #10b981;
        }
        .warning {
            background-color: #fef3c7;
            border: 1px solid #f59e0b;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .warning h3 {
            margin: 0 0 10px 0;
            color: #92400e;
            font-size: 16px;
        }
        .warning ul {
            margin: 10px 0 0 0;
            padding-left: 20px;
        }
        .warning li {
            color: #92400e;
            margin: 5px 0;
        }
        .footer {
            background-color: #f9fafb;
            padding: 20px 30px;
            text-align: center;
            border-top: 1px solid #e5e7eb;
        }
        .footer p {
            margin: 0;
            color: #6b7280;
            font-size: 14px;
        }
        .security-tips {
            background-color: #eff6ff;
            border: 1px solid #3b82f6;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .security-tips h3 {
            margin: 0 0 10px 0;
            color: #1e40af;
            font-size: 16px;
        }
        .security-tips ul {
            margin: 10px 0 0 0;
            padding-left: 20px;
        }
        .security-tips li {
            color: #1e40af;
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        {{-- Header --}}
        <div class="header">
            <h1>🎉 ¡Tienda Creada Exitosamente!</h1>
            <p>Credenciales de acceso para {{ $credentials['store_name'] }}</p>
        </div>

        {{-- Content --}}
        <div class="content">
            {{-- Store Information --}}
            <div class="section">
                <h2>🏪 Información de la Tienda</h2>
                <div class="credential-item">
                    <span class="credential-label">Nombre:</span>
                    <span class="credential-value">{{ $credentials['store_name'] }}</span>
                </div>
                <div class="credential-item">
                    <span class="credential-label">URL:</span>
                    <span class="credential-value">{{ $credentials['store_slug'] }}</span>
                </div>
                
                <div class="links">
                    <a href="{{ $credentials['frontend_url'] }}" class="link-item" target="_blank">
                        <strong>🌐 Ver tu tienda</strong><br>
                        <small>{{ $credentials['frontend_url'] }}</small>
                    </a>
                    <a href="{{ $credentials['admin_url'] }}" class="link-item" target="_blank">
                        <strong>⚙️ Panel de administración</strong><br>
                        <small>{{ $credentials['admin_url'] }}</small>
                    </a>
                </div>
            </div>

            {{-- Admin Credentials --}}
            <div class="section">
                <h2>👤 Credenciales del Administrador</h2>
                <div class="credential-item">
                    <span class="credential-label">Nombre:</span>
                    <span class="credential-value">{{ $credentials['name'] }}</span>
                </div>
                <div class="credential-item">
                    <span class="credential-label">Email:</span>
                    <span class="credential-value">{{ $credentials['email'] }}</span>
                </div>
                <div class="credential-item">
                    <span class="credential-label">Contraseña:</span>
                    <span class="credential-value password-value">{{ $credentials['password'] }}</span>
                </div>
            </div>

            {{-- Security Warning --}}
            <div class="warning">
                <h3>⚠️ Información Importante de Seguridad</h3>
                <p><strong>Esta es la única vez que recibirás estas credenciales por email.</strong></p>
                <ul>
                    <li>Cambia la contraseña inmediatamente después del primer acceso</li>
                    <li>No reenvíes este email a otras personas</li>
                    <li>Guarda las credenciales en un gestor de contraseñas seguro</li>
                    <li>Elimina este email después de guardar las credenciales</li>
                </ul>
            </div>

            {{-- Security Tips --}}
            <div class="security-tips">
                <h3>🔒 Consejos de Seguridad</h3>
                <ul>
                    <li>Usa una contraseña fuerte con al menos 12 caracteres</li>
                    <li>Incluye mayúsculas, minúsculas, números y símbolos</li>
                    <li>Activa la autenticación de dos factores si está disponible</li>
                    <li>Revisa regularmente los accesos a tu cuenta</li>
                    <li>Mantén tu navegador y sistema operativo actualizados</li>
                </ul>
            </div>

            {{-- Next Steps --}}
            <div class="section">
                <h2>🚀 Próximos Pasos</h2>
                <ol style="padding-left: 20px; color: #374151;">
                    <li style="margin: 8px 0;">Accede al panel de administración usando las credenciales proporcionadas</li>
                    <li style="margin: 8px 0;">Cambia la contraseña por una de tu elección</li>
                    <li style="margin: 8px 0;">Configura la información básica de tu tienda</li>
                    <li style="margin: 8px 0;">Añade tus primeros productos o servicios</li>
                    <li style="margin: 8px 0;">Personaliza el diseño según tu marca</li>
                </ol>
            </div>
        </div>

        {{-- Footer --}}
        <div class="footer">
            <p>
                Credenciales generadas el {{ $generatedAt }}<br>
                <strong>SuperLinkiu</strong> - Plataforma de E-commerce
            </p>
        </div>
    </div>
</body>
</html>