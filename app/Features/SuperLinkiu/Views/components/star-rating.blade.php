@extends('shared::layouts.admin')

@section('title', 'Star Rating')

@section('content')
<div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
    <div class="sm:col-span-1 col-span-12">
        <!-- Star Rating por Defecto -->
        <div class="card">
            <div class="card-header">
                <h2 class="title-card">Star Rating por Defecto</h2>
            </div>
            <div class="p-6">
                <ul class="flex flex-wrap items-center gap-3">
                    <li class="text-warning-200 text-[32px] leading-none">
                        <x-solar-star-bold class="w-8 h-8" />
                    </li>
                    <li class="text-warning-200 text-[32px] leading-none">
                        <x-solar-star-bold class="w-8 h-8" />
                    </li>
                    <li class="text-warning-200 text-[32px] leading-none">
                        <x-solar-star-bold class="w-8 h-8" />
                    </li>
                    <li class="text-warning-200 text-[32px] leading-none">
                        <x-solar-star-bold class="w-8 h-8" />
                    </li>
                    <li class="text-warning-200 text-[32px] leading-none">
                        <x-solar-star-bold class="w-8 h-8" />
                    </li>
                </ul>
                <div class="mt-4">
                    <span class="text-base font-semibold text-black-300">5.0 de 5 estrellas</span>
                </div>
            </div>
        </div>
    </div>

    <div class="sm:col-span-1 col-span-12">
        <!-- Rating Parcial -->
        <div class="card">
            <div class="card-header">
                <h2 class="title-card">Rating Parcial</h2>
            </div>
            <div class="p-6">
                <ul class="flex flex-wrap items-center gap-3">
                    <li class="text-warning-200 text-[32px] leading-none">
                        <x-solar-star-bold class="w-8 h-8" />
                    </li>
                    <li class="text-warning-200 text-[32px] leading-none">
                        <x-solar-star-bold class="w-8 h-8" />
                    </li>
                    <li class="text-warning-200 text-[32px] leading-none">
                        <x-solar-star-bold class="w-8 h-8" />
                    </li>
                    <li class="text-warning-200 text-[32px] leading-none">
                        <x-solar-star-bold class="w-8 h-8" />
                    </li>
                    <li class="text-black-100 text-[32px] leading-none">
                        <x-solar-star-outline class="w-8 h-8" />
                    </li>
                </ul>
                <div class="mt-4">
                    <span class="text-base font-semibold text-black-300">4.0 de 5 estrellas</span>
                </div>
            </div>
        </div>
    </div>

    <div class="sm:col-span-1 col-span-12">
        <!-- Rating con Colores -->
        <div class="card">
            <div class="card-header">
                <h2 class="title-card">Rating con Colores</h2>
            </div>
            <div class="p-6">
                <ul class="flex flex-wrap items-center gap-3">
                    <li class="text-primary-200 text-[32px] leading-none">
                        <x-solar-star-bold class="w-8 h-8" />
                    </li>
                    <li class="text-secondary-200 text-[32px] leading-none">
                        <x-solar-star-bold class="w-8 h-8" />
                    </li>
                    <li class="text-success-200 text-[32px] leading-none">
                        <x-solar-star-bold class="w-8 h-8" />
                    </li>
                    <li class="text-info-200 text-[32px] leading-none">
                        <x-solar-star-bold class="w-8 h-8" />
                    </li>
                    <li class="text-error-200 text-[32px] leading-none">
                        <x-solar-star-bold class="w-8 h-8" />
                    </li>
                </ul>
                <div class="mt-4">
                    <span class="text-base font-semibold text-black-300">Rating multicolor</span>
                </div>
            </div>
        </div>
    </div>

    <div class="sm:col-span-1 col-span-12">
        <!-- Rating Interactivo -->
        <div class="card">
            <div class="card-header">
                <h2 class="title-card">Rating Interactivo</h2>
            </div>
            <div class="p-6">
                <ul class="flex flex-wrap items-center gap-3">
                    <li class="text-warning-200 text-[32px] leading-none cursor-pointer hover:text-warning-300 transition-colors">
                        <x-solar-star-bold class="w-8 h-8" />
                    </li>
                    <li class="text-warning-200 text-[32px] leading-none cursor-pointer hover:text-warning-300 transition-colors">
                        <x-solar-star-bold class="w-8 h-8" />
                    </li>
                    <li class="text-black-100 text-[32px] leading-none cursor-pointer hover:text-warning-300 transition-colors">
                        <x-solar-star-outline class="w-8 h-8" />
                    </li>
                    <li class="text-black-100 text-[32px] leading-none cursor-pointer hover:text-warning-300 transition-colors">
                        <x-solar-star-outline class="w-8 h-8" />
                    </li>
                    <li class="text-black-100 text-[32px] leading-none cursor-pointer hover:text-warning-300 transition-colors">
                        <x-solar-star-outline class="w-8 h-8" />
                    </li>
                </ul>
                <div class="mt-4">
                    <span class="text-base font-semibold text-black-300">2.0 de 5 estrellas</span>
                </div>
            </div>
        </div>
    </div>

    <div class="sm:col-span-1 col-span-12">
        <!-- Rating con Tamaños -->
        <div class="card">
            <div class="card-header">
                <h2 class="title-card">Rating con Tamaños</h2>
            </div>
            <div class="p-6 space-y-4">
                <!-- Pequeño -->
                <div>
                    <ul class="flex flex-wrap items-center gap-1 mb-2">
                        <li class="text-warning-200 leading-none">
                            <x-solar-star-bold class="w-4 h-4" />
                        </li>
                        <li class="text-warning-200 leading-none">
                            <x-solar-star-bold class="w-4 h-4" />
                        </li>
                        <li class="text-warning-200 leading-none">
                            <x-solar-star-bold class="w-4 h-4" />
                        </li>
                        <li class="text-warning-200 leading-none">
                            <x-solar-star-bold class="w-4 h-4" />
                        </li>
                        <li class="text-warning-200 leading-none">
                            <x-solar-star-bold class="w-4 h-4" />
                        </li>
                    </ul>
                    <span class="text-sm font-semibold text-black-300">Pequeño</span>
                </div>

                <!-- Mediano -->
                <div>
                    <ul class="flex flex-wrap items-center gap-2 mb-2">
                        <li class="text-warning-200 leading-none">
                            <x-solar-star-bold class="w-6 h-6" />
                        </li>
                        <li class="text-warning-200 leading-none">
                            <x-solar-star-bold class="w-6 h-6" />
                        </li>
                        <li class="text-warning-200 leading-none">
                            <x-solar-star-bold class="w-6 h-6" />
                        </li>
                        <li class="text-warning-200 leading-none">
                            <x-solar-star-bold class="w-6 h-6" />
                        </li>
                        <li class="text-black-100 leading-none">
                            <x-solar-star-outline class="w-6 h-6" />
                        </li>
                    </ul>
                    <span class="text-2xs font-semibold text-black-300">Mediano</span>
                </div>

                <!-- Grande -->
                <div>
                    <ul class="flex flex-wrap items-center gap-3 mb-2">
                        <li class="text-warning-200 leading-none">
                            <x-solar-star-bold class="w-10 h-10" />
                        </li>
                        <li class="text-warning-200 leading-none">
                            <x-solar-star-bold class="w-10 h-10" />
                        </li>
                        <li class="text-warning-200 leading-none">
                            <x-solar-star-bold class="w-10 h-10" />
                        </li>
                        <li class="text-black-100 leading-none">
                            <x-solar-star-outline class="w-10 h-10" />
                        </li>
                        <li class="text-black-100 leading-none">
                            <x-solar-star-outline class="w-10 h-10" />
                        </li>
                    </ul>
                    <span class="text-base font-semibold text-black-300">Grande</span>
                </div>
            </div>
        </div>
    </div>

    <div class="sm:col-span-1 col-span-12">
        <!-- Rating con Texto -->
        <div class="card">
            <div class="card-header">
                <h2 class="title-card">Rating con Texto</h2>
            </div>
            <div class="p-6 space-y-4">
                <div class="flex items-center gap-4">
                    <ul class="flex flex-wrap items-center gap-2">
                        <li class="text-warning-200 leading-none">
                            <x-solar-star-bold class="w-6 h-6" />
                        </li>
                        <li class="text-warning-200 leading-none">
                            <x-solar-star-bold class="w-6 h-6" />
                        </li>
                        <li class="text-warning-200 leading-none">
                            <x-solar-star-bold class="w-6 h-6" />
                        </li>
                        <li class="text-warning-200 leading-none">
                            <x-solar-star-bold class="w-6 h-6" />
                        </li>
                        <li class="text-warning-200 leading-none">
                            <x-solar-star-bold class="w-6 h-6" />
                        </li>
                    </ul>
                    <div class="flex flex-col">
                        <span class="text-base font-semibold text-black-300">Excelente</span>
                        <span class="text-sm font-semibold text-black-300">5.0 de 5 estrellas</span>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <ul class="flex flex-wrap items-center gap-2">
                        <li class="text-warning-200 leading-none">
                            <x-solar-star-bold class="w-6 h-6" />
                        </li>
                        <li class="text-warning-200 leading-none">
                            <x-solar-star-bold class="w-6 h-6" />
                        </li>
                            <li class="text-warning-200 leading-none">
                            <x-solar-star-bold class="w-6 h-6" />
                        </li>
                        <li class="text-warning-200 leading-none">
                            <x-solar-star-bold class="w-6 h-6" />
                        </li>
                        <li class="text-black-100 leading-none">
                            <x-solar-star-outline class="w-6 h-6" />
                        </li>
                    </ul>
                    <div class="flex flex-col">
                        <span class="text-base font-semibold text-black-300">Muy bueno</span>
                        <span class="text-sm font-semibold text-black-300">4.0 de 5 estrellas</span>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <ul class="flex flex-wrap items-center gap-2">
                        <li class="text-warning-200 leading-none">
                            <x-solar-star-bold class="w-6 h-6" />
                        </li>
                        <li class="text-warning-200 leading-none">
                            <x-solar-star-bold class="w-6 h-6" />
                        </li>
                        <li class="text-warning-200 leading-none">
                            <x-solar-star-bold class="w-6 h-6" />
                        </li>
                        <li class="text-black-100 leading-none">
                            <x-solar-star-outline class="w-6 h-6" />
                        </li>
                        <li class="text-black-100 leading-none">
                            <x-solar-star-outline class="w-6 h-6" />
                        </li>
                    </ul>
                    <div class="flex flex-col">
                        <span class="text-base font-semibold text-black-300">Bueno</span>
                        <span class="text-sm font-semibold text-black-300">3.0 de 5 estrellas</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 