{{-- ================================================================ --}}
{{-- VISTA DE TABLA --}}
{{-- ================================================================ --}}

<div class="table-container">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                {{-- ================================================================ --}}
                {{-- Header --}}
                <tr class="table-header">
                    <th class="px-6 py-3">
                        <input type="checkbox" id="selectAll" class="rounded border-white-300">
                    </th>
                    <th class="px-6 py-3 text-left">Tienda</th>
                    <th class="px-6 py-3 text-left">Plan</th>
                    <th class="px-6 py-3 text-left">Estado</th>
                    <th class="px-6 py-3 text-left">Verificada</th>
                    <th class="px-6 py-3 text-left">Creada</th>
                    <th class="px-12 py-3 text-left">Acciones</th>
                </tr>
            </thead>
            <tbody class="bg-white-50 divide-y divide-white-100">
                @forelse($stores as $store)
                    <tr class="text-black-400 hover:bg-white-100">
                        <td class="px-6 py-4 text-center">
                            <input type="checkbox" class="store-checkbox rounded border-white-300" value="{{ $store->id }}">
                        </td>

                        {{-- ================================================================ --}}
                        {{-- Logo y información --}}
                        <td class="px-6 py-4">
                            <div class="flex text-sm">
                                @if($store->design && $store->design->is_published && $store->design->logo_url)
                                    <div class="relative hidden w-10 h-10 mr-3 rounded-full md:block">
                                        <img class="object-cover w-full h-full rounded-full"
                                            src="{{ $store->design->logo_url }}"
                                            alt="{{ $store->name }}"
                                            loading="lazy" />
                                    </div>
                                @else
                                    <div class="w-10 h-10 mr-3 rounded-full bg-primary-100 flex items-center justify-center">
                                        <span class="text-primary-300 font-semibold text-sm">
                                            {{ strtoupper(substr($store->name, 0, 2)) }}
                                        </span>
                                    </div>
                                @endif
                                <div>
                                    <p class="font-semibold">{{ $store->name }}</p>
                                    <p class="text-xs text-black-200">{{ $store->email }}</p>
                                    <p class="text-xs text-black-200">{{ $store->slug }}</p>
                                    <p class="text-xs text-black-200">{{ $store->phone }}</p>
                                </div>
                            </div>
                        </td>

                        {{-- ================================================================ --}}
                        {{-- Plan de la tienda --}}
                        <td class="py-4 text-sm">
                            <span class="bagde-table-primary">{{ $store->plan->name }}</span>
                        </td>

                        {{-- ================================================================ --}}
                        {{-- Estado de la tienda --}}
                        <td class="px-2 py-4 text-sm">
                            @if($store->status === 'active')
                                <span class="bagde-table-success">Activa</span>
                            @elseif($store->status === 'inactive')
                                <span class="bagde-table-warning">Inactiva</span>
                            @else
                                <span class="bg-error-200 text-white-50 px-2 py-1 rounded-full text-xs font-medium">Suspendida</span>
                            @endif
                        </td>

                        {{-- ================================================================ --}}
                        {{-- Verificada --}}
                        <td class="px-6 py-4">
                            <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox"
                                            class="sr-only peer verified-toggle"
                                            {{ $store->verified ? 'checked' : '' }}
                                            data-store-id="{{ $store->id }}"
                                            data-url="{{ route('superlinkiu.stores.toggle-verified', $store->slug) }}">
                                        <div class="table-toggle"></div>
                                    </label>
                        </td>

                        {{-- ================================================================ --}}
                        {{-- Date --}}
                        <td class="px-4 py-4 text-sm text-black-200">
                            {{ $store->created_at->format('d/m/Y') }}
                        </td>

                        {{-- ================================================================ --}}
                        {{-- Acciones --}}
                        <td class="py-4">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('superlinkiu.stores.show', $store) }}"
                                    class="table-action-show" 
                                    title="Ver detalles">
                                    <x-solar-eye-outline class="table-action-icon" />
                                </a>
                                <a href="{{ route('superlinkiu.stores.edit', $store) }}"
                                    class="table-action-edit"
                                    title="Editar">
                                    <x-solar-pen-2-outline class="table-action-icon" />
                                </a>
                                <button @click="loginAsStore({{ $store->id }})"
                                    class="table-action-login"
                                    title="Entrar como admin">
                                    <x-solar-login-3-outline class="table-action-icon" />
                                </button>
                                <button @click="openDeleteModal('{{ $store->slug }}', '{{ $store->name }}')"
                                    class="table-action-delete"
                                    title="Eliminar">
                                    <x-solar-trash-bin-trash-outline class="table-action-icon" />
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    {{-- ================================================================ --}}
                    {{-- Empty --}}
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-black-200">
                            <x-solar-box-outline class="w-12 h-12 mx-auto mb-3 text-black-100" />
                            <p class="text-base font-semibold">No se encontraron tiendas con los filtros aplicados</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div> 