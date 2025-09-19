@extends('shared::layouts.admin')

@section('title', 'Configuración de Facturas')

@section('content')
<div class="p-6">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-black-500">Configuración de Facturas</h1>
        <p class="text-black-300 mt-1">Personaliza la información que aparece en las facturas generadas</p>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <form action="{{ route('superlinkiu.billing-settings.update') }}" method="POST" enctype="multipart/form-data" class="p-6">
            @csrf
            @method('PUT')

            <!-- Logo Section -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-black-500 mb-4">Logo de la Empresa</h3>
                
                <div class="flex items-start gap-6">
                    <!-- Current Logo -->
                    <div class="flex flex-col items-center">
                        <div class="w-32 h-32 border-2 border-dashed border-gray-300 rounded-lg flex items-center justify-center bg-gray-50 mb-3">
                            @if($settings->logo_url)
                                @php
                                    $logoUrl = $settings->logo_url;
                                    if (strpos($logoUrl, '/storage/') === 0) {
                                        $logoUrl = asset($logoUrl);
                                    } elseif (!filter_var($logoUrl, FILTER_VALIDATE_URL)) {
                                        $logoUrl = asset('storage/' . ltrim($logoUrl, '/storage/'));
                                    }
                                @endphp
                                <img src="{{ $logoUrl }}" alt="Logo actual" class="max-w-full max-h-full object-contain rounded">
                            @else
                                <div class="text-center text-gray-400">
                                    <x-solar-gallery-wide-outline class="w-8 h-8 mx-auto mb-2" />
                                    <span class="text-sm">Sin logo</span>
                                </div>
                            @endif
                        </div>
                        
                        @if($settings->logo_url)
                            <button type="button" onclick="removeLogo()" class="text-xs text-red-600 hover:text-red-800">
                                Eliminar logo
                            </button>
                        @endif
                    </div>

                    <!-- Upload -->
                    <div class="flex-1">
                        <label for="logo" class="block text-sm font-medium text-black-300 mb-2">
                            Nuevo Logo
                        </label>
                        <input type="file" 
                               name="logo" 
                               id="logo" 
                               accept="image/*"
                               class="w-full px-4 py-2 border border-accent-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:bg-primary-50 file:text-primary-300 hover:file:bg-primary-100">
                        <p class="text-xs text-gray-500 mt-1">PNG, JPG, GIF hasta 2MB. Tamaño recomendado: 300x100px</p>
                        @error('logo')
                            <p class="text-xs text-error-300 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Company Info -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <!-- Company Name -->
                <div>
                    <label for="company_name" class="block text-sm font-medium text-black-300 mb-2">
                        Nombre de la Empresa *
                    </label>
                    <input type="text" 
                           name="company_name" 
                           id="company_name" 
                           value="{{ old('company_name', $settings->company_name) }}"
                           required
                           class="w-full px-4 py-2 border border-accent-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none @error('company_name') border-error-200 @enderror">
                    @error('company_name')
                        <p class="text-xs text-error-300 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Tax ID -->
                <div>
                    <label for="tax_id" class="block text-sm font-medium text-black-300 mb-2">
                        NIT / Cédula
                    </label>
                    <input type="text" 
                           name="tax_id" 
                           id="tax_id" 
                           value="{{ old('tax_id', $settings->tax_id) }}"
                           class="w-full px-4 py-2 border border-accent-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none @error('tax_id') border-error-200 @enderror"
                           placeholder="Ej: 900.123.456-7">
                    @error('tax_id')
                        <p class="text-xs text-error-300 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Phone -->
                <div>
                    <label for="phone" class="block text-sm font-medium text-black-300 mb-2">
                        Teléfono
                    </label>
                    <input type="text" 
                           name="phone" 
                           id="phone" 
                           value="{{ old('phone', $settings->phone) }}"
                           class="w-full px-4 py-2 border border-accent-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none @error('phone') border-error-200 @enderror"
                           placeholder="Ej: +57 300 123 4567">
                    @error('phone')
                        <p class="text-xs text-error-300 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-black-300 mb-2">
                        Email de Contacto
                    </label>
                    <input type="email" 
                           name="email" 
                           id="email" 
                           value="{{ old('email', $settings->email) }}"
                           class="w-full px-4 py-2 border border-accent-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none @error('email') border-error-200 @enderror"
                           placeholder="contacto@empresa.com">
                    @error('email')
                        <p class="text-xs text-error-300 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Address -->
            <div class="mb-8">
                <label for="company_address" class="block text-sm font-medium text-black-300 mb-2">
                    Dirección de la Empresa
                </label>
                <textarea name="company_address" 
                          id="company_address" 
                          rows="3"
                          class="w-full px-4 py-2 border border-accent-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none @error('company_address') border-error-200 @enderror"
                          placeholder="Calle 123 #45-67, Ciudad, Departamento">{{ old('company_address', $settings->company_address) }}</textarea>
                @error('company_address')
                    <p class="text-xs text-error-300 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Footer Text -->
            <div class="mb-8">
                <label for="footer_text" class="block text-sm font-medium text-black-300 mb-2">
                    Mensaje en Pie de Página
                </label>
                <textarea name="footer_text" 
                          id="footer_text" 
                          rows="3"
                          class="w-full px-4 py-2 border border-accent-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none @error('footer_text') border-error-200 @enderror"
                          placeholder="Mensaje personalizado que aparecerá al final de cada factura">{{ old('footer_text', $settings->footer_text) }}</textarea>
                <p class="text-xs text-gray-500 mt-1">Este mensaje aparecerá en la parte inferior de todas las facturas</p>
                @error('footer_text')
                    <p class="text-xs text-error-300 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Actions -->
            <div class="flex justify-end gap-3 pt-6 border-t border-gray-200">
                <button type="button" onclick="resetForm()" class="btn-outline-secondary px-6 py-2 rounded-lg">
                    Cancelar
                </button>
                <button type="submit" class="btn-primary px-6 py-2 rounded-lg flex items-center gap-2">
                    <x-solar-check-circle-outline class="w-5 h-5" />
                    Guardar Configuración
                </button>
            </div>
        </form>
    </div>

    <!-- Preview Section -->
    <div class="mt-8 bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-black-500 mb-4">Vista Previa</h3>
            <div class="border border-gray-200 rounded-lg p-6 bg-gray-50">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h4 class="text-xl font-bold">Factura #DEMO-001</h4>
                        <p class="text-sm text-gray-600">Fecha: {{ now()->format('d/m/Y') }}</p>
                    </div>
                    <div class="text-right">
                        @if($settings->logo_url)
                            @php
                                $previewLogoUrl = $settings->logo_url;
                                if (strpos($previewLogoUrl, '/storage/') === 0) {
                                    $previewLogoUrl = asset($previewLogoUrl);
                                } elseif (!filter_var($previewLogoUrl, FILTER_VALIDATE_URL)) {
                                    $previewLogoUrl = asset('storage/' . ltrim($previewLogoUrl, '/storage/'));
                                }
                            @endphp
                            <img src="{{ $previewLogoUrl }}" alt="Logo" class="h-12 mb-2">
                        @endif
                        <div class="text-sm">
                            <p class="font-semibold">{{ $settings->company_name ?: 'Nombre de la Empresa' }}</p>
                            @if($settings->company_address)
                                <p>{{ $settings->company_address }}</p>
                            @endif
                            @if($settings->tax_id)
                                <p>{{ $settings->tax_id }}</p>
                            @endif
                            @if($settings->phone || $settings->email)
                                <p>{{ $settings->phone }}{{ $settings->phone && $settings->email ? ', ' : '' }}{{ $settings->email }}</p>
                            @endif
                        </div>
                    </div>
                </div>
                
                <div class="border-t border-gray-300 pt-4 mt-4">
                    <p class="text-xs text-center text-gray-500">
                        {{ $settings->footer_text ?: 'Mensaje personalizado del pie de página' }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function removeLogo() {
    if (confirm('¿Estás seguro de que deseas eliminar el logo?')) {
        fetch('{{ route("superlinkiu.billing-settings.remove-logo") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error al eliminar el logo');
            }
        })
        .catch(error => {
            alert('Error al eliminar el logo');
        });
    }
}

function resetForm() {
    if (confirm('¿Estás seguro de que deseas cancelar los cambios?')) {
        location.reload();
    }
}
</script>
@endsection
