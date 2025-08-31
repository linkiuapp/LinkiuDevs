<x-tenant-admin-layout :store="$store">
    @section('title', 'Detalles de Categoría')

    @section('content')
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center gap-3 mb-4">
                <a href="{{ route('tenant.admin.categories.index', $store->slug) }}" 
                   class="text-black-300 hover:text-black-400">
                    <x-solar-arrow-left-outline class="w-6 h-6" />
                </a>
                <h1 class="text-lg font-semibold text-black-500">Detalles de Categoría</h1>
            </div>
        </div>

        <!-- Card principal -->
        <div class="bg-accent-50 rounded-lg p-0 overflow-hidden p-6">
            <div class="border-b border-accent-100 bg-accent-50 py-4 px-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 bg-accent-100 rounded-lg p-3 flex items-center justify-center">
                            <img src="{{ $category->icon->image_url }}" 
                                 alt="{{ $category->icon->display_name }}" 
                                 class="w-full h-full object-contain">
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold text-black-500 mb-1">{{ $category->name }}</h2>
                            <div class="flex items-center gap-3">
                                @if($category->is_active)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-success-200 text-black-300">
                                        <x-solar-check-circle-outline class="w-3 h-3 mr-1" />
                                        Activa
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-error-200 text-black-300">
                                        <x-solar-close-circle-outline class="w-3 h-3 mr-1" />
                                        Inactiva
                                    </span>
                                @endif
                                
                                @if($category->parent)
                                    <span class="text-sm text-black-300">
                                        Subcategoría de: <strong>{{ $category->parent->name }}</strong>
                                    </span>
                                @else
                                    <span class="text-sm text-black-300">Categoría principal</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-end items-center gap-8">
                        <a href="{{ route('tenant.admin.categories.edit', [$store->slug, $category->id]) }}" 
                           class="btn-primary px-4 py-2 rounded-lg flex items-center gap-2">
                           <x-solar-pen-new-square-outline class="w-4 h-4 mr-2" />
                           Editar
                        </a>
                    </div>
                </div>
            </div>

            <div class="p-6 space-y-6">
                <!-- Información básica -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-lg font-semibold text-black-500 mb-4">Información Básica</h3>
                        <div class="space-y-3">
                            <div>
                                <span class="text-sm font-medium text-black-400">Nombre:</span>
                                <p class="text-black-500">{{ $category->name }}</p>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-black-400">Slug:</span>
                                <p class="text-black-500 font-mono text-sm">{{ $category->slug }}</p>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-black-400">URL:</span>
                                <p class="text-black-500 text-sm">
                                    <a href="{{ config('app.url') }}/{{ $store->slug }}/categoria/{{ $category->slug }}" 
                                       target="_blank" 
                                       class="text-info-200 hover:text-info-300">
                                        {{ config('app.url') }}/{{ $store->slug }}/categoria/{{ $category->slug }}
                                        <x-solar-link-outline class="w-4 h-4 inline ml-1" />
                                    </a>
                                </p>
                            </div>
                            @if($category->description)
                                <div>
                                    <span class="text-sm font-medium text-black-400">Descripción:</span>
                                    <p class="text-black-500">{{ $category->description }}</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div>
                        <h3 class="text-lg font-semibold text-black-500 mb-4">Configuración</h3>
                        <div class="space-y-3">
                            <div>
                                <span class="text-sm font-medium text-black-400">Estado:</span>
                                <p class="text-black-500">{{ $category->is_active ? 'Activa' : 'Inactiva' }}</p>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-black-400">Orden:</span>
                                <p class="text-black-500">{{ $category->sort_order }}</p>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-black-400">Tipo:</span>
                                <p class="text-black-500">
                                    {{ $category->parent ? 'Subcategoría' : 'Categoría principal' }}
                                </p>
                            </div>
                            @if($category->parent)
                                <div>
                                    <span class="text-sm font-medium text-black-400">Categoría padre:</span>
                                    <p class="text-black-500">
                                        <a href="{{ route('tenant.admin.categories.show', [$store->slug, $category->parent->id]) }}" 
                                           class="text-info-200 hover:text-info-300">
                                            {{ $category->parent->name }}
                                        </a>
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Estadísticas -->
                <div>
                    <h3 class="text-lg font-semibold text-black-500 mb-4">Estadísticas</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-accent-100 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-black-400">Productos</p>
                                    <p class="text-xl font-black text-black-500">{{ $category->products_count }}</p>
                                </div>
                                <x-solar-box-outline class="w-8 h-8 text-primary-400" />
                            </div>
                        </div>
                        
                        @if(!$category->parent)
                            <div class="bg-accent-100 rounded-lg p-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-black-400">Subcategorías</p>
                                        <p class="text-xl font-black text-black-500">{{ $category->children->count() }}</p>
                                    </div>
                                    <x-solar-folder-outline class="w-8 h-8 text-secondary-400" />
                                </div>
                            </div>
                        @endif
                        
                        <div class="bg-accent-100 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-black-400">Creada</p>
                                    <p class="text-sm text-black-500">{{ $category->created_at->format('d/m/Y') }}</p>
                                </div>
                                <x-solar-calendar-outline class="w-8 h-8 text-info-400" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Subcategorías -->
                @if($category->children->count() > 0)
                    <div>
                        <h3 class="text-lg font-semibold text-black-500 mb-4">Subcategorías</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($category->children as $child)
                                <div class="bg-accent-100 rounded-lg p-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 bg-accent-50 rounded-lg p-2 flex items-center justify-center">
                                            <img src="{{ $child->icon->image_url }}" 
                                                 alt="{{ $child->icon->display_name }}" 
                                                 class="w-full h-full object-contain">
                                        </div>
                                        <div class="flex-1">
                                            <h4 class="font-medium text-black-500">{{ $child->name }}</h4>
                                            <p class="text-sm text-black-300">{{ $child->products_count }} productos</p>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            @if($child->is_active)
                                                <span class="w-2 h-2 bg-success-400 rounded-full"></span>
                                            @else
                                                <span class="w-2 h-2 bg-error-400 rounded-full"></span>
                                            @endif
                                            <a href="{{ route('tenant.admin.categories.show', [$store->slug, $child->id]) }}" 
                                               class="text-info-200 hover:text-info-300">
                                                <x-solar-eye-outline class="w-4 h-4" />
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Metadatos -->
                <div class="border-t border-accent-100 pt-6">
                    <h3 class="text-lg font-semibold text-black-500 mb-4">Metadatos</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <span class="text-sm font-medium text-black-400">Fecha de creación:</span>
                            <p class="text-black-500">{{ $category->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-black-400">Última actualización:</span>
                            <p class="text-black-500">{{ $category->updated_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endsection
</x-tenant-admin-layout> 