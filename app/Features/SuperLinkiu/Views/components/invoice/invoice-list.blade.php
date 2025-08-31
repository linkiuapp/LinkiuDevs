@extends('shared::layouts.admin')

@section('title', 'Lista de Facturas')

@section('content')
<div class="card">
    <div class="card-header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <h2 class="title-card">Lista de Facturas</h2>
            <div class="flex flex-wrap items-center gap-3">
                <select class="bg-accent-50 border border-accent-200 rounded-lg px-3 py-2 text-base text-black-400">
                    <option value="">Estado</option>
                    <option value="paid">Pagado</option>
                    <option value="pending">Pendiente</option>
                    <option value="overdue">Vencido</option>
                </select>
                <a href="{{ route('superlinkiu.components.invoice.invoice-add') }}" class="bg-primary-200 text-accent-50 px-4 py-2 rounded-lg inline-flex items-center gap-2 hover:bg-primary-300 transition-colors">
                    <x-solar-add-circle-outline class="w-5 h-5" />
                    <span class="text-base">Crear Factura</span>
                </a>
            </div>
        </div>
    </div>

    <div class="p-6">
        <!-- Controles de filtros -->
        <div class="flex flex-wrap items-center gap-4 mb-6">
            <div class="flex items-center gap-2">
                <span class="text-base text-black-400">Mostrar</span>
                <select id="entriesPerPage" class="bg-accent-50 border border-accent-200 rounded-lg px-3 py-2 text-base text-black-400 focus:outline-none focus:border-primary-200 focus:ring-1 focus:ring-primary-200">
                    <option value="10">10</option>
                    <option value="15">15</option>
                    <option value="20">20</option>
                    <option value="25">25</option>
                </select>
                <span class="text-base text-black-400">entradas</span>
            </div>
            <div class="relative">
                <input type="text" id="searchInput" placeholder="Buscar facturas..." class="bg-accent-50 border border-accent-200 rounded-lg px-4 py-2 pl-10 text-base text-black-400 focus:outline-none focus:border-primary-200 focus:ring-1 focus:ring-primary-200">
                <div class="absolute left-3 top-1/2 transform -translate-y-1/2 text-black-300">
                    <x-solar-magnifer-outline class="w-5 h-5" />
                </div>
            </div>
        </div>

        <!-- Tabla de facturas -->
        <div class="overflow-x-auto">
            <table class="w-full border-collapse border border-accent-200">
                <thead>
                    <tr class="bg-accent-100">
                        <th class="border border-accent-200 px-4 py-3 text-left">
                            <div class="flex items-center gap-2">
                                <input type="checkbox" id="selectAll" class="w-4 h-4 text-primary-200 border-accent-300 rounded">
                                <span class="text-base text-black-400">No.</span>
                            </div>
                        </th>
                        <th class="border border-accent-200 px-4 py-3 text-left text-base text-black-400">Factura</th>
                        <th class="border border-accent-200 px-4 py-3 text-left text-base text-black-400">Cliente</th>
                        <th class="border border-accent-200 px-4 py-3 text-left text-base text-black-400">Fecha Emisión</th>
                        <th class="border border-accent-200 px-4 py-3 text-left text-base text-black-400">Monto</th>
                        <th class="border border-accent-200 px-4 py-3 text-left text-base text-black-400">Estado</th>
                        <th class="border border-accent-200 px-4 py-3 text-left text-base text-black-400">Acciones</th>
                    </tr>
                </thead>
                <tbody id="invoiceTableBody">
                    <tr class="hover:bg-accent-100 transition-colors">
                        <td class="border border-accent-200 px-4 py-3">
                            <div class="flex items-center gap-2">
                                <input type="checkbox" class="invoice-checkbox w-4 h-4 text-primary-200 border-accent-300 rounded">
                                <span class="text-base text-black-400">01</span>
                            </div>
                        </td>
                        <td class="border border-accent-200 px-4 py-3">
                            <a href="#" class="text-primary-200 hover:text-primary-300 text-base font-semibold">#526534</a>
                        </td>
                        <td class="border border-accent-200 px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-primary-100 rounded-full flex items-center justify-center">
                                    <span class="text-primary-200 font-semibold text-base">KM</span>
                                </div>
                                <div>
                                    <p class="text-base text-black-400 font-semibold mb-0">Kathryn Murphy</p>
                                    <p class="text-sm text-black-300 mb-0">kathryn@email.com</p>
                                </div>
                            </div>
                        </td>
                        <td class="border border-accent-200 px-4 py-3 text-base text-black-400">25 Jan 2025</td>
                        <td class="border border-accent-200 px-4 py-3 text-base text-black-400 font-semibold">$200.00</td>
                        <td class="border border-accent-200 px-4 py-3">
                            <span class="bg-success-200 text-accent-50 px-3 py-1 rounded-full text-2xs font-semibold">Pagado</span>
                        </td>
                        <td class="border border-accent-200 px-4 py-3">
                            <div class="flex items-center gap-2">
                                <button type="button" class="p-2 bg-info-50 text-info-200 hover:bg-info-50 rounded-lg transition-colors" title="Ver">
                                    <x-solar-eye-outline class="w-4 h-4" />
                                </button>
                                <button type="button" class="p-2 bg-secondary-50 text-secondary-200 hover:bg-secondary-50 rounded-lg transition-colors" title="Editar">
                                    <x-solar-pen-outline class="w-4 h-4" />
                                </button>
                                <button type="button" class="p-2 bg-error-50 text-error-200 hover:bg-error-50 rounded-lg transition-colors" title="Eliminar">
                                    <x-solar-trash-bin-minimalistic-outline class="w-4 h-4" />
                                </button>
                            </div>
                        </td>
                    </tr>
                    
                    <tr class="hover:bg-accent-100 transition-colors">
                        <td class="border border-accent-200 px-4 py-3">
                            <div class="flex items-center gap-2">
                                <input type="checkbox" class="invoice-checkbox w-4 h-4 text-primary-200 border-accent-300 rounded">
                                <span class="text-base text-black-400">02</span>
                            </div>
                        </td>
                        <td class="border border-accent-200 px-4 py-3">
                            <a href="#" class="text-primary-200 hover:text-primary-300 text-base font-semibold">#696589</a>
                        </td>
                        <td class="border border-accent-200 px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-secondary-100 rounded-full flex items-center justify-center">
                                    <span class="text-secondary-200 font-semibold text-base">AB</span>
                                </div>
                                <div>
                                    <p class="text-base text-black-400 font-semibold mb-0">Annette Black</p>
                                    <p class="text-sm text-black-300 mb-0">annette@email.com</p>
                                </div>
                            </div>
                        </td>
                        <td class="border border-accent-200 px-4 py-3 text-base text-black-400">25 Jan 2025</td>
                        <td class="border border-accent-200 px-4 py-3 text-base text-black-400 font-semibold">$200.00</td>
                        <td class="border border-accent-200 px-4 py-3">
                            <span class="bg-success-200 text-accent-50 px-3 py-1 rounded-full text-2xs font-semibold">Pagado</span>
                        </td>
                        <td class="border border-accent-200 px-4 py-3">
                            <div class="flex items-center gap-2">
                                <button type="button" class="p-2 bg-info-50 text-info-200 hover:bg-info-50 rounded-lg transition-colors" title="Ver">
                                    <x-solar-eye-outline class="w-4 h-4" />
                                </button>
                                <button type="button" class="p-2 bg-secondary-50 text-secondary-200 hover:bg-secondary-50 rounded-lg transition-colors" title="Editar">
                                    <x-solar-pen-outline class="w-4 h-4" />
                                </button>
                                <button type="button" class="p-2 bg-error-50 text-error-200 hover:bg-error-50 rounded-lg transition-colors" title="Eliminar">
                                    <x-solar-trash-bin-minimalistic-outline class="w-4 h-4" />
                                </button>
                            </div>
                        </td>
                    </tr>

                    <tr class="hover:bg-accent-100 transition-colors">
                        <td class="border border-accent-200 px-4 py-3">
                            <div class="flex items-center gap-2">
                                <input type="checkbox" class="invoice-checkbox w-4 h-4 text-primary-200 border-accent-300 rounded">
                                <span class="text-base text-black-400">03</span>
                            </div>
                        </td>
                        <td class="border border-accent-200 px-4 py-3">
                            <a href="#" class="text-primary-200 hover:text-primary-300 text-base font-semibold">#256584</a>
                        </td>
                        <td class="border border-accent-200 px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-warning-100 rounded-full flex items-center justify-center">
                                    <span class="text-warning-200 font-semibold text-base">RR</span>
                                </div>
                                <div>
                                    <p class="text-base text-black-400 font-semibold mb-0">Ronald Richards</p>
                                    <p class="text-sm text-black-300 mb-0">ronald@email.com</p>
                                </div>
                            </div>
                        </td>
                        <td class="border border-accent-200 px-4 py-3 text-base text-black-400">10 Feb 2025</td>
                        <td class="border border-accent-200 px-4 py-3 text-base text-black-400 font-semibold">$200.00</td>
                        <td class="border border-accent-200 px-4 py-3">
                            <span class="bg-warning-200 text-accent-50 px-3 py-1 rounded-full text-2xs font-semibold">Pendiente</span>
                        </td>
                        <td class="border border-accent-200 px-4 py-3">
                            <div class="flex items-center gap-2">
                                <button type="button" class="p-2 bg-info-50 text-info-200 hover:bg-info-50 rounded-lg transition-colors" title="Ver">
                                    <x-solar-eye-outline class="w-4 h-4" />
                                </button>
                                <button type="button" class="p-2 bg-secondary-50 text-secondary-200 hover:bg-secondary-50 rounded-lg transition-colors" title="Editar">
                                    <x-solar-pen-outline class="w-4 h-4" />
                                </button>
                                <button type="button" class="p-2 bg-error-50 text-error-200 hover:bg-error-50 rounded-lg transition-colors" title="Eliminar">
                                    <x-solar-trash-bin-minimalistic-outline class="w-4 h-4" />
                                </button>
                            </div>
                        </td>
                    </tr>

                    <tr class="hover:bg-accent-100 transition-colors">
                        <td class="border border-accent-200 px-4 py-3">
                            <div class="flex items-center gap-2">
                                <input type="checkbox" class="invoice-checkbox w-4 h-4 text-primary-200 border-accent-300 rounded">
                                <span class="text-base text-black-400">04</span>
                            </div>
                        </td>
                        <td class="border border-accent-200 px-4 py-3">
                            <a href="#" class="text-primary-200 hover:text-primary-300 text-base font-semibold">#526587</a>
                        </td>
                        <td class="border border-accent-200 px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-error-100 rounded-full flex items-center justify-center">
                                    <span class="text-error-200 font-semibold text-base">EP</span>
                                </div>
                                <div>
                                    <p class="text-base text-black-400 font-semibold mb-0">Eleanor Pena</p>
                                    <p class="body-small text-black-300 mb-0">eleanor@email.com</p>
                                </div>
                            </div>
                        </td>
                        <td class="border border-accent-200 px-4 py-3 text-base text-black-400">10 Feb 2025</td>
                        <td class="border border-accent-200 px-4 py-3 text-base text-black-400 font-semibold">$150.00</td>
                        <td class="border border-accent-200 px-4 py-3">
                            <span class="bg-error-200 text-accent-50 px-3 py-1 rounded-full text-2xs font-semibold">Vencido</span>
                        </td>
                        <td class="border border-accent-200 px-4 py-3">
                            <div class="flex items-center gap-2">
                                <button type="button" class="p-2 bg-info-50 text-info-200 hover:bg-info-50 rounded-lg transition-colors" title="Ver">
                                    <x-solar-eye-outline class="w-4 h-4" />
                                </button>
                                <button type="button" class="p-2 bg-secondary-50 text-secondary-200 hover:bg-secondary-50 rounded-lg transition-colors" title="Editar">
                                    <x-solar-pen-outline class="w-4 h-4" />
                                </button>
                                <button type="button" class="p-2 bg-error-50 text-error-200 hover:bg-error-50 rounded-lg transition-colors" title="Eliminar">
                                    <x-solar-trash-bin-minimalistic-outline class="w-4 h-4" />
                                </button>
                            </div>
                        </td>
                    </tr>

                    <tr class="hover:bg-accent-100 transition-colors">
                        <td class="border border-accent-200 px-4 py-3">
                            <div class="flex items-center gap-2">
                                <input type="checkbox" class="invoice-checkbox w-4 h-4 text-primary-200 border-accent-300 rounded">
                                <span class="text-base text-black-400">05</span>
                            </div>
                        </td>
                        <td class="border border-accent-200 px-4 py-3">
                            <a href="#" class="text-primary-200 hover:text-primary-300 text-base font-semibold">#105986</a>
                        </td>
                        <td class="border border-accent-200 px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-info-100 rounded-full flex items-center justify-center">
                                    <span class="text-info-200 font-semibold text-base">LA</span>
                                </div>
                                <div>
                                    <p class="text-base text-black-400 font-semibold mb-0">Leslie Alexander</p>
                                    <p class="text-sm text-black-300 mb-0">leslie@email.com</p>
                                </div>
                            </div>
                        </td>
                        <td class="border border-accent-200 px-4 py-3 text-base text-black-400">15 March 2025</td>
                        <td class="border border-accent-200 px-4 py-3 text-base text-black-400 font-semibold">$150.00</td>
                        <td class="border border-accent-200 px-4 py-3">
                            <span class="bg-warning-200 text-accent-50 px-3 py-1 rounded-full text-2xs font-semibold">Pendiente</span>
                        </td>
                        <td class="border border-accent-200 px-4 py-3">
                            <div class="flex items-center gap-2">
                                <button type="button" class="p-2 bg-info-50 text-info-200 hover:bg-info-50 rounded-lg transition-colors" title="Ver">
                                    <x-solar-eye-outline class="w-4 h-4" />
                                </button>
                                <button type="button" class="p-2 bg-secondary-50 text-secondary-200 hover:bg-secondary-50 rounded-lg transition-colors" title="Editar">
                                    <x-solar-pen-outline class="w-4 h-4" />
                                </button>
                                <button type="button" class="p-2 bg-error-50 text-error-200 hover:bg-error-50 rounded-lg transition-colors" title="Eliminar">
                                    <x-solar-trash-bin-minimalistic-outline class="w-4 h-4" />
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        <div class="flex flex-wrap items-center justify-between gap-4 mt-6">
            <div class="flex items-center gap-2">
                <button type="button" id="deleteSelected" class="bg-error-200 text-accent-50 px-4 py-2 rounded-lg inline-flex items-center gap-2 hover:bg-error-300 transition-colors disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                    <x-solar-trash-bin-minimalistic-outline class="w-5 h-5" />
                    <span class="text-base">Eliminar Seleccionados</span>
                </button>
                <span class="text-base text-black-400">Mostrando 1 a 5 de 12 entradas</span>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" class="w-8 h-8 bg-accent-50 border border-accent-200 rounded-lg flex items-center justify-center text-black-400 hover:bg-accent-100 transition-colors">
                    <x-solar-arrow-left-outline class="w-4 h-4" />
                </button>
                <button type="button" class="w-8 h-8 bg-primary-200 text-accent-50 rounded-lg flex items-center justify-center">1</button>
                <button type="button" class="w-8 h-8 bg-accent-50 border border-accent-200 rounded-lg flex items-center justify-center text-black-400 hover:bg-accent-100 transition-colors">2</button>
                <button type="button" class="w-8 h-8 bg-accent-50 border border-accent-200 rounded-lg flex items-center justify-center text-black-400 hover:bg-accent-100 transition-colors">3</button>
                <button type="button" class="w-8 h-8 bg-accent-50 border border-accent-200 rounded-lg flex items-center justify-center text-black-400 hover:bg-accent-100 transition-colors">
                    <x-solar-arrow-right-outline class="w-4 h-4" />
                </button>
            </div>
        </div>
    </div>
</div>
@endsection 