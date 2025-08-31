@extends('shared::layouts.admin')

@section('title', 'Pie Chart')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="col-span-1">
        <!-- Gráfico Donut Básico -->
        <div class="card">
            <div class="card-header">
                <h2 class="title-card">Gráfico Donut Básico</h2>
            </div>
            <div class="card-body">
                <div class="flex flex-col lg:flex-row items-center gap-6">
                    <!-- Gráfico -->
                    <div class="relative flex-shrink-0">
                        <svg class="w-48 h-48" viewBox="0 0 200 200">
                            <!-- Fondo del donut -->
                            <circle
                                cx="100"
                                cy="100"
                                r="80"
                                fill="none"
                                stroke="#F1EAFF" <!-- primary-50 -->
                                stroke-width="30"
                            />
                            <!-- Segmento 1 - 40% -->
                            <circle
                                cx="100"
                                cy="100"
                                r="80"
                                fill="none"
                                stroke="#350692" <!-- primary-300 -->
                                stroke-width="30"
                                stroke-dasharray="201 502"
                                stroke-dashoffset="0"
                                transform="rotate(-90 100 100)"
                                class="transition-all duration-300 hover:stroke-width-35"
                            />
                            <!-- Segmento 2 - 30% -->
                            <circle
                                cx="100"
                                cy="100"
                                r="80"
                                fill="none"
                                stroke="#007945" <!-- success-300 -->
                                stroke-width="30"
                                stroke-dasharray="151 352"
                                stroke-dashoffset="-201"
                                transform="rotate(-90 100 100)"
                                class="transition-all duration-300 hover:stroke-width-35"
                            />
                            <!-- Segmento 3 - 20% -->
                            <circle
                                cx="100"
                                cy="100"
                                r="80"
                                fill="none"
                                stroke="#997500" <!-- warning-300 -->
                                stroke-width="30"
                                stroke-dasharray="100 403"
                                stroke-dashoffset="-352"
                                transform="rotate(-90 100 100)"
                                class="transition-all duration-300 hover:stroke-width-35"
                            />
                            <!-- Segmento 4 - 10% -->
                            <circle
                                cx="100"
                                cy="100"
                                r="80"
                                fill="none"
                                stroke="#90000B" <!-- error-300 -->
                                stroke-width="30"
                                stroke-dasharray="50 453"
                                stroke-dashoffset="-452"
                                transform="rotate(-90 100 100)"
                                class="transition-all duration-300 hover:stroke-width-35"
                            />
                        </svg>
                        <!-- Centro del donut -->
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <div class="text-lg text-black-300 font-medium">Total</div>
                            <div class="text-3xl font-semibold text-black-500">72</div>
                        </div>
                    </div>
                    <!-- Leyenda -->
                    <div class="flex-1 space-y-3">
                        <div class="flex items-center justify-between border-b border-accent-100 pb-2">
                            <span class="text-sm font-medium text-black-300">Categoría</span>
                            <span class="text-sm font-medium text-black-300">Valor</span>
                            <span class="text-sm font-medium text-black-300">%</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-3 h-3 bg-primary-300 rounded-full"></div>
                                <span class="text-sm text-black-400">Categoría 1</span>
                            </div>
                            <span class="text-sm text-black-500 font-medium">29</span>
                            <span class="text-sm text-black-400">40%</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-3 h-3 bg-success-300 rounded-full"></div>
                                <span class="text-sm text-black-400">Categoría 2</span>
                            </div>
                            <span class="text-sm text-black-500 font-medium">22</span>
                            <span class="text-sm text-black-400">30%</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-3 h-3 bg-warning-300 rounded-full"></div>
                                <span class="text-sm text-black-400">Categoría 3</span>
                            </div>
                            <span class="text-sm text-black-500 font-medium">14</span>
                            <span class="text-sm text-black-400">20%</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-3 h-3 bg-error-300 rounded-full"></div>
                                <span class="text-sm text-black-400">Categoría 4</span>
                            </div>
                            <span class="text-sm text-black-500 font-medium">7</span>
                            <span class="text-sm text-black-400">10%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-span-1">
        <!-- Gráfico Pie Completo -->
        <div class="bg-accent-50 rounded-lg p-0 overflow-hidden">
            <div class="border-b border-accent-100 bg-accent-50 py-4 px-6">
                <h2 class="text-3xl text-black-500 mb-0">Gráfico Pie Completo</h2>
            </div>
            <div class="p-6">
                <div class="flex flex-col items-center space-y-4">
                    <!-- Gráfico -->
                    <div class="relative">
                        <svg class="w-48 h-48" viewBox="0 0 200 200">
                            <!-- Segmento 1 - 35% -->
                            <path
                                d="M 100 100 L 100 20 A 80 80 0 0 1 156.56 56.56 Z"
                                fill="#6b46c1"
                                stroke="#ffffff"
                                stroke-width="2"
                                class="transition-all duration-300 hover:fill-opacity-80 cursor-pointer"
                            />
                            
                            <!-- Segmento 2 - 25% -->
                            <path
                                d="M 100 100 L 156.56 56.56 A 80 80 0 0 1 180 100 Z"
                                fill="#10b981"
                                stroke="#ffffff"
                                stroke-width="2"
                                class="transition-all duration-300 hover:fill-opacity-80 cursor-pointer"
                            />
                            
                            <!-- Segmento 3 - 20% -->
                            <path
                                d="M 100 100 L 180 100 A 80 80 0 0 1 143.44 143.44 Z"
                                fill="#f59e0b"
                                stroke="#ffffff"
                                stroke-width="2"
                                class="transition-all duration-300 hover:fill-opacity-80 cursor-pointer"
                            />
                            
                            <!-- Segmento 4 - 20% -->
                            <path
                                d="M 100 100 L 143.44 143.44 A 80 80 0 0 1 100 20 Z"
                                fill="#ef4444"
                                stroke="#ffffff"
                                stroke-width="2"
                                class="transition-all duration-300 hover:fill-opacity-80 cursor-pointer"
                            />
                        </svg>
                    </div>
                    
                    <!-- Leyenda horizontal -->
                    <div class="grid grid-cols-2 gap-4 w-full">
                        <div class="flex items-center space-x-2">
                            <div class="w-4 h-4 bg-primary-400 rounded"></div>
                            <div>
                                <div class="text-sm font-medium text-black-500">Móvil</div>
                                <div class="text-xs text-black-300">35%</div>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="w-4 h-4 bg-success-400 rounded"></div>
                            <div>
                                <div class="text-sm font-medium text-black-500">Desktop</div>
                                <div class="text-xs text-black-300">25%</div>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="w-4 h-4 bg-warning-300 rounded"></div>
                            <div>
                                <div class="text-sm font-medium text-black-500">Tablet</div>
                                <div class="text-xs text-black-300">20%</div>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="w-4 h-4 bg-error-400 rounded"></div>
                            <div>
                                <div class="text-sm font-medium text-black-500">Otros</div>
                                <div class="text-xs text-black-300">20%</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Estadísticas adicionales -->
                    <div class="border-t border-accent-100 pt-4 w-full">
                        <div class="grid grid-cols-3 gap-4 text-center">
                            <div>
                                <div class="text-lg font-semibold text-primary-400">15.2K</div>
                                <div class="text-xs text-black-300">Total Usuarios</div>
                            </div>
                            <div>
                                <div class="text-lg font-semibold text-success-400">+5.3%</div>
                                <div class="text-xs text-black-300">Vs. Anterior</div>
                            </div>
                            <div>
                                <div class="text-lg font-semibold text-black-500">4</div>
                                <div class="text-xs text-black-300">Dispositivos</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-span-1">
        <!-- Gráfico Donut con Gradiente -->
        <div class="bg-accent-50 rounded-lg p-0 overflow-hidden">
            <div class="border-b border-accent-100 bg-accent-50 py-4 px-6">
                <h2 class="text-3xl text-black-500 mb-0">Donut con Gradiente</h2>
            </div>
            <div class="p-6">
                <div class="flex flex-col items-center space-y-6">
                    <!-- Gráfico -->
                    <div class="relative">
                        <svg class="w-48 h-48" viewBox="0 0 200 200">
                            <!-- Definir gradientes -->
                            <defs>
                                <linearGradient id="gradient1" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" style="stop-color:#6b46c1;stop-opacity:1" />
                                    <stop offset="100%" style="stop-color:#8b5cf6;stop-opacity:1" />
                                </linearGradient>
                                <linearGradient id="gradient2" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" style="stop-color:#10b981;stop-opacity:1" />
                                    <stop offset="100%" style="stop-color:#34d399;stop-opacity:1" />
                                </linearGradient>
                                <linearGradient id="gradient3" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" style="stop-color:#f59e0b;stop-opacity:1" />
                                    <stop offset="100%" style="stop-color:#fbbf24;stop-opacity:1" />
                                </linearGradient>
                            </defs>
                            
                            <!-- Fondo del donut -->
                            <circle
                                cx="100"
                                cy="100"
                                r="80"
                                fill="none"
                                stroke="#f3f4f6"
                                stroke-width="25"
                            />
                            
                            <!-- Segmento 1 - 50% -->
                            <circle
                                cx="100"
                                cy="100"
                                r="80"
                                fill="none"
                                stroke="url(#gradient1)"
                                stroke-width="25"
                                stroke-dasharray="251 502"
                                stroke-dashoffset="0"
                                transform="rotate(-90 100 100)"
                                class="transition-all duration-500"
                            />
                            
                            <!-- Segmento 2 - 30% -->
                            <circle
                                cx="100"
                                cy="100"
                                r="80"
                                fill="none"
                                stroke="url(#gradient2)"
                                stroke-width="25"
                                stroke-dasharray="151 352"
                                stroke-dashoffset="-251"
                                transform="rotate(-90 100 100)"
                                class="transition-all duration-500"
                            />
                            
                            <!-- Segmento 3 - 20% -->
                            <circle
                                cx="100"
                                cy="100"
                                r="80"
                                fill="none"
                                stroke="url(#gradient3)"
                                stroke-width="25"
                                stroke-dasharray="100 403"
                                stroke-dashoffset="-402"
                                transform="rotate(-90 100 100)"
                                class="transition-all duration-500"
                            />
                        </svg>
                        
                        <!-- Centro del donut -->
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <div class="text-2xl font-bold text-primary-400">89%</div>
                            <div class="text-sm text-black-300">Completado</div>
                        </div>
                    </div>
                    
                    <!-- Controles -->
                    <div class="flex items-center space-x-4">
                        <button class="px-4 py-2 bg-primary-100 text-primary-400 rounded-lg text-sm hover:bg-primary-200 transition-colors">
                            <x-solar-refresh-outline class="w-4 h-4 inline mr-2" />
                            Actualizar
                        </button>
                        <button class="px-4 py-2 bg-accent-100 text-black-400 rounded-lg text-sm hover:bg-accent-200 transition-colors">
                            <x-solar-export-outline class="w-4 h-4 inline mr-2" />
                            Exportar
                        </button>
                    </div>
                    
                    <!-- Métricas -->
                    <div class="grid grid-cols-3 gap-4 w-full text-center">
                        <div>
                            <div class="text-lg font-semibold text-primary-400">50%</div>
                            <div class="text-xs text-black-300">Proceso A</div>
                        </div>
                        <div>
                            <div class="text-lg font-semibold text-success-400">30%</div>
                            <div class="text-xs text-black-300">Proceso B</div>
                        </div>
                        <div>
                            <div class="text-lg font-semibold text-warning-300">20%</div>
                            <div class="text-xs text-black-300">Proceso C</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-span-1">
        <!-- Gráfico Semi-círculo -->
        <div class="bg-accent-50 rounded-lg p-0 overflow-hidden">
            <div class="border-b border-accent-100 bg-accent-50 py-4 px-6">
                <h2 class="text-3xl text-black-500 mb-0">Gráfico Semi-círculo</h2>
            </div>
            <div class="p-6">
                <div class="flex flex-col items-center space-y-6">
                    <!-- Gráfico -->
                    <div class="relative">
                        <svg class="w-48 h-32" viewBox="0 0 200 100">
                            <!-- Fondo del semi-círculo -->
                            <path
                                d="M 20 100 A 80 80 0 0 1 180 100"
                                fill="none"
                                stroke="#f3f4f6"
                                stroke-width="20"
                                stroke-linecap="round"
                            />
                            
                            <!-- Progreso del semi-círculo -->
                            <path
                                d="M 20 100 A 80 80 0 0 1 180 100"
                                fill="none"
                                stroke="#6b46c1"
                                stroke-width="20"
                                stroke-linecap="round"
                                stroke-dasharray="251"
                                stroke-dashoffset="75"
                                class="transition-all duration-1000"
                            />
                        </svg>
                        
                        <!-- Valor central -->
                        <div class="absolute bottom-4 left-1/2 transform -translate-x-1/2 text-center">
                            <div class="text-2xl font-bold text-primary-400">70%</div>
                            <div class="text-sm text-black-300">Rendimiento</div>
                        </div>
                    </div>
                    
                    <!-- Indicadores -->
                    <div class="flex items-center justify-between w-full">
                        <div class="text-center">
                            <div class="text-sm text-black-300">Mínimo</div>
                            <div class="text-lg font-semibold text-error-400">0%</div>
                        </div>
                        <div class="text-center">
                            <div class="text-sm text-black-300">Objetivo</div>
                            <div class="text-lg font-semibold text-success-400">85%</div>
                        </div>
                        <div class="text-center">
                            <div class="text-sm text-black-300">Máximo</div>
                            <div class="text-lg font-semibold text-black-500">100%</div>
                        </div>
                    </div>
                    
                    <!-- Estado -->
                    <div class="flex items-center justify-center space-x-2 w-full">
                        <div class="flex items-center space-x-1">
                            <x-solar-check-circle-outline class="w-5 h-5 text-success-400" />
                            <span class="text-sm text-success-400 font-medium">Por encima del promedio</span>
                        </div>
                    </div>
                    
                    <!-- Acciones -->
                    <div class="flex items-center space-x-3 w-full justify-center">
                        <button class="px-3 py-1.5 bg-primary-400 text-accent-50 rounded-lg text-sm hover:bg-primary-500 transition-colors">
                            Ver detalles
                        </button>
                        <button class="px-3 py-1.5 bg-accent-100 text-black-400 rounded-lg text-sm hover:bg-accent-200 transition-colors">
                            Comparar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 