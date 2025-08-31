<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Debug - Monitor de Errores</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f8f9fa;
            color: #333;
            line-height: 1.6;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .header h1 {
            font-size: 24px;
            margin-bottom: 5px;
        }
        
        .header-info {
            font-size: 14px;
            opacity: 0.9;
        }
        
        .header-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all 0.2s;
        }
        
        .btn-primary {
            background: rgba(255,255,255,0.2);
            color: white;
        }
        
        .btn-primary:hover {
            background: rgba(255,255,255,0.3);
        }
        
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        
        .btn-success {
            background: #28a745;
            color: white;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-left: 4px solid #667eea;
        }
        
        .stat-card.error {
            border-left-color: #dc3545;
        }
        
        .stat-card.warning {
            border-left-color: #ffc107;
        }
        
        .stat-card.info {
            border-left-color: #17a2b8;
        }
        
        .stat-card h3 {
            font-size: 14px;
            color: #666;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .stat-card .value {
            font-size: 32px;
            font-weight: bold;
            color: #333;
        }
        
        .filters {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: center;
        }
        
        .filters select, .filters input {
            padding: 8px 12px;
            border: 2px solid #e1e5e9;
            border-radius: 6px;
            font-size: 14px;
        }
        
        .filters select:focus, .filters input:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .errors-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .errors-header {
            background: #f8f9fa;
            padding: 20px;
            border-bottom: 1px solid #e1e5e9;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .error-item {
            padding: 20px;
            border-bottom: 1px solid #e1e5e9;
        }
        
        .error-item:last-child {
            border-bottom: none;
        }
        
        .error-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 10px;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .error-level {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .error-level.error {
            background: #fee;
            color: #c33;
        }
        
        .error-level.warning {
            background: #fff3cd;
            color: #856404;
        }
        
        .error-level.info {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .error-timestamp {
            color: #666;
            font-size: 14px;
        }
        
        .error-message {
            font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
            background: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
            margin: 10px 0;
            font-size: 13px;
            line-height: 1.4;
            overflow-x: auto;
        }
        
        .error-stack {
            font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
            background: #2d3748;
            color: #e2e8f0;
            padding: 15px;
            border-radius: 6px;
            font-size: 12px;
            line-height: 1.4;
            max-height: 300px;
            overflow-y: auto;
            white-space: pre-wrap;
            margin-top: 10px;
        }
        
        .toggle-stack {
            background: #6c757d;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            cursor: pointer;
            margin-top: 10px;
        }
        
        .no-errors {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }
        
        .no-errors h3 {
            margin-bottom: 10px;
            color: #28a745;
        }
        
        .loading {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        
        .auto-refresh {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
        }
        
        .auto-refresh input[type="checkbox"] {
            transform: scale(1.2);
        }
        
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            
            .filters {
                flex-direction: column;
                align-items: stretch;
            }
            
            .error-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <div>
                <h1>🔧 System Debug Monitor</h1>
                <div class="header-info">
                    {{ config('app.name') }} - {{ config('app.env') }} | 
                    Última actualización: <span id="last-update">{{ now()->format('H:i:s') }}</span>
                </div>
            </div>
            <div class="header-actions">
                <button class="btn btn-primary" onclick="refreshData()">🔄 Actualizar</button>
                <button class="btn btn-success" onclick="testNotification()">📧 Test Email</button>
                <a href="/system-debug/logout" class="btn btn-danger">🚪 Salir</a>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card error">
                <h3>Errores Totales</h3>
                <div class="value">{{ $stats['total'] }}</div>
            </div>
            <div class="stat-card warning">
                <h3>Últimas 24h</h3>
                <div class="value">{{ $stats['recent_24h'] }}</div>
            </div>
            <div class="stat-card info">
                <h3>Errores Críticos</h3>
                <div class="value">{{ $stats['by_level']['ERROR'] ?? 0 }}</div>
            </div>
            <div class="stat-card">
                <h3>Memoria PHP</h3>
                <div class="value" style="font-size: 20px;">{{ $systemInfo['memory_limit'] }}</div>
            </div>
        </div>

        <!-- Filters -->
        <div class="filters">
            <label>
                Nivel:
                <select id="level-filter" onchange="filterErrors()">
                    <option value="">Todos</option>
                    <option value="ERROR" {{ $level === 'ERROR' ? 'selected' : '' }}>ERROR</option>
                    <option value="WARNING" {{ $level === 'WARNING' ? 'selected' : '' }}>WARNING</option>
                    <option value="INFO" {{ $level === 'INFO' ? 'selected' : '' }}>INFO</option>
                </select>
            </label>
            
            <label>
                Límite:
                <select id="limit-filter" onchange="filterErrors()">
                    <option value="25" {{ $limit === 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ $limit === 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ $limit === 100 ? 'selected' : '' }}>100</option>
                    <option value="200" {{ $limit === 200 ? 'selected' : '' }}>200</option>
                </select>
            </label>
            
            <div class="auto-refresh">
                <input type="checkbox" id="auto-refresh" onchange="toggleAutoRefresh()">
                <label for="auto-refresh">Auto-actualizar (30s)</label>
            </div>
        </div>

        <!-- Errors List -->
        <div class="errors-container">
            <div class="errors-header">
                <h2>Errores Recientes ({{ count($errors) }})</h2>
                <button class="btn btn-danger" onclick="clearOldLogs()">🗑️ Limpiar Logs Antiguos</button>
            </div>
            
            <div id="errors-list">
                @if(count($errors) > 0)
                    @foreach($errors as $index => $error)
                        <div class="error-item">
                            <div class="error-header">
                                <span class="error-level {{ strtolower($error['level']) }}">{{ $error['level'] }}</span>
                                <span class="error-timestamp">{{ $error['timestamp'] }}</span>
                            </div>
                            
                            <div class="error-message">{{ $error['message'] }}</div>
                            
                            @if(!empty($error['stack_trace']))
                                <button class="toggle-stack" onclick="toggleStack({{ $index }})">
                                    Ver Stack Trace
                                </button>
                                <div class="error-stack" id="stack-{{ $index }}" style="display: none;">{{ $error['stack_trace'] }}</div>
                            @endif
                        </div>
                    @endforeach
                @else
                    <div class="no-errors">
                        <h3>✅ ¡Excelente!</h3>
                        <p>No se encontraron errores recientes</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        let autoRefreshInterval = null;
        
        function refreshData() {
            const level = document.getElementById('level-filter').value;
            const limit = document.getElementById('limit-filter').value;
            
            document.getElementById('errors-list').innerHTML = '<div class="loading">🔄 Cargando...</div>';
            
            fetch(`/system-debug/errors?level=${level}&limit=${limit}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateErrorsList(data.errors);
                        document.getElementById('last-update').textContent = new Date().toLocaleTimeString();
                    }
                })
                .catch(error => {
                    console.error('Error refreshing data:', error);
                    document.getElementById('errors-list').innerHTML = '<div class="no-errors"><h3>❌ Error</h3><p>No se pudieron cargar los datos</p></div>';
                });
        }
        
        function updateErrorsList(errors) {
            const container = document.getElementById('errors-list');
            
            if (errors.length === 0) {
                container.innerHTML = '<div class="no-errors"><h3>✅ ¡Excelente!</h3><p>No se encontraron errores recientes</p></div>';
                return;
            }
            
            let html = '';
            errors.forEach((error, index) => {
                html += `
                    <div class="error-item">
                        <div class="error-header">
                            <span class="error-level ${error.level.toLowerCase()}">${error.level}</span>
                            <span class="error-timestamp">${error.timestamp}</span>
                        </div>
                        <div class="error-message">${error.message}</div>
                        ${error.stack_trace ? `
                            <button class="toggle-stack" onclick="toggleStack(${index})">Ver Stack Trace</button>
                            <div class="error-stack" id="stack-${index}" style="display: none;">${error.stack_trace}</div>
                        ` : ''}
                    </div>
                `;
            });
            
            container.innerHTML = html;
        }
        
        function filterErrors() {
            const level = document.getElementById('level-filter').value;
            const limit = document.getElementById('limit-filter').value;
            
            const url = new URL(window.location);
            if (level) {
                url.searchParams.set('level', level);
            } else {
                url.searchParams.delete('level');
            }
            url.searchParams.set('limit', limit);
            
            window.location.href = url.toString();
        }
        
        function toggleStack(index) {
            const stack = document.getElementById(`stack-${index}`);
            const button = stack.previousElementSibling;
            
            if (stack.style.display === 'none') {
                stack.style.display = 'block';
                button.textContent = 'Ocultar Stack Trace';
            } else {
                stack.style.display = 'none';
                button.textContent = 'Ver Stack Trace';
            }
        }
        
        function toggleAutoRefresh() {
            const checkbox = document.getElementById('auto-refresh');
            
            if (checkbox.checked) {
                autoRefreshInterval = setInterval(refreshData, 30000);
            } else {
                if (autoRefreshInterval) {
                    clearInterval(autoRefreshInterval);
                    autoRefreshInterval = null;
                }
            }
        }
        
        function testNotification() {
            fetch('/system-debug/test-notification', { method: 'POST' })
                .then(response => response.json())
                .then(data => {
                    alert(data.message);
                })
                .catch(error => {
                    alert('Error al enviar notificación de prueba');
                });
        }
        
        function clearOldLogs() {
            if (confirm('¿Estás seguro de eliminar logs antiguos (más de 7 días)?')) {
                fetch('/system-debug/clear-logs', { method: 'POST' })
                    .then(response => response.json())
                    .then(data => {
                        alert(data.message);
                        if (data.success) {
                            refreshData();
                        }
                    })
                    .catch(error => {
                        alert('Error al limpiar logs');
                    });
            }
        }
    </script>
</body>
</html>