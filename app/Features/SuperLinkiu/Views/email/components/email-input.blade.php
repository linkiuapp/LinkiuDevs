@props([
    'contextKey',
    'contextInfo',
    'currentValue' => null,
    'errors' => null
])

<div class="bg-accent-50 border border-accent-200 rounded-lg p-6">
    <div class="flex items-start gap-4">
        <div class="flex-shrink-0">
            @if($contextKey === 'store_management')
                <div class="w-12 h-12 bg-primary-100 rounded-lg flex items-center justify-center">
                    <x-solar-shop-outline class="w-6 h-6 text-primary-300" />
                </div>
            @elseif($contextKey === 'support')
                <div class="w-12 h-12 bg-info-100 rounded-lg flex items-center justify-center">
                    <x-solar-chat-round-dots-outline class="w-6 h-6 text-info-300" />
                </div>
            @else
                <div class="w-12 h-12 bg-success-100 rounded-lg flex items-center justify-center">
                    <x-solar-dollar-outline class="w-6 h-6 text-success-300" />
                </div>
            @endif
        </div>
        
        <div class="flex-1">
            <h3 class="text-lg font-semibold text-black-500 mb-2">{{ $contextInfo['name'] }}</h3>
            <p class="text-sm text-black-300 mb-4">{{ $contextInfo['description'] }}</p>
            
            <div class="space-y-2">
                <label for="{{ $contextKey }}_email" class="block text-sm font-medium text-black-400">
                    Dirección de Email
                </label>
                <input 
                    type="email" 
                    id="{{ $contextKey }}_email"
                    name="{{ $contextKey }}_email" 
                    value="{{ old($contextKey . '_email', $currentValue ?? $contextInfo['default_email']) }}"
                    class="w-full px-3 py-2 border border-accent-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-200 @if($errors && $errors->has($contextKey . '_email')) border-error-300 @endif"
                    placeholder="{{ $contextInfo['default_email'] }}"
                    required
                >
                @if($errors && $errors->has($contextKey . '_email'))
                    <p class="text-sm text-error-300">{{ $errors->first($contextKey . '_email') }}</p>
                @endif
            </div>
            
            @if($currentValue)
                <div class="mt-3 flex items-center gap-2 text-xs text-black-300">
                    <x-solar-check-circle-outline class="w-4 h-4 text-success-300" />
                    Configurado y activo
                </div>
            @else
                <div class="mt-3 flex items-center gap-2 text-xs text-warning-300">
                    <x-solar-info-circle-outline class="w-4 h-4" />
                    Usando valor por defecto
                </div>
            @endif
        </div>
    </div>
</div>