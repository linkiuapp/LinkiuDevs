@extends('shared::layouts.admin')

@section('title', 'Detalles de Tienda')

@section('content')
<div class="container-fluid" x-data="storeManagement">
    <!-- Sistema de Notificaciones -->
    <div x-show="showNotification" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform translate-y-2"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 transform translate-y-0"
         x-transition:leave-end="opacity-0 transform translate-y-2"
         class="fixed top-4 right-4 z-50 max-w-sm">
        <div :class="{
            'bg-success-200 text-white-50': notificationType === 'success',
            'bg-error-200 text-white-50': notificationType === 'error',
            'bg-warning-200 text-black-400': notificationType === 'warning',
            'bg-info-200 text-white-50': notificationType === 'info'
        }" class="rounded-lg shadow-lg p-4 flex items-center gap-3">
            <template x-if="notificationType === 'success'">
                <x-solar-check-circle-bold class="w-5 h-5 flex-shrink-0" />
            </template>
            <template x-if="notificationType === 'error'">
                <x-solar-close-circle-bold class="w-5 h-5 flex-shrink-0" />
            </template>
            <template x-if="notificationType === 'warning'">
                <x-solar-danger-triangle-bold class="w-5 h-5 flex-shrink-0" />
            </template>
            <template x-if="notificationType === 'info'">
                <x-solar-info-circle-bold class="w-5 h-5 flex-shrink-0" />
            </template>
            <span x-text="notificationMessage" class="text-sm font-medium"></span>
            <button @click="showNotification = false" class="ml-auto">
                <x-solar-close-circle-bold class="w-4 h-4" />
            </button>
        </div>
    </div>

    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div class="flex items-center gap-4">
            @if($store->logo_url)
                <img class="w-16 h-16 rounded-full object-cover" src="{{ $store->logo_url }}" alt="{{ $store->name }}">
            @else
                <div class="w-16 h-16 rounded-full bg-primary-100 flex items-center justify-center">
                    <span class="text-primary-300 font-bold text-xl">
                        {{ strtoupper(substr($store->name, 0, 2)) }}
                    </span>
                </div>
            @endif
            <div>
                <h1 class="text-lg font-bold text-black-400">{{ $store->name }}</h1>
                <div class="flex items-center gap-2">
                    <p class="text-sm text-black-300">{{ str_replace(['http://', 'https://'], '', url('/')) }}/{{ $store->slug }}</p>
                    <a href="{{ url('/' . $store->slug) }}" target="_blank" class="text-primary-200 hover:text-primary-300">
                        <x-solar-square-arrow-right-up-outline class="w-4 h-4" />
                    </a>
                </div>
            </div>
        </div>
        <div class="flex gap-3">
            <button @click="loginAsStore({{ $store->id }})"
                class="btn-outline-success px-4 py-2 rounded-lg flex items-center gap-2">
                <x-solar-login-3-outline class="w-5 h-5" />
                Entrar como Admin
            </button>
            <a href="{{ route('superlinkiu.stores.edit', $store) }}"
                class="btn-primary px-4 py-2 rounded-lg flex items-center gap-2">
                <x-solar-pen-2-outline class="w-5 h-5" />
                Editar Tienda
            </a>
            <a href="{{ route('superlinkiu.stores.index') }}" 
                class="btn-outline-secondary px-4 py-2 rounded-lg flex items-center gap-2">
                <x-solar-arrow-left-outline class="w-5 h-5" />
                Volver
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Columna principal -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Información básica -->
            <div class="bg-white-50 rounded-lg p-0 overflow-hidden">
                <div class="border-b border-white-100 bg-white-50 py-4 px-6">
                    <h2 class="text-lg font-semibold text-black-400 mb-0">Información Básica</h2>
                </div>
                <div class="p-6">
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-black-300">URL de la tienda</dt>
                            <dd class="mt-1 text-sm text-black-400">
                                <div class="flex items-center gap-2">
                                    <span>{{ str_replace(['http://', 'https://'], '', url('/')) }}/{{ $store->slug }}</span>
                                    <a href="{{ url('/' . $store->slug) }}" target="_blank" class="text-primary-200 hover:text-primary-300">
                                        <x-solar-square-arrow-right-up-outline class="w-4 h-4" />
                                    </a>
                                </div>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-black-300">Email</dt>
                            <dd class="mt-1 text-sm text-black-400">{{ $store->email }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-black-300">Teléfono</dt>
                            <dd class="mt-1 text-sm text-black-400">{{ $store->phone ?: 'No especificado' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-black-300">Documento</dt>
                            <dd class="mt-1 text-sm text-black-400">{{ $store->getFullDocument() ?: 'No especificado' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-black-300">Ubicación</dt>
                            <dd class="mt-1 text-sm text-black-400">{{ $store->getFullAddress() ?: 'No especificada' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-black-300">Plan</dt>
                            <dd class="mt-1 text-sm text-black-400">
                                <span class="badge-soft-primary">{{ $store->plan->name }}</span>
                            </dd>
                        </div>
                        <div class="md:col-span-2">
                            <dt class="text-sm font-medium text-black-300">Descripción</dt>
                            <dd class="mt-1 text-sm text-black-400">{{ $store->description ?: 'Sin descripción' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Estado y verificación -->
            <div class="bg-white-50 rounded-lg p-0 overflow-hidden">
                <div class="border-b border-white-100 bg-white-50 py-4 px-6">
                    <h2 class="text-lg font-semibold text-black-400 mb-0">Estado de la Tienda</h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <p class="text-sm font-medium text-black-300 mb-2">Estado</p>
                            @if($store->status === 'active')
                                <span class="badge-soft-success px-4 py-2">Activa</span>
                            @elseif($store->status === 'inactive')
                                <span class="badge-soft-warning px-4 py-2">Inactiva</span>
                            @else
                                <span class="badge-soft-error px-4 py-2">Suspendida</span>
                            @endif
                        </div>
                        <div>
                            <p class="text-sm font-medium text-black-300 mb-2">Verificación</p>
                            <div class="flex items-center gap-3">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" 
                                        class="sr-only peer verified-toggle" 
                                        {{ $store->verified ? 'checked' : '' }}
                                        data-store-id="{{ $store->id }}"
                                        data-url="{{ route('superlinkiu.stores.toggle-verified', $store) }}">
                                    <div class="w-11 h-6 bg-white-200 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white-50 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white-50 after:border-white-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-200"></div>
                                </label>
                                <span class="text-sm text-black-300">{{ $store->verified ? 'Verificada' : 'No verificada' }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 pt-4 border-t border-white-100">
                        <dl class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <dt class="text-black-300">Creada</dt>
                                <dd class="text-black-400 mt-1">{{ $store->created_at->format('d/m/Y H:i') }}</dd>
                            </div>
                            <div>
                                <dt class="text-black-300">Última actualización</dt>
                                <dd class="text-black-400 mt-1">{{ $store->updated_at->format('d/m/Y H:i') }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>

            <!-- Historial de extensiones -->
            <div class="bg-white-50 rounded-lg p-0 overflow-hidden">
                <div class="border-b border-white-100 bg-white-50 py-4 px-6">
                    <h2 class="text-lg font-semibold text-black-400 mb-0">Historial de Extensiones</h2>
                </div>
                <div class="p-6">
                    @forelse($store->planExtensions()->latest()->take(5)->get() as $extension)
                        <div class="p-4 bg-white-100 rounded-lg mb-3 last:mb-0">
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="font-medium text-black-400">
                                        Extensión de {{ $extension->getDurationInDays() }} días
                                    </p>
                                    <p class="text-sm text-black-300 mt-1">
                                        {{ $extension->start_date->format('d/m/Y') }} - {{ $extension->end_date->format('d/m/Y') }}
                                    </p>
                                    @if($extension->reason)
                                        <p class="text-sm text-black-300 mt-2">
                                            <span class="font-medium">Razón:</span> {{ $extension->reason }}
                                        </p>
                                    @endif
                                    <p class="text-xs text-black-200 mt-2">
                                        Por {{ $extension->superAdmin->name }} • {{ $extension->created_at->diffForHumans() }}
                                    </p>
                                </div>
                                @if($extension->isActive())
                                    <span class="badge-soft-success text-xs">Activa</span>
                                @else
                                    <span class="badge-soft-secondary text-xs">Expirada</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-black-300 text-center py-4">No hay extensiones registradas</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Columna lateral -->
        <div class="space-y-6">
            <!-- Plan actual -->
            <div class="bg-white-50 rounded-lg p-0 overflow-hidden">
                <div class="border-b border-white-100 bg-white-50 py-4 px-6">
                    <h2 class="text-lg font-semibold text-black-400 mb-0">Plan Actual</h2>
                </div>
                <div class="p-6">
                    <div class="text-center mb-4">
                        <p class="text-2xl font-bold text-primary-300">{{ $store->plan->name }}</p>
                        <p class="text-3xl font-bold text-black-400 mt-2">{{ $store->plan->getPriceFormatted() }}</p>
                        <p class="text-sm text-black-300">por mes</p>
                    </div>
                    
                    <div class="space-y-3 pt-4 border-t border-white-100">
                        @if($store->plan->max_products)
                        <div class="flex justify-between text-sm">
                            <span class="text-black-300">Productos máximos</span>
                            <span class="font-medium text-black-400">{{ $store->plan->max_products }}</span>
                        </div>
                        @endif
                        @if($store->plan->max_categories)
                        <div class="flex justify-between text-sm">
                            <span class="text-black-300">Categorías máximas</span>
                            <span class="font-medium text-black-400">{{ $store->plan->max_categories }}</span>
                        </div>
                        @endif
                        @if($store->plan->max_slider)
                        <div class="flex justify-between text-sm">
                            <span class="text-black-300">Imágenes en slider</span>
                            <span class="font-medium text-black-400">{{ $store->plan->max_slider }}</span>
                        </div>
                        @endif
                        <div class="flex justify-between text-sm">
                            <span class="text-black-300">Duración</span>
                            <span class="font-medium text-black-400">{{ $store->plan->duration_in_days }} días</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Extender plan -->
            <div class="bg-white-50 rounded-lg p-0 overflow-hidden">
                <div class="border-b border-white-100 bg-white-50 py-4 px-6">
                    <h2 class="text-lg font-semibold text-black-400 mb-0">Extender Plan</h2>
                </div>
                <div class="p-6">
                    <form action="{{ route('superlinkiu.stores.extend-plan', $store) }}" method="POST" 
                          @submit.prevent="$event.target.submit(); showNotificationMessage('Extensión aplicada exitosamente', 'success')">
                        @csrf
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-black-300 mb-2">
                                    Días de extensión <span class="text-error-300">*</span>
                                </label>
                                <input type="number"
                                    name="days"
                                    min="1"
                                    max="365"
                                    class="w-full px-4 py-2 border border-white-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none"
                                    required
                                    placeholder="30">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-black-300 mb-2">
                                    Razón <span class="text-error-300">*</span>
                                </label>
                                <textarea name="reason"
                                    rows="2"
                                    class="w-full px-4 py-2 border border-white-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none"
                                    required
                                    placeholder="Motivo de la extensión..."></textarea>
                            </div>
                            <button type="submit"
                                class="w-full btn-primary px-4 py-2 rounded-lg flex items-center justify-center gap-2">
                                <x-solar-calendar-add-outline class="w-5 h-5" />
                                Aplicar Extensión
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Acciones rápidas -->
            <div class="bg-white-50 rounded-lg p-0 overflow-hidden">
                <div class="border-b border-white-100 bg-white-50 py-4 px-6">
                    <h2 class="text-lg font-semibold text-black-400 mb-0">Acciones Rápidas</h2>
                </div>
                <div class="p-6 space-y-3">
                    <button @click="openDeleteModal('{{ $store->slug }}', '{{ $store->name }}')"
                        class="w-full btn-outline-error px-4 py-2 rounded-lg flex items-center justify-center gap-2">
                        <x-solar-trash-bin-trash-outline class="w-5 h-5" />
                        Eliminar Tienda
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de eliminación -->
    <div x-show="showDeleteModal" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto"
         style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div x-show="showDeleteModal"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="closeDeleteModal"
                 class="fixed inset-0 bg-black-500/75 backdrop-blur-sm transition-opacity"></div>

            <div x-show="showDeleteModal"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 @click.stop
                 class="inline-block align-bottom bg-white-50 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white-50 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-error-100 sm:mx-0 sm:h-10 sm:w-10">
                            <x-solar-trash-bin-trash-outline class="h-6 w-6 text-error-300" />
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-black-400">
                                Eliminar Tienda
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-black-300">
                                    ¿Estás seguro de que deseas eliminar la tienda <span class="font-semibold" x-text="deleteStoreName"></span>? 
                                    Esta acción no se puede deshacer.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-white-100 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                    <button type="button"
                            @click="confirmDelete"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-error-200 text-base font-medium text-white-50 hover:bg-error-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-error-200 sm:ml-3 sm:w-auto sm:text-sm">
                        Eliminar
                    </button>
                    <button type="button"
                            @click="closeDeleteModal"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-white-200 shadow-sm px-4 py-2 bg-white-50 text-base font-medium text-black-300 hover:bg-white-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-200 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 