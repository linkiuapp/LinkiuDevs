@extends('shared::layouts.admin')

@section('title', 'Switch')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">

    <div class="md:col-span-1 col-span-12">
        <!-- Switch con Colores -->
        <div class="card">
            <div class="card-header">
                <h2 class="title-card">Switch con Colores</h2>
            </div>
            <div class="p-6">
                <div class="flex items-center flex-wrap gap-6">
                    <div class="flex items-center gap-3">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" value="" class="sr-only peer" checked>
                            <div class="w-11 h-6 bg-black-100 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white-50 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white-50 after:border-white-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-300"></div>
                        </label>
                        <span class="text-base font-semibold text-black-300">Primary</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" value="" class="sr-only peer" checked>
                            <div class="w-11 h-6 bg-black-100 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white-50 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white-50 after:border-white-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-secondary-200"></div>
                        </label>
                        <span class="text-base font-semibold text-black-300">Secondary</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" value="" class="sr-only peer" checked>
                            <div class="w-11 h-6 bg-black-100 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white-50 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white-50 after:border-white-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-success-200"></div>
                        </label>
                        <span class="text-base font-semibold text-black-300">Success</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" value="" class="sr-only peer" checked>
                            <div class="w-11 h-6 bg-black-100 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white-50 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white-50 after:border-white-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-warning-200"></div>
                        </label>
                        <span class="text-base font-semibold text-black-300">Warning</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="md:col-span-1 col-span-12">
        <!-- Switch con Tamaños -->
        <div class="card">
            <div class="card-header">
                <h2 class="title-card">Switch con Tamaños</h2>
            </div>
            <div class="p-6">
                <div class="flex items-center flex-wrap gap-6">
                    <div class="flex items-center gap-3">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" value="" class="sr-only peer" checked>
                            <div class="w-7 h-4 bg-black-100 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white-50 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white-50 after:border-white-300 after:border after:rounded-full after:h-3 after:w-3 after:transition-all peer-checked:bg-primary-200"></div>
                        </label>
                        <span class="text-sm font-semibold text-black-300">Pequeño</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" value="" class="sr-only peer" checked>
                            <div class="w-11 h-6 bg-black-100 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white-50 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white-50 after:border-white-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-200"></div>
                        </label>
                        <span class="text-2xs font-semibold text-black-300">Mediano</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" value="" class="sr-only peer" checked>
                            <div class="w-14 h-7 bg-black-100 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white-50 after:content-[''] after:absolute after:top-[2px] after:left-[5px] after:bg-white-50 after:border-white-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-primary-200"></div>
                        </label>
                        <span class="text-base font-semibold text-black-300">Grande</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="md:col-span-1 col-span-12">
        <!-- Switch Deshabilitado -->
        <div class="card">
            <div class="card-header">
                <h2 class="title-card">Switch Deshabilitado</h2>
            </div>
            <div class="p-6">
                <div class="flex items-center flex-wrap gap-6">
                    <div class="flex items-center gap-3">
                        <label class="relative inline-flex items-center cursor-not-allowed opacity-50">
                            <input type="checkbox" value="" class="sr-only peer" checked disabled>
                            <div class="w-11 h-6 bg-black-100 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white-50 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white-50 after:border-white-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-200"></div>
                        </label>
                        <span class="text-base font-semibold text-black-300 opacity-50">Switch Activo Deshabilitado</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <label class="relative inline-flex items-center cursor-not-allowed opacity-50">
                            <input type="checkbox" value="" class="sr-only peer" disabled>
                            <div class="w-11 h-6 bg-black-100 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white-50 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white-50 after:border-white-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-200"></div>
                        </label>
                        <span class="text-base font-semibold text-black-300 opacity-50">Switch Inactivo Deshabilitado</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="md:col-span-1 col-span-12">
        <!-- Switch con Texto -->
        <div class="card">
            <div class="card-header">
                <h2 class="title-card">Switch con Texto</h2>
            </div>
            <div class="p-6">
                <div class="flex items-center flex-wrap gap-6">
                    <div class="flex items-center gap-3">
                        <span class="body-base text-black-400">Off</span>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" value="" class="sr-only peer" checked>
                            <div class="w-11 h-6 bg-black-100 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white-50 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white-50 after:border-white-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-200"></div>
                        </label>
                        <span class="text-base font-semibold text-black-300">On</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="body-base text-black-400">Inactivo</span>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" value="" class="sr-only peer" checked>
                            <div class="w-11 h-6 bg-black-100 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white-50 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white-50 after:border-white-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-success-200"></div>
                        </label>
                        <span class="text-base font-semibold text-black-300">Activo</span>
                    </div>

                    <div class="flex items-center gap-3">
                        <span class="body-base text-black-400">No verificado</span>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" value="" class="sr-only peer" checked>
                            <div class="w-11 h-6 bg-black-100 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white-50 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white-50 after:border-white-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-info-200"></div>
                        </label>
                        <span class="text-base font-semibold text-black-300">Verificado</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="md:col-span-1 col-span-12">
        <!-- Switch con Iconos -->
        <div class="card">
            <div class="card-header">
                <h2 class="title-card">Switch con Iconos</h2>
            </div>
            <div class="p-6">
                <div class="flex items-center flex-wrap gap-6">
                    <div class="flex items-center gap-3">
                        <x-solar-sun-outline class="w-5 h-5 text-warning-300" />
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" value="" class="sr-only peer" checked>
                            <div class="w-11 h-6 bg-black-100 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white-50 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white-50 after:border-white-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-200"></div>
                        </label>
                        <x-solar-moon-outline class="w-5 h-5 text-primary-400" />
                    </div>
                    <div class="flex items-center gap-3">
                        <x-solar-volume-cross-outline class="w-5 h-5 text-error-400" />
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" value="" class="sr-only peer" checked>
                            <div class="w-11 h-6 bg-black-100 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white-50 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white-50 after:border-white-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-success-200"></div>
                        </label>
                        <x-solar-volume-loud-outline class="w-5 h-5 text-success-400" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 