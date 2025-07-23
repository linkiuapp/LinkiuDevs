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
                    <th class="px-6 py-3 text-left">Categoría</th>
                    <th class="px-6 py-3 text-left">Tipo</th>
                    <th class="px-6 py-3 text-left">Estado</th>
                    <th class="py-3 text-left">Activar/Desactivar</th>
                    <th class="py-3 text-left">Productos</th>
                    <th class="px-6 py-3 text-left">Orden</th>
                    <th class="px-6 py-3 text-left">Acciones</th>
                </tr>
            </thead>
            <tbody class="bg-white-50 divide-y divide-white-100 id="sortableCategories">
                @forelse($categories as $category)
                    <tr class="text-black-400 hover:bg-white-100">
                        <td class="px-6 py-4 text-center">
                            <input type="checkbox" class="store-checkbox rounded border-white-300" value="{{ $store->id }}">
                        </td>

                        {{-- ================================================================ --}}
                        {{-- Logo y información --}}
                        <td class="px-6 py-4">
                            <div class="flex text-sm">
                                @if($category->icon)
                                    <img class="object-contain w-10 h-10 mr-3"
                                        src="{{ $category->icon->image_url }}"
                                        alt="{{ $category->icon->display_name }}"
                                        loading="lazy" />
                                @endif
                                <div>
                                    <p class="font-semibold">{{ $category->name }}</p>
                                    <p class="text-xs text-black-200">{{ $category->slug }}</p>
                                    @if($category->description)
                                        <p class="text-xs text-black-200">{{ $category->description }}</p>
                                    @endif
                                </div>
                            </div>
                        </td>

                        {{-- ================================================================ --}}
                        {{-- Tipo de categoría --}}
                        <td class="py-4 text-sm">
                            @if($category->parent)
                                <span class="bagde-table-warning">
                                    Subcategoría de {{ $category->parent->name }}
                                </span>
                            @else
                                <span class="bagde-table-primary">
                                    Principal
                                </span>
                            @endif
                        </td>

                        {{-- ================================================================ --}}
                        {{-- Estado de la tienda --}}
                        <td class="px-2 py-4 text-sm">
                            @if($category->is_active)
                                <span class="bagde-table-success">Activa</span>
                            @else
                                <span class="bagde-table-error">Inactiva</span>
                            @endif
                        </td>

                        {{-- ================================================================ --}}
                        {{-- Activar/Desactivar --}}
                        <td class="px-6 py-4">
                            <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox"
                                            class="sr-only peer category-toggle"
                                            {{ $category->is_active ? 'checked' : '' }}
                                            data-category-id="{{ $category->id }}"
                                            data-url="{{ route('tenant.admin.categories.toggle-status', [$store->slug, $category->id]) }}">
                                        <div class="table-toggle"></div>
                                    </label>
                        </td>

                        {{-- ================================================================ --}}
                        {{-- Productos --}}
                        <td class="px-6 py-4">
                            <span class="bagde-table-info">
                                {{ $category->products_count }}
                            </span>
                        </td>

                        {{-- ================================================================ --}}
                        {{-- Orden --}}
                        <td class="px-6 py-4 text-sm text-black-200">
                            <span class="drag-handle cursor-move">
                                <x-solar-sort-outline class="w-5 h-5 text-black-300" />
                            </span>
                        </td>

                        {{-- ================================================================ --}}
                        {{-- Acciones --}}
                        <td class="py-4">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('tenant.admin.categories.show', [$store->slug, $category->id]) }}"
                                    class="table-action-show" 
                                    title="Ver detalles">
                                    <x-solar-eye-outline class="table-action-icon" />
                                </a>
                                <a href="{{ route('tenant.admin.categories.edit', [$store->slug, $category->id]) }}"
                                    class="table-action-edit"
                                    title="Editar">
                                    <x-solar-pen-2-outline class="table-action-icon" />
                                </a>
                                <button @click="deleteCategory({{ $category->id }}, '{{ $category->name }}')"
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
                                <td colspan="7" class="px-6 py-8 text-center">
                                    <div class="flex flex-col items-center">
                                        <x-solar-box-outline class="w-12 h-12 text-black-200 mb-3" />
                                        <p class="text-black-300">No hay categorías creadas</p>
                                        @if($totalCategories < $categoryLimit)
                                            <a href="{{ route('tenant.admin.categories.create', $store->slug) }}" 
                                               class="btn-primary mt-3 text-sm">
                                                Crear primera categoría
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div> 