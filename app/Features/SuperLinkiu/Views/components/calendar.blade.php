@extends('shared::layouts.admin')

@section('title', 'Calendar')

@section('content')
<div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
    <div class="col-span-1">
        <!-- Calendario Básico -->
        <div class="card">
            <div class="card-header">
                <h2 class="title-card">Calendario Básico</h2>
            </div>
            <div class="card-body">
                <div class="max-w-md mx-auto">
                    <!-- Header del Calendario -->
                    <div class="flex items-center justify-between mb-4">
                        <button class="p-2 rounded-lg hover:bg-primary-50 hover:text-primary-300 text-black-400 transition-colors">
                            <x-solar-arrow-left-outline class="w-5 h-5" />
                        </button>
                        <h3 class="text-xl font-semibold text-black-500">Noviembre 2024</h3>
                        <button class="p-2 rounded-lg hover:bg-primary-50 hover:text-primary-300 text-black-400 transition-colors">
                            <x-solar-arrow-right-outline class="w-5 h-5" />
                        </button>
                    </div>

                    <!-- Días de la Semana -->
                    <div class="grid grid-cols-7 gap-1 mb-2">
                        <div class="text-center text-sm font-medium text-black-300 py-2">Dom</div>
                        <div class="text-center text-sm font-medium text-black-300 py-2">Lun</div>
                        <div class="text-center text-sm font-medium text-black-300 py-2">Mar</div>
                        <div class="text-center text-sm font-medium text-black-300 py-2">Mié</div>
                        <div class="text-center text-sm font-medium text-black-300 py-2">Jue</div>
                        <div class="text-center text-sm font-medium text-black-300 py-2">Vie</div>
                        <div class="text-center text-sm font-medium text-black-300 py-2">Sáb</div>
                    </div>

                    <!-- Días del Mes -->
                    <div class="grid grid-cols-7 gap-1">
                        <!-- Días del mes anterior -->
                        <div class="text-center text-sm text-black-200 py-2">27</div>
                        <div class="text-center text-sm text-black-200 py-2">28</div>
                        <div class="text-center text-sm text-black-200 py-2">29</div>
                        <div class="text-center text-sm text-black-200 py-2">30</div>
                        <div class="text-center text-sm text-black-200 py-2">31</div>
                        
                        <!-- Días del mes actual -->
                        <div class="text-center text-sm text-black-400 py-2 rounded-lg hover:bg-primary-50 hover:text-primary-300 cursor-pointer transition-colors">1</div>
                        <div class="text-center text-sm text-black-400 py-2 rounded-lg hover:bg-primary-50 hover:text-primary-300 cursor-pointer transition-colors">2</div>
                        <div class="text-center text-sm text-black-400 py-2 rounded-lg hover:bg-primary-50 hover:text-primary-300 cursor-pointer transition-colors">3</div>
                        <div class="text-center text-sm text-black-400 py-2 rounded-lg hover:bg-primary-50 hover:text-primary-300 cursor-pointer transition-colors">4</div>
                        <div class="text-center text-sm text-black-400 py-2 rounded-lg hover:bg-primary-50 hover:text-primary-300 cursor-pointer transition-colors">5</div>
                        <div class="text-center text-sm text-black-400 py-2 rounded-lg hover:bg-primary-50 hover:text-primary-300 cursor-pointer transition-colors">6</div>
                        <div class="text-center text-sm text-black-400 py-2 rounded-lg hover:bg-primary-50 hover:text-primary-300 cursor-pointer transition-colors">7</div>
                        <div class="text-center text-sm text-black-400 py-2 rounded-lg hover:bg-primary-50 hover:text-primary-300 cursor-pointer transition-colors">8</div>
                        <div class="text-center text-sm text-black-400 py-2 rounded-lg hover:bg-primary-50 hover:text-primary-300 cursor-pointer transition-colors">9</div>
                        <div class="text-center text-sm text-black-400 py-2 rounded-lg hover:bg-primary-50 hover:text-primary-300 cursor-pointer transition-colors">10</div>
                        <div class="text-center text-sm text-black-400 py-2 rounded-lg hover:bg-primary-50 hover:text-primary-300 cursor-pointer transition-colors">11</div>
                        <div class="text-center text-sm text-black-400 py-2 rounded-lg hover:bg-primary-50 hover:text-primary-300 cursor-pointer transition-colors">12</div>
                        <div class="text-center text-sm text-black-400 py-2 rounded-lg hover:bg-primary-50 hover:text-primary-300 cursor-pointer transition-colors">13</div>
                        <div class="text-center text-sm text-black-400 py-2 rounded-lg hover:bg-primary-50 hover:text-primary-300 cursor-pointer transition-colors">14</div>
                        
                        <!-- Día actual -->
                        <div class="text-center text-sm text-white-50 py-2 rounded-lg bg-primary-200 cursor-pointer">15</div>
                        
                        <!-- Resto del mes -->
                        <div class="text-center text-sm text-black-400 py-2 rounded-lg hover:bg-primary-50 hover:text-primary-300 cursor-pointer transition-colors">16</div>
                        <div class="text-center text-sm text-black-400 py-2 rounded-lg hover:bg-primary-50 hover:text-primary-300 cursor-pointer transition-colors">17</div>
                        <div class="text-center text-sm text-black-400 py-2 rounded-lg hover:bg-primary-50 hover:text-primary-300 cursor-pointer transition-colors">18</div>
                        <div class="text-center text-sm text-black-400 py-2 rounded-lg hover:bg-primary-50 hover:text-primary-300 cursor-pointer transition-colors">19</div>
                        <div class="text-center text-sm text-black-400 py-2 rounded-lg hover:bg-primary-50 hover:text-primary-300 cursor-pointer transition-colors">20</div>
                        <div class="text-center text-sm text-black-400 py-2 rounded-lg hover:bg-primary-50 hover:text-primary-300 cursor-pointer transition-colors">21</div>
                        <div class="text-center text-sm text-black-400 py-2 rounded-lg hover:bg-primary-50 hover:text-primary-300 cursor-pointer transition-colors">22</div>
                        <div class="text-center text-sm text-black-400 py-2 rounded-lg hover:bg-primary-50 hover:text-primary-300 cursor-pointer transition-colors">23</div>
                        <div class="text-center text-sm text-black-400 py-2 rounded-lg hover:bg-primary-50 hover:text-primary-300 cursor-pointer transition-colors">24</div>
                        <div class="text-center text-sm text-black-400 py-2 rounded-lg hover:bg-primary-50 hover:text-primary-300 cursor-pointer transition-colors">25</div>
                        <div class="text-center text-sm text-black-400 py-2 rounded-lg hover:bg-primary-50 hover:text-primary-300 cursor-pointer transition-colors">26</div>
                        <div class="text-center text-sm text-black-400 py-2 rounded-lg hover:bg-primary-50 hover:text-primary-300 cursor-pointer transition-colors">27</div>
                        <div class="text-center text-sm text-black-400 py-2 rounded-lg hover:bg-primary-50 hover:text-primary-300 cursor-pointer transition-colors">28</div>
                        <div class="text-center text-sm text-black-400 py-2 rounded-lg hover:bg-primary-50 hover:text-primary-300 cursor-pointer transition-colors">29</div>
                        <div class="text-center text-sm text-black-400 py-2 rounded-lg hover:bg-primary-50 hover:text-primary-300 cursor-pointer transition-colors">30</div>
                        
                        <!-- Días del mes siguiente -->
                        <div class="text-center text-sm text-black-200 py-2">1</div>
                        <div class="text-center text-sm text-black-200 py-2">2</div>
                        <div class="text-center text-sm text-black-200 py-2">3</div>
                        <div class="text-center text-sm text-black-200 py-2">4</div>
                        <div class="text-center text-sm text-black-200 py-2">5</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-span-1">
        <!-- Calendario con Eventos -->
        <div class="card">
            <div class="card-header">
                <h2 class="title-card">Calendario con Eventos</h2>
            </div>
            <div class="card-body">
                <div class="max-w-md mx-auto">
                    <!-- Header del Calendario -->
                    <div class="flex items-center justify-between mb-4">
                        <button class="p-2 rounded-lg hover:bg-primary-50 hover:text-primary-300 text-black-400 transition-colors">
                            <x-solar-arrow-left-outline class="w-5 h-5" />
                        </button>
                        <h3 class="text-xl font-semibold text-black-500">Noviembre 2024</h3>
                        <button class="p-2 rounded-lg hover:bg-primary-50 hover:text-primary-300 text-black-400 transition-colors">
                            <x-solar-arrow-right-outline class="w-5 h-5" />
                        </button>
                    </div>

                    <!-- Días de la Semana -->
                    <div class="grid grid-cols-7 gap-1 mb-2">
                        <div class="text-center text-sm font-medium text-black-300 py-2">Dom</div>
                        <div class="text-center text-sm font-medium text-black-300 py-2">Lun</div>
                        <div class="text-center text-sm font-medium text-black-300 py-2">Mar</div>
                        <div class="text-center text-sm font-medium text-black-300 py-2">Mié</div>
                        <div class="text-center text-sm font-medium text-black-300 py-2">Jue</div>
                        <div class="text-center text-sm font-medium text-black-300 py-2">Vie</div>
                        <div class="text-center text-sm font-medium text-black-300 py-2">Sáb</div>
                    </div>

                    <!-- Días del Mes con Eventos -->
                    <div class="grid grid-cols-7 gap-1">
                        <!-- Días del mes anterior -->
                        <div class="text-center text-sm text-black-200 py-2">27</div>
                        <div class="text-center text-sm text-black-200 py-2">28</div>
                        <div class="text-center text-sm text-black-200 py-2">29</div>
                        <div class="text-center text-sm text-black-200 py-2">30</div>
                        <div class="text-center text-sm text-black-200 py-2">31</div>
                        
                        <!-- Días con eventos -->
                        <div class="text-center text-sm text-black-400 py-2 rounded-lg hover:bg-primary-50 hover:text-primary-300 cursor-pointer transition-colors">1</div>
                        <div class="text-center text-sm text-black-400 py-2 rounded-lg hover:bg-primary-50 hover:text-primary-300 cursor-pointer transition-colors">2</div>
                        
                        <!-- Día con evento -->
                        <div class="text-center text-sm text-black-400 py-2 rounded-lg hover:bg-primary-50 hover:text-primary-300 cursor-pointer transition-colors relative">
                            3
                            <div class="w-1 h-1 bg-success-200 rounded-full absolute bottom-1 left-1/2 transform -translate-x-1/2"></div>
                        </div>
                        
                        <div class="text-center text-sm text-black-400 py-2 rounded-lg hover:bg-primary-50 hover:text-primary-300 cursor-pointer transition-colors">4</div>
                        <div class="text-center text-sm text-black-400 py-2 rounded-lg hover:bg-primary-50 hover:text-primary-300 cursor-pointer transition-colors">5</div>
                        <div class="text-center text-sm text-black-400 py-2 rounded-lg hover:bg-primary-50 hover:text-primary-300 cursor-pointer transition-colors">6</div>
                        <div class="text-center text-sm text-black-400 py-2 rounded-lg hover:bg-primary-50 hover:text-primary-300 cursor-pointer transition-colors">7</div>
                        
                        <!-- Día con múltiples eventos -->
                        <div class="text-center text-sm text-black-400 py-2 rounded-lg hover:bg-primary-50 hover:text-primary-300 cursor-pointer transition-colors relative">
                            8
                            <div class="flex gap-1 absolute bottom-1 left-1/2 transform -translate-x-1/2">
                                <div class="w-1 h-1 bg-success-200 rounded-full"></div>
                                <div class="w-1 h-1 bg-warning-200 rounded-full"></div>
                            </div>
                        </div>
                        
                        <div class="text-center text-sm text-black-400 py-2 rounded-lg hover:bg-primary-50 hover:text-primary-300 cursor-pointer transition-colors">9</div>
                        <div class="text-center text-sm text-black-400 py-2 rounded-lg hover:bg-primary-50 hover:text-primary-300 cursor-pointer transition-colors">10</div>
                        <div class="text-center text-sm text-black-400 py-2 rounded-lg hover:bg-primary-50 hover:text-primary-300 cursor-pointer transition-colors">11</div>
                        <div class="text-center text-sm text-black-400 py-2 rounded-lg hover:bg-primary-50 hover:text-primary-300 cursor-pointer transition-colors">12</div>
                        <div class="text-center text-sm text-black-400 py-2 rounded-lg hover:bg-primary-50 hover:text-primary-300 cursor-pointer transition-colors">13</div>
                        <div class="text-center text-sm text-black-400 py-2 rounded-lg hover:bg-primary-50 hover:text-primary-300 cursor-pointer transition-colors">14</div>
                        
                        <!-- Día actual -->
                        <div class="text-center text-sm text-white-50 py-2 rounded-lg bg-primary-200 cursor-pointer">15</div>
                        
                        <div class="text-center text-sm text-black-400 py-2 rounded-lg hover:bg-primary-50 hover:text-primary-300 cursor-pointer transition-colors">16</div>
                        <div class="text-center text-sm text-black-400 py-2 rounded-lg hover:bg-primary-50 hover:text-primary-300 cursor-pointer transition-colors">17</div>
                        <div class="text-center text-sm text-black-400 py-2 rounded-lg hover:bg-primary-50 hover:text-primary-300 cursor-pointer transition-colors">18</div>
                        <div class="text-center text-sm text-black-400 py-2 rounded-lg hover:bg-primary-50 hover:text-primary-300 cursor-pointer transition-colors">19</div>
                        
                        <!-- Día con evento urgente -->
                        <div class="text-center text-sm text-black-400 py-2 rounded-lg hover:bg-primary-50 hover:text-primary-300 cursor-pointer transition-colors relative">
                            20
                            <div class="w-1 h-1 bg-error-200 rounded-full absolute bottom-1 left-1/2 transform -translate-x-1/2"></div>
                        </div>
                        
                        <div class="text-center text-sm text-black-400 py-2 rounded-lg hover:bg-primary-50 hover:text-primary-300 cursor-pointer transition-colors">21</div>
                        <div class="text-center text-sm text-black-400 py-2 rounded-lg hover:bg-primary-50 hover:text-primary-300 cursor-pointer transition-colors">22</div>
                        <div class="text-center text-sm text-black-400 py-2 rounded-lg hover:bg-primary-50 hover:text-primary-300 cursor-pointer transition-colors">23</div>
                        <div class="text-center text-sm text-black-400 py-2 rounded-lg hover:bg-primary-50 hover:text-primary-300 cursor-pointer transition-colors">24</div>
                        <div class="text-center text-sm text-black-400 py-2 rounded-lg hover:bg-primary-50 hover:text-primary-300 cursor-pointer transition-colors">25</div>
                        <div class="text-center text-sm text-black-400 py-2 rounded-lg hover:bg-primary-50 hover:text-primary-300 cursor-pointer transition-colors">26</div>
                        <div class="text-center text-sm text-black-400 py-2 rounded-lg hover:bg-primary-50 hover:text-primary-300 cursor-pointer transition-colors">27</div>
                        <div class="text-center text-sm text-black-400 py-2 rounded-lg hover:bg-primary-50 hover:text-primary-300 cursor-pointer transition-colors">28</div>
                        <div class="text-center text-sm text-black-400 py-2 rounded-lg hover:bg-primary-50 hover:text-primary-300 cursor-pointer transition-colors">29</div>
                        <div class="text-center text-sm text-black-400 py-2 rounded-lg hover:bg-primary-50 hover:text-primary-300 cursor-pointer transition-colors">30</div>
                        
                        <!-- Días del mes siguiente -->
                        <div class="text-center text-sm text-black-200 py-2">1</div>
                        <div class="text-center text-sm text-black-200 py-2">2</div>
                        <div class="text-center text-sm text-black-200 py-2">3</div>
                        <div class="text-center text-sm text-black-200 py-2">4</div>
                        <div class="text-center text-sm text-black-200 py-2">5</div>
                    </div>

                    <!-- Leyenda -->
                    <div class="mt-4 pt-4 border-t border-white-100">
                        <h4 class="text-sm font-medium text-black-500 mb-2">Leyenda:</h4>
                        <div class="flex flex-wrap gap-4 text-xs">
                            <div class="flex items-center gap-1">
                                <div class="w-2 h-2 bg-success-200 rounded-full"></div>
                                <span class="text-black-400">Reunión</span>
                            </div>
                            <div class="flex items-center gap-1">
                                <div class="w-2 h-2 bg-warning-200 rounded-full"></div>
                                <span class="text-black-400">Tarea</span>
                            </div>
                            <div class="flex items-center gap-1">
                                <div class="w-2 h-2 bg-error-200 rounded-full"></div>
                                <span class="text-black-400">Urgente</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-span-1">
        <!-- Selector de Fecha -->
        <div class="card">
            <div class="card-header">
                <h2 class="title-card">Selector de Fecha</h2>
            </div>
            <div class="card-body">
                <div class="max-w-md mx-auto">
                    <div class="space-y-4">
                        <!-- Input de Fecha -->
                        <div>
                            <label class="block body-base text-black-500 font-medium mb-2">Fecha de Inicio</label>
                            <input type="date" class="w-full px-4 py-3 bg-white-50 border border-white-200 rounded-lg body-base text-black-400 focus:outline-none focus:border-primary-300 focus:ring-1 focus:ring-primary-300 transition-colors" value="2024-11-15">
                        </div>

                        <!-- Input de Fecha y Hora -->
                        <div>
                            <label class="block body-base text-black-500 font-medium mb-2">Fecha y Hora</label>
                            <input type="datetime-local" class="w-full px-4 py-3 bg-white-50 border border-white-200 rounded-lg body-base text-black-400 focus:outline-none focus:border-primary-300 focus:ring-1 focus:ring-primary-300 transition-colors" value="2024-11-15T10:30">
                        </div>

                        <!-- Rango de Fechas -->
                        <div>
                            <label class="block body-base text-black-500 font-medium mb-2">Rango de Fechas</label>
                            <div class="grid grid-cols-2 gap-3">
                                <input type="date" class="w-full px-4 py-3 bg-white-50 border border-white-200 rounded-lg body-base text-black-400 focus:outline-none focus:border-primary-300 focus:ring-1 focus:ring-primary-300 transition-colors" value="2024-11-15">
                                <input type="date" class="w-full px-4 py-3 bg-white-50 border border-white-200 rounded-lg body-base text-black-400 focus:outline-none focus:border-primary-300 focus:ring-1 focus:ring-primary-300 transition-colors" value="2024-11-30">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-span-1">
        <!-- Calendario Compacto -->
        <div class="card">
            <div class="card-header">
                <h2 class="title-card">Calendario Compacto</h2>
            </div>
            <div class="card-body">
                <div class="max-w-xs mx-auto">
                    <!-- Header del Calendario -->
                    <div class="flex items-center justify-between mb-3">
                        <button class="p-1 rounded hover:bg-primary-50 hover:text-primary-300 text-black-400 transition-colors">
                            <x-solar-arrow-left-outline class="w-4 h-4" />
                        </button>
                        <h3 class="text-lg font-semibold text-black-500">Nov 2024</h3>
                        <button class="p-1 rounded hover:bg-primary-50 hover:text-primary-300 text-black-400 transition-colors">
                            <x-solar-arrow-right-outline class="w-4 h-4" />
                        </button>
                    </div>

                    <!-- Días de la Semana -->
                    <div class="grid grid-cols-7 gap-1 mb-1">
                        <div class="text-center text-xs font-medium text-black-300 py-1">D</div>
                        <div class="text-center text-xs font-medium text-black-300 py-1">L</div>
                        <div class="text-center text-xs font-medium text-black-300 py-1">M</div>
                        <div class="text-center text-xs font-medium text-black-300 py-1">M</div>
                        <div class="text-center text-xs font-medium text-black-300 py-1">J</div>
                        <div class="text-center text-xs font-medium text-black-300 py-1">V</div>
                        <div class="text-center text-xs font-medium text-black-300 py-1">S</div>
                    </div>

                    <!-- Días del Mes -->
                    <div class="grid grid-cols-7 gap-1">
                        <!-- Semana 1 -->
                        <div class="text-center text-xs text-black-200 py-1">27</div>
                        <div class="text-center text-xs text-black-200 py-1">28</div>
                        <div class="text-center text-xs text-black-200 py-1">29</div>
                        <div class="text-center text-xs text-black-200 py-1">30</div>
                        <div class="text-center text-xs text-black-200 py-1">31</div>
                        <div class="text-center text-xs text-black-400 py-1 rounded hover:bg-primary-50 hover:text-primary-300 cursor-pointer transition-colors">1</div>
                        <div class="text-center text-xs text-black-400 py-1 rounded hover:bg-primary-50 hover:text-primary-300 cursor-pointer transition-colors">2</div>
                        
                        <!-- Semana 2 -->
                        <div class="text-center text-xs text-black-400 py-1 rounded hover:bg-primary-50 hover:text-primary-300 cursor-pointer transition-colors">3</div>
                        <div class="text-center text-xs text-black-400 py-1 rounded hover:bg-primary-50 hover:text-primary-300 cursor-pointer transition-colors">4</div>
                        <div class="text-center text-xs text-black-400 py-1 rounded hover:bg-primary-50 hover:text-primary-300 cursor-pointer transition-colors">5</div>
                        <div class="text-center text-xs text-black-400 py-1 rounded hover:bg-primary-50 hover:text-primary-300 cursor-pointer transition-colors">6</div>
                        <div class="text-center text-xs text-black-400 py-1 rounded hover:bg-primary-50 hover:text-primary-300 cursor-pointer transition-colors">7</div>
                        <div class="text-center text-xs text-black-400 py-1 rounded hover:bg-primary-50 hover:text-primary-300 cursor-pointer transition-colors">8</div>
                        <div class="text-center text-xs text-black-400 py-1 rounded hover:bg-primary-50 hover:text-primary-300 cursor-pointer transition-colors">9</div>
                        
                        <!-- Semana 3 -->
                        <div class="text-center text-xs text-black-400 py-1 rounded hover:bg-primary-50 hover:text-primary-300 cursor-pointer transition-colors">10</div>
                        <div class="text-center text-xs text-black-400 py-1 rounded hover:bg-primary-50 hover:text-primary-300 cursor-pointer transition-colors">11</div>
                        <div class="text-center text-xs text-black-400 py-1 rounded hover:bg-primary-50 hover:text-primary-300 cursor-pointer transition-colors">12</div>
                        <div class="text-center text-xs text-black-400 py-1 rounded hover:bg-primary-50 hover:text-primary-300 cursor-pointer transition-colors">13</div>
                        <div class="text-center text-xs text-black-400 py-1 rounded hover:bg-primary-50 hover:text-primary-300 cursor-pointer transition-colors">14</div>
                        <div class="text-center text-xs text-white-50 py-1 rounded bg-primary-200 cursor-pointer">15</div>
                        <div class="text-center text-xs text-black-400 py-1 rounded hover:bg-primary-50 hover:text-primary-300 cursor-pointer transition-colors">16</div>
                        
                        <!-- Semana 4 -->
                        <div class="text-center text-xs text-black-400 py-1 rounded hover:bg-primary-50 hover:text-primary-300 cursor-pointer transition-colors">17</div>
                        <div class="text-center text-xs text-black-400 py-1 rounded hover:bg-primary-50 hover:text-primary-300 cursor-pointer transition-colors">18</div>
                        <div class="text-center text-xs text-black-400 py-1 rounded hover:bg-primary-50 hover:text-primary-300 cursor-pointer transition-colors">19</div>
                        <div class="text-center text-xs text-black-400 py-1 rounded hover:bg-primary-50 hover:text-primary-300 cursor-pointer transition-colors">20</div>
                        <div class="text-center text-xs text-black-400 py-1 rounded hover:bg-primary-50 hover:text-primary-300 cursor-pointer transition-colors">21</div>
                        <div class="text-center text-xs text-black-400 py-1 rounded hover:bg-primary-50 hover:text-primary-300 cursor-pointer transition-colors">22</div>
                        <div class="text-center text-xs text-black-400 py-1 rounded hover:bg-primary-50 hover:text-primary-300 cursor-pointer transition-colors">23</div>
                        
                        <!-- Semana 5 -->
                        <div class="text-center text-xs text-black-400 py-1 rounded hover:bg-primary-50 hover:text-primary-300 cursor-pointer transition-colors">24</div>
                        <div class="text-center text-xs text-black-400 py-1 rounded hover:bg-primary-50 hover:text-primary-300 cursor-pointer transition-colors">25</div>
                        <div class="text-center text-xs text-black-400 py-1 rounded hover:bg-primary-50 hover:text-primary-300 cursor-pointer transition-colors">26</div>
                        <div class="text-center text-xs text-black-400 py-1 rounded hover:bg-primary-50 hover:text-primary-300 cursor-pointer transition-colors">27</div>
                        <div class="text-center text-xs text-black-400 py-1 rounded hover:bg-primary-50 hover:text-primary-300 cursor-pointer transition-colors">28</div>
                        <div class="text-center text-xs text-black-400 py-1 rounded hover:bg-primary-50 hover:text-primary-300 cursor-pointer transition-colors">29</div>
                        <div class="text-center text-xs text-black-400 py-1 rounded hover:bg-primary-50 hover:text-primary-300 cursor-pointer transition-colors">30</div>
                        
                        <!-- Días del mes siguiente -->
                        <div class="text-center text-xs text-black-200 py-1">1</div>
                        <div class="text-center text-xs text-black-200 py-1">2</div>
                        <div class="text-center text-xs text-black-200 py-1">3</div>
                        <div class="text-center text-xs text-black-200 py-1">4</div>
                        <div class="text-center text-xs text-black-200 py-1">5</div>
                        <div class="text-center text-xs text-black-200 py-1">6</div>
                        <div class="text-center text-xs text-black-200 py-1">7</div>
                    </div>

                    <!-- Fecha Seleccionada -->
                    <div class="mt-3 pt-3 border-t border-white-100">
                        <div class="text-center">
                            <div class="text-sm font-medium text-black-500">Fecha Seleccionada</div>
                            <div class="text-xs text-black-400">15 de Noviembre, 2024</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 