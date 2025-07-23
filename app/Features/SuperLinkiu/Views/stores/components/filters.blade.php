{{-- ================================================================ --}}
{{-- FILTROS Y BÚSQUEDA --}}
{{-- ================================================================ --}}

<div class="bg-white-50 rounded-lg p-0 overflow-hidden mb-6">
    <div class="border-b border-white-100 bg-white-50 py-4 px-6">
        <h2 class="text-3xl text-black-500 mb-0">Filtros de Búsqueda</h2>
    </div>
    
    <div class="p-6">
        <form method="GET" action="{{ route('superlinkiu.stores.index') }}" id="filterForm">
            <input type="hidden" name="view" value="{{ $viewType }}">
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                {{-- Búsqueda --}}
                <div class="lg:col-span-2">
                    <label class="block text-sm font-medium text-black-300 mb-2">Buscar</label>
                    <div class="relative">
                        <input type="text" 
                            name="search" 
                            value="{{ request('search') }}"
                            placeholder="Buscar por nombre, email, documento o slug..."
                            class="w-full pl-10 pr-4 py-2 border border-white-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none">
                        <x-solar-magnifer-outline class="w-5 h-5 text-black-200 absolute left-3 top-2.5" />
                    </div>
                </div>

                {{-- Filtro por plan --}}
                <div>
                    <label class="block text-sm font-medium text-black-300 mb-2">Plan</label>
                    <select name="plan_id" class="w-full px-4 py-2 border border-white-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none">
                        <option value="">Todos los planes</option>
                        @foreach($plans as $plan)
                            <option value="{{ $plan->id }}" {{ request('plan_id') == $plan->id ? 'selected' : '' }}>
                                {{ $plan->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Filtro por estado --}}
                <div>
                    <label class="block text-sm font-medium text-black-300 mb-2">Estado</label>
                    <select name="status" class="w-full px-4 py-2 border border-white-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none">
                        <option value="">Todos los estados</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Activa</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactiva</option>
                        <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>Suspendida</option>
                    </select>
                </div>

                {{-- Filtro por verificación --}}
                <div>
                    <label class="block text-sm font-medium text-black-300 mb-2">Verificación</label>
                    <select name="verified" class="w-full px-4 py-2 border border-white-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none">
                        <option value="">Todas</option>
                        <option value="1" {{ request('verified') === '1' ? 'selected' : '' }}>Verificadas</option>
                        <option value="0" {{ request('verified') === '0' ? 'selected' : '' }}>No verificadas</option>
                    </select>
                </div>

                {{-- Rango de fechas --}}
                <div>
                    <label class="block text-sm font-medium text-black-300 mb-2">Desde</label>
                    <input type="date" 
                        name="start_date" 
                        value="{{ request('start_date') }}"
                        class="w-full px-4 py-2 border border-white-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium text-black-300 mb-2">Hasta</label>
                    <input type="date" 
                        name="end_date" 
                        value="{{ request('end_date') }}"
                        class="w-full px-4 py-2 border border-white-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none">
                </div>

                {{-- Botones de acción --}}
                <div class="flex items-end gap-2">
                    <button type="submit" class="btn-primary px-4 py-2 rounded-lg flex items-center gap-2">
                        <x-solar-filter-outline class="w-5 h-5" />
                        Filtrar
                    </button>
                    <a href="{{ route('superlinkiu.stores.index') }}" class="btn-outline-secondary px-4 py-2 rounded-lg">
                        Limpiar
                    </a>
                </div>
            </div>
        </form>
    </div>
</div> 