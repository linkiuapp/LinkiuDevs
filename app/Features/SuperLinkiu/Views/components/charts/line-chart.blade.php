@extends('shared::layouts.admin')

@section('title', 'Line Chart')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="col-span-1">
        <!-- Gráfico de Líneas Básico -->
        <div class="card">
            <div class="card-header">
                <h2 class="title-card">Gráfico de Líneas Básico</h2>
            </div>
            <div class="card-body">
                <div class="space-y-4">
                    <!-- Gráfico -->
                    <div class="relative h-64 bg-accent-100 rounded-lg p-4">
                        <svg class="w-full h-full" viewBox="0 0 400 200" preserveAspectRatio="xMidYMid meet">
                            <defs>
                                <pattern id="grid" width="40" height="20" patternUnits="userSpaceOnUse">
                                    <path d="M 40 0 L 0 0 0 20" fill="none" stroke="#e5e7eb" stroke-width="1"/>
                                </pattern>
                            </defs>
                            <rect width="100%" height="100%" fill="url(#grid)"/>
                            <!-- Línea principal -->
                            <polyline
                                fill="none"
                                stroke="#350692" <!--primary-300 -->
                                stroke-width="3"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                points="40,140 80,100 120,120 160,80 200,90 240,60 280,70 320,40 360,50"
                            />
                            <!-- Puntos de datos -->
                            <circle cx="40" cy="140" r="4" fill="#350692" class="hover:r-6 transition-all"/>
                            <circle cx="80" cy="100" r="4" fill="#350692" class="hover:r-6 transition-all"/>
                            <circle cx="120" cy="120" r="4" fill="#350692" class="hover:r-6 transition-all"/>
                            <circle cx="160" cy="80" r="4" fill="#350692" class="hover:r-6 transition-all"/>
                            <circle cx="200" cy="90" r="4" fill="#350692" class="hover:r-6 transition-all"/>
                            <circle cx="240" cy="60" r="4" fill="#350692" class="hover:r-6 transition-all"/>
                            <circle cx="280" cy="70" r="4" fill="#350692" class="hover:r-6 transition-all"/>
                            <circle cx="320" cy="40" r="4" fill="#350692" class="hover:r-6 transition-all"/>
                            <circle cx="360" cy="50" r="4" fill="#350692" class="hover:r-6 transition-all"/>
                        </svg>
                        <!-- Etiquetas del eje X -->
                        <div class="absolute bottom-0 left-0 right-0 flex justify-between px-4 text-xs text-black-300">
                            <span>Ene</span>
                            <span>Feb</span>
                            <span>Mar</span>
                            <span>Abr</span>
                            <span>May</span>
                            <span>Jun</span>
                            <span>Jul</span>
                            <span>Ago</span>
                            <span>Sep</span>
                        </div>
                    </div>
                    <!-- Estadísticas -->
                    <div class="border-t border-accent-100 pt-4">
                        <div class="grid grid-cols-3 gap-4 text-center">
                            <div>
                                <div class="text-2xl font-semibold text-primary-300">42.5K</div>
                                <div class="text-sm text-black-300">Usuarios</div>
                            </div>
                            <div>
                                <div class="text-2xl font-semibold text-success-300">+8.2%</div>
                                <div class="text-sm text-black-300">Crecimiento</div>
                            </div>
                            <div>
                                <div class="text-2xl font-semibold text-black-500">9</div>
                                <div class="text-sm text-black-300">Meses</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-span-1">
        <!-- Gráfico de Líneas Múltiples -->
        <div class="card">
            <div class="card-header">
                <h2 class="title-card">Gráfico de Líneas Múltiples</h2>
            </div>
            <div class="card-body">
                <div class="space-y-4">
                    <!-- Leyenda -->
                    <div class="flex items-center justify-center space-x-6 mb-4">
                        <div class="flex items-center space-x-2">
                            <div class="w-3 h-1 bg-primary-300 rounded-full"></div>
                            <span class="text-sm text-black-300">Ventas</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="w-3 h-1 bg-success-300 rounded-full"></div>
                            <span class="text-sm text-black-300">Leads</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="w-3 h-1 bg-warning-300 rounded-full"></div>
                            <span class="text-sm text-black-300">Conversiones</span>
                        </div>
                    </div>
                    
                    <!-- Gráfico -->
                    <div class="relative h-64 bg-accent-100 rounded-lg p-4">
                        <svg class="w-full h-full" viewBox="0 0 400 200" preserveAspectRatio="xMidYMid meet">
                            <!-- Grid lines -->
                            <rect width="100%" height="100%" fill="url(#grid)"/>
                            
                            <!-- Línea 1 - Ventas -->
                            <polyline
                                fill="none"
                                stroke="#6b46c1"
                                stroke-width="3"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                points="40,140 80,100 120,120 160,80 200,90 240,60 280,70 320,40 360,50"
                            />
                            
                            <!-- Línea 2 - Leads -->
                            <polyline
                                fill="none"
                                stroke="#10b981"
                                stroke-width="3"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                points="40,160 80,130 120,140 160,110 200,120 240,90 280,100 320,70 360,80"
                            />
                            
                            <!-- Línea 3 - Conversiones -->
                            <polyline
                                fill="none"
                                stroke="#f59e0b"
                                stroke-width="3"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                points="40,180 80,160 120,170 160,150 200,155 240,130 280,140 320,110 360,120"
                            />
                            
                            <!-- Puntos de la primera línea -->
                            <circle cx="40" cy="140" r="3" fill="#6b46c1"/>
                            <circle cx="80" cy="100" r="3" fill="#6b46c1"/>
                            <circle cx="120" cy="120" r="3" fill="#6b46c1"/>
                            <circle cx="160" cy="80" r="3" fill="#6b46c1"/>
                            <circle cx="200" cy="90" r="3" fill="#6b46c1"/>
                            <circle cx="240" cy="60" r="3" fill="#6b46c1"/>
                            <circle cx="280" cy="70" r="3" fill="#6b46c1"/>
                            <circle cx="320" cy="40" r="3" fill="#6b46c1"/>
                            <circle cx="360" cy="50" r="3" fill="#6b46c1"/>
                            
                            <!-- Puntos de la segunda línea -->
                            <circle cx="40" cy="160" r="3" fill="#10b981"/>
                            <circle cx="80" cy="130" r="3" fill="#10b981"/>
                            <circle cx="120" cy="140" r="3" fill="#10b981"/>
                            <circle cx="160" cy="110" r="3" fill="#10b981"/>
                            <circle cx="200" cy="120" r="3" fill="#10b981"/>
                            <circle cx="240" cy="90" r="3" fill="#10b981"/>
                            <circle cx="280" cy="100" r="3" fill="#10b981"/>
                            <circle cx="320" cy="70" r="3" fill="#10b981"/>
                            <circle cx="360" cy="80" r="3" fill="#10b981"/>
                            
                            <!-- Puntos de la tercera línea -->
                            <circle cx="40" cy="180" r="3" fill="#f59e0b"/>
                            <circle cx="80" cy="160" r="3" fill="#f59e0b"/>
                            <circle cx="120" cy="170" r="3" fill="#f59e0b"/>
                            <circle cx="160" cy="150" r="3" fill="#f59e0b"/>
                            <circle cx="200" cy="155" r="3" fill="#f59e0b"/>
                            <circle cx="240" cy="130" r="3" fill="#f59e0b"/>
                            <circle cx="280" cy="140" r="3" fill="#f59e0b"/>
                            <circle cx="320" cy="110" r="3" fill="#f59e0b"/>
                            <circle cx="360" cy="120" r="3" fill="#f59e0b"/>
                        </svg>
                        
                        <!-- Etiquetas del eje X -->
                        <div class="absolute bottom-0 left-0 right-0 flex justify-between px-4 text-xs text-black-300">
                            <span>Ene</span>
                            <span>Feb</span>
                            <span>Mar</span>
                            <span>Abr</span>
                            <span>May</span>
                            <span>Jun</span>
                            <span>Jul</span>
                            <span>Ago</span>
                            <span>Sep</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-span-1">
        <!-- Gráfico de Área -->
        <div class="card">
            <div class="card-header">
                <h2 class="title-card">Gráfico de Área</h2>
            </div>
            <div class="card-body">
                <div class="space-y-4">
                    <!-- Gráfico -->
                    <div class="relative h-64 bg-accent-100 rounded-lg p-4">
                        <svg class="w-full h-full" viewBox="0 0 400 200" preserveAspectRatio="xMidYMid meet">
                            <!-- Grid lines -->
                            <rect width="100%" height="100%" fill="url(#grid)"/>
                            
                            <!-- Área rellena -->
                            <defs>
                                <linearGradient id="areaGradient" x1="0%" y1="0%" x2="0%" y2="100%">
                                    <stop offset="0%" style="stop-color:#6b46c1;stop-opacity:0.3" />
                                    <stop offset="100%" style="stop-color:#6b46c1;stop-opacity:0.05" />
                                </linearGradient>
                            </defs>
                            
                            <path
                                d="M40,140 L80,100 L120,120 L160,80 L200,90 L240,60 L280,70 L320,40 L360,50 L360,200 L40,200 Z"
                                fill="url(#areaGradient)"
                            />
                            
                            <!-- Línea superior -->
                            <polyline
                                fill="none"
                                stroke="#6b46c1"
                                stroke-width="3"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                points="40,140 80,100 120,120 160,80 200,90 240,60 280,70 320,40 360,50"
                            />
                            
                            <!-- Puntos de datos -->
                            <circle cx="40" cy="140" r="4" fill="#ffffff" stroke="#6b46c1" stroke-width="2"/>
                            <circle cx="80" cy="100" r="4" fill="#ffffff" stroke="#6b46c1" stroke-width="2"/>
                            <circle cx="120" cy="120" r="4" fill="#ffffff" stroke="#6b46c1" stroke-width="2"/>
                            <circle cx="160" cy="80" r="4" fill="#ffffff" stroke="#6b46c1" stroke-width="2"/>
                            <circle cx="200" cy="90" r="4" fill="#ffffff" stroke="#6b46c1" stroke-width="2"/>
                            <circle cx="240" cy="60" r="4" fill="#ffffff" stroke="#6b46c1" stroke-width="2"/>
                            <circle cx="280" cy="70" r="4" fill="#ffffff" stroke="#6b46c1" stroke-width="2"/>
                            <circle cx="320" cy="40" r="4" fill="#ffffff" stroke="#6b46c1" stroke-width="2"/>
                            <circle cx="360" cy="50" r="4" fill="#ffffff" stroke="#6b46c1" stroke-width="2"/>
                        </svg>
                        
                        <!-- Etiquetas del eje X -->
                        <div class="absolute bottom-0 left-0 right-0 flex justify-between px-4 text-xs text-black-300">
                            <span>Ene</span>
                            <span>Feb</span>
                            <span>Mar</span>
                            <span>Abr</span>
                            <span>May</span>
                            <span>Jun</span>
                            <span>Jul</span>
                            <span>Ago</span>
                            <span>Sep</span>
                        </div>
                    </div>
                    
                    <!-- Información adicional -->
                    <div class="border-t border-accent-100 pt-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                <x-solar-graph-up-outline class="w-5 h-5 text-primary-300" />
                                <span class="text-sm text-black-500 font-medium">Tendencia ascendente</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <span class="text-sm text-success-300 font-medium">+24.7%</span>
                                <x-solar-arrow-up-outline class="w-4 h-4 text-success-300" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-span-1">
        <!-- Gráfico de Líneas con Puntos -->
        <div class="card">
            <div class="card-header">
                <h2 class="title-card">Gráfico con Puntos Destacados</h2>
            </div>
            <div class="card-body">
                <div class="space-y-4">
                    <!-- Controles -->
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center space-x-4">
                            <button class="px-3 py-1 bg-primary-100 text-primary-300 rounded-lg text-sm hover:bg-primary-200 transition-colors">
                                <x-solar-calendar-outline class="w-4 h-4 inline mr-1" />
                                Último mes
                            </button>
                            <button class="px-3 py-1 bg-accent-100 text-black-300 rounded-lg text-sm hover:bg-accent-200 transition-colors">
                                Último año
                            </button>
                        </div>
                        <div class="flex items-center space-x-2">
                            <x-solar-download-outline class="w-4 h-4 text-black-300 cursor-pointer hover:text-primary-300 transition-colors" />
                            <x-solar-printer-outline class="w-4 h-4 text-black-300 cursor-pointer hover:text-primary-300 transition-colors" />
                        </div>
                    </div>
                    
                    <!-- Gráfico -->
                    <div class="relative h-64 bg-accent-100 rounded-lg p-4">
                        <svg class="w-full h-full" viewBox="0 0 400 200" preserveAspectRatio="xMidYMid meet">
                            <!-- Grid lines -->
                            <rect width="100%" height="100%" fill="url(#grid)"/>
                            
                            <!-- Línea principal -->
                            <polyline
                                fill="none"
                                stroke="#10b981"
                                stroke-width="3"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                points="40,140 80,100 120,120 160,80 200,90 240,60 280,70 320,40 360,50"
                            />
                            
                            <!-- Puntos normales -->
                            <circle cx="40" cy="140" r="5" fill="#10b981" class="opacity-80"/>
                            <circle cx="80" cy="100" r="5" fill="#10b981" class="opacity-80"/>
                            <circle cx="120" cy="120" r="5" fill="#10b981" class="opacity-80"/>
                            <circle cx="200" cy="90" r="5" fill="#10b981" class="opacity-80"/>
                            <circle cx="240" cy="60" r="5" fill="#10b981" class="opacity-80"/>
                            <circle cx="280" cy="70" r="5" fill="#10b981" class="opacity-80"/>
                            <circle cx="360" cy="50" r="5" fill="#10b981" class="opacity-80"/>
                            
                            <!-- Puntos destacados -->
                            <circle cx="160" cy="80" r="8" fill="#f59e0b" stroke="#ffffff" stroke-width="2" class="animate-pulse"/>
                            <circle cx="320" cy="40" r="8" fill="#ef4444" stroke="#ffffff" stroke-width="2" class="animate-pulse"/>
                        </svg>
                        
                        <!-- Etiquetas del eje X -->
                        <div class="absolute bottom-0 left-0 right-0 flex justify-between px-4 text-xs text-black-300">
                            <span>Ene</span>
                            <span>Feb</span>
                            <span>Mar</span>
                            <span>Abr</span>
                            <span>May</span>
                            <span>Jun</span>
                            <span>Jul</span>
                            <span>Ago</span>
                            <span>Sep</span>
                        </div>
                        
                        <!-- Tooltips para puntos destacados -->
                        <div class="absolute top-12 left-32 bg-warning-100 text-warning-800 px-2 py-1 rounded text-xs">
                            Pico: 1,250
                        </div>
                        <div class="absolute top-6 right-20 bg-error-100 text-error-800 px-2 py-1 rounded text-xs">
                            Máximo: 1,890
                        </div>
                    </div>
                    
                    <!-- Leyenda de puntos -->
                    <div class="flex items-center justify-center space-x-6 text-sm">
                        <div class="flex items-center space-x-2">
                            <div class="w-3 h-3 bg-success-300 rounded-full"></div>
                            <span class="text-black-300">Valores normales</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="w-3 h-3 bg-warning-300 rounded-full"></div>
                            <span class="text-black-300">Picos</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="w-3 h-3 bg-error-300 rounded-full"></div>
                            <span class="text-black-300">Máximos</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 