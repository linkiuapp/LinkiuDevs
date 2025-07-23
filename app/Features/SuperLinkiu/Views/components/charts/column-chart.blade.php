@extends('shared::layouts.admin')

@section('title', 'Column Chart')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="col-span-1">
        <!-- Gráfico de Columnas Básico -->
        <div class="card">
            <div class="card-header">
                <h2 class="title-card">Gráfico de Columnas Básico</h2>
            </div>
            <div class="card-body">
                <div class="space-y-4">
                    <!-- Datos del gráfico -->
                    <div class="flex items-end space-x-3 h-64 px-4">
                        <div class="flex flex-col items-center flex-1">
                            <div class="w-full bg-primary-300 rounded-t-lg mb-2 transition-all duration-300 hover:bg-primary-200" style="height: 60%"></div>
                            <span class="text-sm text-black-300">Ene</span>
                        </div>
                        <div class="flex flex-col items-center flex-1">
                            <div class="w-full bg-primary-300 rounded-t-lg mb-2 transition-all duration-300 hover:bg-primary-200" style="height: 80%"></div>
                            <span class="text-sm text-black-300">Feb</span>
                        </div>
                        <div class="flex flex-col items-center flex-1">
                            <div class="w-full bg-primary-300 rounded-t-lg mb-2 transition-all duration-300 hover:bg-primary-200" style="height: 45%"></div>
                            <span class="text-sm text-black-300">Mar</span>
                        </div>
                        <div class="flex flex-col items-center flex-1">
                            <div class="w-full bg-primary-300 rounded-t-lg mb-2 transition-all duration-300 hover:bg-primary-200" style="height: 90%"></div>
                            <span class="text-sm text-black-300">Abr</span>
                        </div>
                        <div class="flex flex-col items-center flex-1">
                            <div class="w-full bg-primary-300 rounded-t-lg mb-2 transition-all duration-300 hover:bg-primary-200" style="height: 75%"></div>
                            <span class="text-sm text-black-300">May</span>
                        </div>
                        <div class="flex flex-col items-center flex-1">
                            <div class="w-full bg-primary-300 rounded-t-lg mb-2 transition-all duration-300 hover:bg-primary-200" style="height: 55%"></div>
                            <span class="text-sm text-black-300">Jun</span>
                        </div>
                    </div>
                    
                    <!-- Estadísticas -->
                    <div class="border-t border-white-100 pt-4">
                        <div class="grid grid-cols-3 gap-4 text-center">
                            <div>
                                <div class="text-2xl font-semibold text-primary-300">$24K</div>
                                <div class="text-sm text-black-300">Total</div>
                            </div>
                            <div>
                                <div class="text-2xl font-semibold text-success-300">+12%</div>
                                <div class="text-sm text-black-300">Crecimiento</div>
                            </div>
                            <div>
                                <div class="text-2xl font-semibold text-black-500">6</div>
                                <div class="text-sm text-black-300">Meses</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-span-1">
        <!-- Gráfico de Columnas Múltiples -->
        <div class="card">
            <div class="card-header">
                <h2 class="title-card">Gráfico de Columnas Múltiples</h2>
            </div>
            <div class="card-body">
                <div class="space-y-4">
                    <!-- Leyenda -->
                    <div class="flex items-center justify-center space-x-6 mb-4">
                        <div class="flex items-center space-x-2">
                            <div class="w-3 h-3 bg-primary-300 rounded-full"></div>
                            <span class="text-sm text-black-300">Ventas</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="w-3 h-3 bg-secondary-300 rounded-full"></div>
                            <span class="text-sm text-black-300">Gastos</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="w-3 h-3 bg-success-300 rounded-full"></div>
                            <span class="text-sm text-black-300">Ganancia</span>
                        </div>
                    </div>
                    
                    <!-- Datos del gráfico -->
                    <div class="flex items-end space-x-2 h-64 px-4">
                        <div class="flex flex-col items-center flex-1">
                            <div class="flex space-x-1 mb-2 w-full">
                                <div class="flex-1 bg-primary-300 rounded-t transition-all duration-300 hover:bg-primary-200" style="height: 80px"></div>
                                <div class="flex-1 bg-secondary-300 rounded-t transition-all duration-300 hover:bg-secondary-200" style="height: 60px"></div>
                                <div class="flex-1 bg-success-300 rounded-t transition-all duration-300 hover:bg-success-200" style="height: 45px"></div>
                            </div>
                            <span class="text-sm text-black-300">Ene</span>
                        </div>
                        <div class="flex flex-col items-center flex-1">
                            <div class="flex space-x-1 mb-2 w-full">
                                <div class="flex-1 bg-primary-300 rounded-t transition-all duration-300 hover:bg-primary-200" style="height: 100px"></div>
                                <div class="flex-1 bg-secondary-300 rounded-t transition-all duration-300 hover:bg-secondary-200" style="height: 70px"></div>
                                <div class="flex-1 bg-success-300 rounded-t transition-all duration-300 hover:bg-success-200" style="height: 55px"></div>
                            </div>
                            <span class="text-sm text-black-300">Feb</span>
                        </div>
                        <div class="flex flex-col items-center flex-1">
                            <div class="flex space-x-1 mb-2 w-full">
                                <div class="flex-1 bg-primary-300 rounded-t transition-all duration-300 hover:bg-primary-200" style="height: 75px"></div>
                                <div class="flex-1 bg-secondary-300 rounded-t transition-all duration-300 hover:bg-secondary-200" style="height: 50px"></div>
                                <div class="flex-1 bg-success-300 rounded-t transition-all duration-300 hover:bg-success-200" style="height: 65px"></div>
                            </div>
                            <span class="text-sm text-black-300">Mar</span>
                        </div>
                        <div class="flex flex-col items-center flex-1">
                            <div class="flex space-x-1 mb-2 w-full">
                                <div class="flex-1 bg-primary-300 rounded-t transition-all duration-300 hover:bg-primary-200" style="height: 120px"></div>
                                <div class="flex-1 bg-secondary-300 rounded-t transition-all duration-300 hover:bg-secondary-200" style="height: 80px"></div>
                                <div class="flex-1 bg-success-300 rounded-t transition-all duration-300 hover:bg-success-200" style="height: 70px"></div>
                            </div>
                            <span class="text-sm text-black-300">Abr</span>
                        </div>
                        <div class="flex flex-col items-center flex-1">
                            <div class="flex space-x-1 mb-2 w-full">
                                <div class="flex-1 bg-primary-300 rounded-t transition-all duration-300 hover:bg-primary-200" style="height: 95px"></div>
                                <div class="flex-1 bg-secondary-300 rounded-t transition-all duration-300 hover:bg-secondary-200" style="height: 65px"></div>
                                <div class="flex-1 bg-success-300 rounded-t transition-all duration-300 hover:bg-success-200" style="height: 85px"></div>
                            </div>
                            <span class="text-sm text-black-300">May</span>
                        </div>
                        <div class="flex flex-col items-center flex-1">
                            <div class="flex space-x-1 mb-2 w-full">
                                <div class="flex-1 bg-primary-300 rounded-t transition-all duration-300 hover:bg-primary-200" style="height: 85px"></div>
                                <div class="flex-1 bg-secondary-300 rounded-t transition-all duration-300 hover:bg-secondary-200" style="height: 55px"></div>
                                <div class="flex-1 bg-success-300 rounded-t transition-all duration-300 hover:bg-success-200" style="height: 75px"></div>
                            </div>
                            <span class="text-sm text-black-300">Jun</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-span-1">
        <!-- Gráfico de Columnas Apiladas -->
        <div class="card">
            <div class="card-header">
                <h2 class="title-card">Gráfico de Columnas Apiladas</h2>
            </div>
            <div class="card-body">
                <div class="space-y-4">
                    <!-- Leyenda -->
                    <div class="flex items-center justify-center space-x-6 mb-4">
                        <div class="flex items-center space-x-2">
                            <div class="w-3 h-3 bg-primary-300 rounded-full"></div>
                            <span class="text-sm text-black-300">Desktop</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="w-3 h-3 bg-info-300 rounded-full"></div>
                            <span class="text-sm text-black-300">Mobile</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="w-3 h-3 bg-warning-300 rounded-full"></div>
                            <span class="text-sm text-black-300">Tablet</span>
                        </div>
                    </div>
                    
                    <!-- Datos del gráfico -->
                    <div class="flex items-end space-x-3 h-64 px-4">
                        <div class="flex flex-col items-center flex-1">
                            <div class="w-full mb-2 rounded-lg overflow-hidden" style="height: 150px">
                                <div class="w-full bg-primary-300 transition-all duration-300" style="height: 60%"></div>
                                <div class="w-full bg-info-300 transition-all duration-300" style="height: 25%"></div>
                                <div class="w-full bg-warning-300 transition-all duration-300" style="height: 15%"></div>
                            </div>
                            <span class="text-sm text-black-300">Ene</span>
                        </div>
                        <div class="flex flex-col items-center flex-1">
                            <div class="w-full mb-2 rounded-lg overflow-hidden" style="height: 180px">
                                <div class="w-full bg-primary-300 transition-all duration-300" style="height: 55%"></div>
                                <div class="w-full bg-info-300 transition-all duration-300" style="height: 30%"></div>
                                <div class="w-full bg-warning-300 transition-all duration-300" style="height: 15%"></div>
                            </div>
                            <span class="text-sm text-black-300">Feb</span>
                        </div>
                        <div class="flex flex-col items-center flex-1">
                            <div class="w-full mb-2 rounded-lg overflow-hidden" style="height: 120px">
                                <div class="w-full bg-primary-300 transition-all duration-300" style="height: 50%"></div>
                                <div class="w-full bg-info-300 transition-all duration-300" style="height: 35%"></div>
                                <div class="w-full bg-warning-300 transition-all duration-300" style="height: 15%"></div>
                            </div>
                            <span class="text-sm text-black-300">Mar</span>
                        </div>
                        <div class="flex flex-col items-center flex-1">
                            <div class="w-full mb-2 rounded-lg overflow-hidden" style="height: 200px">
                                <div class="w-full bg-primary-300 transition-all duration-300" style="height: 45%"></div>
                                <div class="w-full bg-info-300 transition-all duration-300" style="height: 40%"></div>
                                <div class="w-full bg-warning-300 transition-all duration-300" style="height: 15%"></div>
                            </div>
                            <span class="text-sm text-black-300">Abr</span>
                        </div>
                        <div class="flex flex-col items-center flex-1">
                            <div class="w-full mb-2 rounded-lg overflow-hidden" style="height: 170px">
                                <div class="w-full bg-primary-300 transition-all duration-300" style="height: 50%"></div>
                                <div class="w-full bg-info-300 transition-all duration-300" style="height: 35%"></div>
                                <div class="w-full bg-warning-300 transition-all duration-300" style="height: 15%"></div>
                            </div>
                            <span class="text-sm text-black-300">May</span>
                        </div>
                        <div class="flex flex-col items-center flex-1">
                            <div class="w-full mb-2 rounded-lg overflow-hidden" style="height: 160px">
                                <div class="w-full bg-primary-300 transition-all duration-300" style="height: 55%"></div>
                                <div class="w-full bg-info-300 transition-all duration-300" style="height: 30%"></div>
                                <div class="w-full bg-warning-300 transition-all duration-300" style="height: 15%"></div>
                            </div>
                            <span class="text-sm text-black-300">Jun</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-span-1">
        <!-- Gráfico de Columnas con Gradiente -->
        <div class="card">
            <div class="card-header">
                <h2 class="title-card">Gráfico con Gradiente</h2>
            </div>
            <div class="card-body">
                <div class="space-y-4">
                    <!-- Datos del gráfico -->
                    <div class="flex items-end space-x-3 h-64 px-4">
                        <div class="flex flex-col items-center flex-1">
                            <div class="w-full bg-gradient-to-t from-primary-300 to-primary-200 rounded-t-lg mb-2 transition-all duration-300 hover:from-primary-300 hover:to-primary-100" style="height: 65%"></div>
                            <span class="text-sm text-black-300">Ene</span>
                        </div>
                        <div class="flex flex-col items-center flex-1">
                            <div class="w-full bg-gradient-to-t from-success-300 to-success-200 rounded-t-lg mb-2 transition-all duration-300 hover:from-success-300 hover:to-success-100" style="height: 85%"></div>
                            <span class="text-sm text-black-300">Feb</span>
                        </div>
                        <div class="flex flex-col items-center flex-1">
                            <div class="w-full bg-gradient-to-t from-info-300 to-info-200 rounded-t-lg mb-2 transition-all duration-300 hover:from-info-300 hover:to-info-100" style="height: 50%"></div>
                            <span class="text-sm text-black-300">Mar</span>
                        </div>
                        <div class="flex flex-col items-center flex-1">
                            <div class="w-full bg-gradient-to-t from-warning-300 to-warning-200 rounded-t-lg mb-2 transition-all duration-300 hover:from-warning-300 hover:to-warning-100" style="height: 95%"></div>
                            <span class="text-sm text-black-300">Abr</span>
                        </div>
                        <div class="flex flex-col items-center flex-1">
                            <div class="w-full bg-gradient-to-t from-secondary-300 to-secondary-200 rounded-t-lg mb-2 transition-all duration-300 hover:from-secondary-300 hover:to-secondary-100" style="height: 80%"></div>
                            <span class="text-sm text-black-300">May</span>
                        </div>
                        <div class="flex flex-col items-center flex-1">
                            <div class="w-full bg-gradient-to-t from-error-300 to-error-200 rounded-t-lg mb-2 transition-all duration-300 hover:from-error-300 hover:to-error-100" style="height: 60%"></div>
                            <span class="text-sm text-black-300">Jun</span>
                        </div>
                    </div>
                    
                    <!-- Información adicional -->
                    <div class="border-t border-white-100 pt-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                <x-solar-chart-outline class="w-5 h-5 text-primary-300" />
                                <span class="text-sm text-black-500 font-medium">Rendimiento por mes</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <span class="text-sm text-success-300 font-medium">+15.3%</span>
                                <x-solar-arrow-up-outline class="w-4 h-4 text-success-300" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 