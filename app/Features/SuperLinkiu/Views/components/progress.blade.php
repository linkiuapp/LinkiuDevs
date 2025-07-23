@extends('shared::layouts.admin')

@section('title', 'Progress Bar')

@section('content')
<div class="grid sm:grid-cols-12 gap-6">
    <div class="col-span-12 sm:col-span-6">
        <!-- Progress por Defecto -->
        <div class="card">
            <div class="card-header">
                <h2 class="title-card">Progress por Defecto</h2>
            </div>
            <div class="p-6">
                <div class="flex items-center flex-col gap-6">
                    <div class="w-full bg-primary-50 rounded-full h-2">
                        <div class="bg-primary-200 h-2 rounded-full transition-all duration-300" style="width: 20%"></div>
                    </div>

                    <div class="w-full bg-primary-50 rounded-full h-2">
                        <div class="bg-primary-200 h-2 rounded-full transition-all duration-300" style="width: 35%"></div>
                    </div>

                    <div class="w-full bg-primary-50 rounded-full h-2">
                        <div class="bg-primary-200 h-2 rounded-full transition-all duration-300" style="width: 50%"></div>
                    </div>

                    <div class="w-full bg-primary-50 rounded-full h-2">
                        <div class="bg-primary-200 h-2 rounded-full transition-all duration-300" style="width: 75%"></div>
                    </div>

                    <div class="w-full bg-primary-50 rounded-full h-2">
                        <div class="bg-primary-200 h-2 rounded-full transition-all duration-300" style="width: 90%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-span-12 sm:col-span-6">
        <!-- Progress con Múltiples Colores -->
        <div class="card">
            <div class="card-header">
                <h2 class="title-card">Progress con Múltiples Colores</h2>
            </div>
            <div class="p-6">
                <div class="flex items-center flex-col gap-6">
                    <div class="w-full bg-primary-50 rounded-full h-2">
                        <div class="bg-primary-200 h-2 rounded-full transition-all duration-300" style="width: 20%"></div>
                    </div>

                    <div class="w-full bg-success-50 rounded-full h-2">
                        <div class="bg-success-200 h-2 rounded-full transition-all duration-300" style="width: 35%"></div>
                    </div>

                    <div class="w-full bg-info-50 rounded-full h-2">
                        <div class="bg-info-200 h-2 rounded-full transition-all duration-300" style="width: 50%"></div>
                    </div>

                    <div class="w-full bg-warning-50 rounded-full h-2">
                        <div class="bg-warning-200 h-2 rounded-full transition-all duration-300" style="width: 75%"></div>
                    </div>

                    <div class="w-full bg-error-50 rounded-full h-2">
                        <div class="bg-error-200 h-2 rounded-full transition-all duration-300" style="width: 90%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-span-12 sm:col-span-6">
        <!-- Progress con Etiqueta Derecha -->
        <div class="card">
            <div class="card-header">
                <h2 class="title-card">Progress con Etiqueta</h2>
            </div>
            <div class="p-6">
                <div class="flex flex-col gap-6">
                    <div class="flex items-center gap-4 w-full">
                        <div class="w-full bg-primary-50 rounded-full h-2">
                            <div class="bg-primary-400 h-2 rounded-full transition-all duration-300" style="width: 20%"></div>
                        </div>
                        <span class="text-black-400 text-base font-semibold leading-none min-w-[32px]">20%</span>
                    </div>

                    <div class="flex items-center gap-4 w-full">
                        <div class="w-full bg-primary-50 rounded-full h-2">
                            <div class="bg-primary-200 h-2 rounded-full transition-all duration-300" style="width: 35%"></div>
                        </div>
                        <span class="text-black-400 text-base font-semibold leading-none min-w-[32px]">35%</span>
                    </div>

                    <div class="flex items-center gap-4 w-full">
                        <div class="w-full bg-primary-50 rounded-full h-2">
                            <div class="bg-primary-200 h-2 rounded-full transition-all duration-300" style="width: 50%"></div>
                        </div>
                        <span class="text-black-400 text-base font-semibold leading-none min-w-[32px]">50%</span>
                    </div>

                    <div class="flex items-center gap-4 w-full">
                        <div class="w-full bg-primary-50 rounded-full h-2">
                            <div class="bg-primary-200 h-2 rounded-full transition-all duration-300" style="width: 75%"></div>
                        </div>
                        <span class="text-black-400 text-base font-semibold leading-none min-w-[32px]">75%</span>
                    </div>

                    <div class="flex items-center gap-4 w-full">
                        <div class="w-full bg-primary-50 rounded-full h-2">
                            <div class="bg-primary-200 h-2 rounded-full transition-all duration-300" style="width: 90%"></div>
                        </div>
                        <span class="text-black-400 text-base font-semibold leading-none min-w-[32px]">90%</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-span-12 sm:col-span-6">
        <!-- Progress Animado -->
        <div class="card">
            <div class="card-header">
                <h2 class="title-card">Progress Animado</h2>
            </div>
            <div class="p-6">
                <div class="flex items-center flex-col gap-6">
                    <div class="w-full bg-primary-50 rounded-full h-2 overflow-hidden">
                        <div class="bg-primary-200 h-2 rounded-full animate-pulse transition-all ease-out duration-1000" style="width: 20%"></div>
                    </div>

                    <div class="w-full bg-success-50 rounded-full h-2 overflow-hidden">
                        <div class="bg-success-200 h-2 rounded-full animate-pulse transition-all ease-out duration-1000" style="width: 35%"></div>
                    </div>

                    <div class="w-full bg-info-50 rounded-full h-2 overflow-hidden">
                        <div class="bg-info-200 h-2 rounded-full animate-pulse transition-all ease-out duration-1000" style="width: 50%"></div>
                    </div>

                    <div class="w-full bg-warning-50 rounded-full h-2 overflow-hidden">
                        <div class="bg-warning-200 h-2 rounded-full animate-pulse transition-all ease-out duration-1000" style="width: 75%"></div>
                    </div>

                    <div class="w-full bg-error-50 rounded-full h-2 overflow-hidden">
                        <div class="bg-error-200 h-2 rounded-full animate-pulse transition-all ease-out duration-1000" style="width: 90%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-span-12 sm:col-span-6">
        <!-- Progress con Gradiente -->
        <div class="card">
            <div class="card-header">
                <h2 class="title-card">Progress con Gradiente</h2>
            </div>
            <div class="p-6">
                <div class="flex items-center flex-col gap-6">
                    <div class="w-full bg-gradient-to-r from-primary-50 to-primary-100 rounded-full h-2 overflow-hidden">
                        <div class="bg-gradient-to-r from-primary-200 to-primary-300 h-2 rounded-full transition-all ease-out duration-1000" style="width: 20%"></div>
                    </div>

                    <div class="w-full bg-gradient-to-r from-success-50 to-success-100 rounded-full h-2 overflow-hidden">
                        <div class="bg-gradient-to-r from-success-200 to-success-300 h-2 rounded-full transition-all ease-out duration-1000" style="width: 35%"></div>
                    </div>

                    <div class="w-full bg-gradient-to-r from-info-50 to-info-100 rounded-full h-2 overflow-hidden">
                        <div class="bg-gradient-to-r from-info-200 to-info-300 h-2 rounded-full transition-all ease-out duration-1000" style="width: 50%"></div>
                    </div>

                    <div class="w-full bg-gradient-to-r from-warning-50 to-warning-100 rounded-full h-2 overflow-hidden">
                        <div class="bg-gradient-to-r from-warning-200 to-warning-300 h-2 rounded-full transition-all ease-out duration-1000" style="width: 75%"></div>
                    </div>

                    <div class="w-full bg-gradient-to-r from-error-50 to-error-100 rounded-full h-2 overflow-hidden">
                        <div class="bg-gradient-to-r from-error-200 to-error-300 h-2 rounded-full transition-all ease-out duration-1000" style="width: 90%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-span-12 sm:col-span-6">
        <!-- Progress con Tamaños -->
        <div class="card">
            <div class="card-header">
                <h2 class="title-card">Progress con Tamaños</h2>
            </div>
            <div class="p-6">
                <div class="flex items-center flex-col gap-6">
                    <div class="w-full">
                        <div class="mb-2">
                            <span class="text-base font-semibold text-black-300">Pequeño</span>
                        </div>
                        <div class="w-full bg-primary-50 rounded-full h-2">
                            <div class="bg-primary-200 h-2 rounded-full transition-all duration-300" style="width: 25%"></div>
                        </div>
                    </div>

                    <div class="w-full">
                        <div class="mb-2">
                            <span class="text-base font-semibold text-black-300">Mediano</span>
                        </div>
                        <div class="w-full bg-primary-50 rounded-full h-2">
                            <div class="bg-primary-200 h-2 rounded-full transition-all duration-300" style="width: 50%"></div>
                        </div>
                    </div>

                    <div class="w-full">
                        <div class="mb-2">
                            <span class="text-base font-semibold text-black-300">Grande</span>
                        </div>
                        <div class="w-full bg-primary-50 rounded-full h-2">
                            <div class="bg-primary-200 h-2 rounded-full transition-all duration-300" style="width: 75%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 