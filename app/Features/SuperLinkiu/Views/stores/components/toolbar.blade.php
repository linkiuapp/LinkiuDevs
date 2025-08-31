{{-- ================================================================ --}}
{{-- BARRA DE HERRAMIENTAS - Vista y acciones bulk --}}
{{-- ================================================================ --}}

<div class="flex justify-between items-center mb-4">
    <div class="flex items-center gap-4">
        {{-- Selector de vista --}}
        <div class="flex bg-accent-100 rounded-lg p-1">
            <a href="{{ route('superlinkiu.stores.index', array_merge(request()->all(), ['view' => 'table'])) }}" 
                class="px-3 py-1.5 rounded transition-colors {{ $viewType === 'table' ? 'bg-primary-200 text-accent-50' : 'text-black-300' }}">
                <x-solar-list-outline class="w-5 h-5" />
            </a>
            <a href="{{ route('superlinkiu.stores.index', array_merge(request()->all(), ['view' => 'cards'])) }}" 
                class="px-3 py-1.5 rounded transition-colors {{ $viewType === 'cards' ? 'bg-primary-200 text-accent-50' : 'text-black-300' }}">
                <x-solar-widget-2-outline class="w-5 h-5" />
            </a>
        </div>

        {{-- Información de resultados --}}
        <span class="text-sm text-black-300">
            Mostrando {{ $stores->firstItem() ?? 0 }} - {{ $stores->lastItem() ?? 0 }} de {{ $stores->total() }} tiendas
        </span>
    </div>

    {{-- Acciones en lote --}}
    <div class="flex items-center gap-3" id="bulkActions" style="display: none;">
        <span class="text-sm text-black-300">
            <span id="selectedCount">0</span> seleccionadas
        </span>
        <select id="bulkActionSelect" class="px-3 py-1.5 border border-accent-200 rounded-lg text-sm focus:outline-none">
            <option value="">Acción en lote...</option>
            <option value="activate">Activar</option>
            <option value="deactivate">Desactivar</option>
            <option value="suspend">Suspender</option>
            <option value="verify">Verificar</option>
            <option value="unverify">Quitar verificación</option>
            <option value="delete">Eliminar</option>
        </select>
        <button @click="executeBulkAction()" class="btn-primary px-3 py-1.5 rounded-lg text-sm">
            Aplicar
        </button>
    </div>
</div> 