@props([
    'store',
    'design'
])

{{-- Vista previa del header de la tienda --}}
<div 
    x-data="headerPreview"
    class="w-full"
>
    {{-- Header --}}
    <div 
        class="w-full p-6 py-10"
        :style="{
            backgroundColor: $store.design.bgColor
        }"
    >
        {{-- Logo y Nombre --}}
        <div class="flex flex-col items-center gap-4 mb-4">
            {{-- Logo --}}
            <template x-if="$store.design.logo">
                <img :src="$store.design.logo" class="w-24 h-24 rounded-full object-cover" alt="Logo">
            </template>
            <template x-if="!$store.design.logo">
                <div class="w-24 h-24 rounded-full bg-white-100 flex items-center justify-center">
                    <span class="text-black-300 text-xl font-bold">{{ substr($store->name, 0, 2) }}</span>
                </div>
            </template>

            {{-- Nombre --}}
            <div>
                <h1 
                    class="text-xl font-black mb-1"
                    :style="{ color: $store.design.textColor }"
                >
                    {{ $store->name }}
                </h1>
                <p 
                    class="text-base font-semibold text-center"
                    :style="{ color: $store.design.descriptionColor }"
                >
                    {{ $store->description ?? 'Descripción de la tienda' }}
                </p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('headerPreview', () => ({
        store: window.store
    }));
});
</script>
@endpush 