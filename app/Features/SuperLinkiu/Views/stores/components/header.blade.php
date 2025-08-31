{{-- ================================================================ --}}
{{-- HEADER - Título y acciones principales --}}
{{-- ================================================================ --}}

<div class="flex justify-between items-center mb-6">
    <h1 class="text-lg font-bold text-black-400">Gestión de Tiendas</h1>
    
    <div class="flex gap-3">
        <button @click="exportData('excel')" 
                class="btn-outline-primary px-4 py-2 rounded-lg flex items-center gap-2">
            <x-solar-download-outline class="w-5 h-5" />
            Exportar Excel
        </button>
        
        <a href="{{ route('superlinkiu.stores.bulk.import') }}" 
           class="btn-outline-secondary px-4 py-2 rounded-lg flex items-center gap-2">
            <x-solar-upload-outline class="w-5 h-5" />
            Importación Masiva
        </a>
        
        <a href="{{ route('superlinkiu.stores.create-wizard') }}" 
           class="btn-primary px-4 py-2 rounded-lg flex items-center gap-2">
            <x-solar-add-circle-outline class="w-5 h-5" />
            Nueva Tienda
        </a>
    </div>
</div> 