@extends('shared::layouts.admin')

@section('title', 'Tags')

@section('content')
<div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
    <div class="md:col-span-1 col-span-12">
        <!-- Tags por Defecto -->
        <div class="card">
            <div class="card-header">
                <h2 class="title-card">Tags por Defecto</h2>
            </div>
            <div class="card-body">
                <ul class="flex flex-wrap items-center gap-4 mb-6">
                    <li class="tag-default">Label</li>
                    <li class="tag-default">UI/UX</li>
                    <li class="tag-default">Frontend</li>
                    <li class="tag-default">Backend</li>
                </ul>
                <ul class="flex flex-wrap items-center gap-3">
                    <li class="tag-default">
                        Design
                        <button class="remove-tag" type="button">
                            <x-solar-close-circle-outline class="w-4 h-4" />
                        </button>
                    </li>
                    <li class="tag-default">
                        Development
                        <button class="remove-tag" type="button">
                            <x-solar-close-circle-outline class="w-4 h-4" />
                        </button>
                    </li>
                    <li class="tag-default">
                        Marketing
                        <button class="remove-tag" type="button">
                            <x-solar-close-circle-outline class="w-4 h-4" />
                        </button>
                    </li>
                    <li class="tag-default">
                        Analytics
                        <button class="remove-tag" type="button">
                            <x-solar-close-circle-outline class="w-4 h-4" />
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="md:col-span-1 col-span-12">
        <!-- Tags con Colores -->
        <div class="card">
            <div class="card-header">
                <h2 class="title-card">Tags con Colores</h2>
            </div>
            <div class="card-body">
                <ul class="flex flex-wrap items-center gap-4 mb-6">
                    <li class="tag-color-primary">Primary</li>
                    <li class="tag-color-secondary">Secondary</li>
                    <li class="tag-color-success">Success</li>
                    <li class="tag-color-warning">Warning</li>
                </ul>
                <ul class="flex flex-wrap items-center gap-3">
                    <li class="tag-color-primary">
                        Primary
                        <button class="remove-tag-solid" type="button">
                            <x-solar-close-circle-outline class="w-4 h-4" />
                        </button>
                    </li>
                    <li class="tag-color-secondary">
                        Secondary
                        <button class="remove-tag-solid" type="button">
                            <x-solar-close-circle-outline class="w-4 h-4" />
                        </button>
                    </li>
                    <li class="tag-color-success">
                        Success
                        <button class="remove-tag-solid" type="button">
                            <x-solar-close-circle-outline class="w-4 h-4" />
                        </button>
                    </li>
                    <li class="tag-color-warning">
                        Warning
                        <button class="remove-tag-solid" type="button">
                            <x-solar-close-circle-outline class="w-4 h-4" />
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="md:col-span-1 col-span-12">
        <!-- Tags con Indicador -->
        <div class="card">
            <div class="card-header">
                <h2 class="title-card">Tags con Indicador</h2>
            </div>
            <div class="card-body">
                <ul class="flex flex-wrap items-center gap-4 mb-6">
                    <li class="tag-indicator-success">
                        <span class="w-2 h-2 bg-success-300 rounded-full"></span>
                        Activo
                    </li>
                    <li class="tag-indicator-pending">
                        <span class="w-2 h-2 bg-warning-200 rounded-full"></span>
                        Pendiente
                    </li>
                    <li class="tag-indicator-error">
                        <span class="w-2 h-2 bg-error-300 rounded-full"></span>
                        Inactivo
                    </li>
                </ul>
                <ul class="flex flex-wrap items-center gap-3">
                    <li class="tag-indicator-success">
                        <span class="w-2 h-2 bg-success-300 rounded-full"></span>
                        Activo
                        <button class="remove-tag" type="button">
                            <x-solar-close-circle-outline class="w-4 h-4" />
                        </button>
                    </li>
                    <li class="tag-indicator-pending">
                        <span class="w-2 h-2 bg-warning-200 rounded-full"></span>
                        Pendiente
                        <button class="remove-tag" type="button">
                            <x-solar-close-circle-outline class="w-4 h-4" />
                        </button>
                    </li>
                    <li class="tag-indicator-error">
                        <span class="w-2 h-2 bg-error-300 rounded-full"></span>
                        Inactivo
                        <button class="remove-tag" type="button">
                            <x-solar-close-circle-outline class="w-4 h-4" />
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection 