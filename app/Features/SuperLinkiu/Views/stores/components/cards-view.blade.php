{{-- ================================================================ --}}
{{-- VISTA DE CARDS --}}
{{-- ================================================================ --}}

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
    @forelse($stores as $store)
        <div class="bg-accent-50 rounded-lg p-0 overflow-hidden hover:shadow-lg transition-shadow">
            <div class="p-6">
                {{-- Header del card --}}
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center">
                        @if($store->logo_url)
                            <img class="w-12 h-12 rounded-full object-cover"
                                src="{{ $store->logo_url }}"
                                alt="{{ $store->name }}" />
                        @else
                            <div class="w-12 h-12 rounded-full bg-primary-100 flex items-center justify-center">
                                <span class="text-primary-300 font-bold text-lg">
                                    {{ strtoupper(substr($store->name, 0, 2)) }}
                                </span>
                            </div>
                        @endif
                        <div class="ml-3">
                            <h3 class="text-base font-semibold text-black-400">{{ $store->name }}</h3>
                            <p class="text-xs text-black-200">{{ $store->slug }}</p>
                        </div>
                    </div>
                    <input type="checkbox" class="store-checkbox rounded border-accent-300" value="{{ $store->id }}">
                </div>

                {{-- Información --}}
                <div class="space-y-3 mb-4">
                    <div class="flex items-center text-sm">
                        <x-solar-letter-outline class="w-4 h-4 text-black-200 mr-2" />
                        <span class="text-black-300 truncate">{{ $store->email }}</span>
                    </div>

                    @if($store->phone)
                        <div class="flex items-center text-sm">
                            <x-solar-phone-outline class="w-4 h-4 text-black-200 mr-2" />
                            <span class="text-black-300">{{ $store->phone }}</span>
                        </div>
                    @endif

                    <div class="flex items-center justify-between">
                        <span class="bg-primary-200 text-accent-50 px-2 py-1 rounded-full text-xs font-medium">{{ $store->plan->name }}</span>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" 
                                class="sr-only peer verified-toggle" 
                                {{ $store->verified ? 'checked' : '' }}
                                data-store-id="{{ $store->id }}"
                                data-url="{{ route('superlinkiu.stores.toggle-verified', $store) }}">
                            <div class="w-9 h-5 bg-accent-200 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-accent-50 after:content-[''] after:absolute after:top-[1px] after:left-[1px] after:bg-accent-50 after:border-accent-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-primary-200"></div>
                        </label>
                    </div>
                </div>

                {{-- Estado --}}
                <div class="mb-4">
                    @if($store->status === 'active')
                        <span class="bg-success-200 text-accent-50 w-full text-center py-2 px-2 rounded text-xs font-medium block">Activa</span>
                    @elseif($store->status === 'inactive')
                        <span class="bg-warning-300 text-black-500 w-full text-center py-2 px-2 rounded text-xs font-medium block">Inactiva</span>
                    @else
                        <span class="bg-error-200 text-accent-50 w-full text-center py-2 px-2 rounded text-xs font-medium block">Suspendida</span>
                    @endif
                </div>

                {{-- Acciones --}}
                <div class="flex gap-2">
                    <a href="{{ route('superlinkiu.stores.show', $store) }}"
                        class="flex-1 bg-primary-50 hover:bg-primary-200 hover:text-accent-50 text-primary-300 p-2 rounded-lg text-center transition-colors">
                        <x-solar-eye-outline class="w-4 h-4 mx-auto" />
                    </a>
                    <a href="{{ route('superlinkiu.stores.edit', $store) }}"
                        class="flex-1 bg-info-50 hover:bg-info-200 hover:text-accent-50 text-info-300 p-2 rounded-lg text-center transition-colors">
                        <x-solar-pen-2-outline class="w-4 h-4 mx-auto" />
                    </a>
                    <button @click="loginAsStore({{ $store->id }})"
                        class="flex-1 bg-success-50 hover:bg-success-200 hover:text-accent-50 text-success-300 p-2 rounded-lg transition-colors">
                        <x-solar-login-3-outline class="w-4 h-4 mx-auto" />
                    </button>
                    <button @click="openDeleteModal('{{ $store->slug }}', '{{ $store->name }}')"
                        class="flex-1 bg-error-50 hover:bg-error-200 hover:text-accent-50 text-error-300 p-2 rounded-lg transition-colors">
                        <x-solar-trash-bin-trash-outline class="w-4 h-4 mx-auto" />
                    </button>
                </div>
            </div>
        </div>
    @empty
        <div class="col-span-full text-center py-12">
            <x-solar-box-outline class="w-16 h-16 mx-auto mb-4 text-black-100" />
            <p class="text-black-200">No se encontraron tiendas con los filtros aplicados</p>
        </div>
    @endforelse
</div> 