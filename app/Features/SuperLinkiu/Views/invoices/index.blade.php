@extends('shared::layouts.admin')

@section('title', 'Gestión de Facturas')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-lg font-bold text-black-400">Gestión de Facturas</h1>
        <a href="{{ route('superlinkiu.invoices.create') }}" class="bg-primary-200 hover:bg-primary-300 text-accent-50 px-4 py-2 rounded-lg flex items-center gap-2 transition-colors">
            <x-solar-add-circle-outline class="w-5 h-5" />
            Nueva Factura
        </a>
    </div>

    <!-- Estadísticas -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
        <div class="bg-accent-50 rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-black-300">Total Facturas</p>
                    <p class="text-2xl font-bold text-black-500">{{ $stats['total'] }}</p>
                </div>
                <x-solar-document-text-outline class="w-8 h-8 text-primary-300" />
            </div>
        </div>
        <div class="bg-accent-50 rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-black-300">Pendientes</p>
                    <p class="text-2xl font-bold text-warning-300">{{ $stats['pending'] }}</p>
                </div>
                <x-solar-clock-circle-outline class="w-8 h-8 text-warning-300" />
            </div>
        </div>
        <div class="bg-accent-50 rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-black-300">Pagadas</p>
                    <p class="text-2xl font-bold text-success-300">{{ $stats['paid'] }}</p>
                </div>
                <x-solar-check-circle-outline class="w-8 h-8 text-success-300" />
            </div>
        </div>
        <div class="bg-accent-50 rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-black-300">Vencidas</p>
                    <p class="text-2xl font-bold text-error-300">{{ $stats['overdue'] }}</p>
                </div>
                <x-solar-danger-triangle-outline class="w-8 h-8 text-error-300" />
            </div>
        </div>
        <div class="bg-accent-50 rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-black-300">Ingresos Totales</p>
                    <p class="text-2xl font-bold text-primary-300">${{ number_format($stats['total_amount'], 0, ',', '.') }}</p>
                </div>
                <x-solar-dollar-outline class="w-8 h-8 text-primary-300" />
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="bg-accent-50 rounded-lg p-6 mb-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-6 gap-4">
            <div>
                <label class="block text-sm font-medium text-black-400 mb-2">Buscar</label>
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}"
                       placeholder="Número de factura o tienda"
                       class="w-full px-3 py-2 border border-accent-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-200">
            </div>
            <div>
                <label class="block text-sm font-medium text-black-400 mb-2">Tienda</label>
                <select name="store_id" class="w-full px-3 py-2 border border-accent-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-200">
                    <option value="">Todas las tiendas</option>
                    @foreach($stores as $store)
                        <option value="{{ $store->id }}" {{ request('store_id') == $store->id ? 'selected' : '' }}>
                            {{ $store->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-black-400 mb-2">Plan</label>
                <select name="plan_id" class="w-full px-3 py-2 border border-accent-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-200">
                    <option value="">Todos los planes</option>
                    @foreach($plans as $plan)
                        <option value="{{ $plan->id }}" {{ request('plan_id') == $plan->id ? 'selected' : '' }}>
                            {{ $plan->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-black-400 mb-2">Estado</label>
                <select name="status" class="w-full px-3 py-2 border border-accent-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-200">
                    <option value="">Todos los estados</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pendiente</option>
                    <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Pagada</option>
                    <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Vencida</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelada</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-black-400 mb-2">Período</label>
                <select name="period" class="w-full px-3 py-2 border border-accent-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-200">
                    <option value="">Todos los períodos</option>
                    <option value="monthly" {{ request('period') == 'monthly' ? 'selected' : '' }}>Mensual</option>
                    <option value="quarterly" {{ request('period') == 'quarterly' ? 'selected' : '' }}>Trimestral</option>
                    <option value="biannual" {{ request('period') == 'biannual' ? 'selected' : '' }}>Semestral</option>
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="bg-primary-200 hover:bg-primary-300 text-accent-50 px-4 py-2 rounded-lg transition-colors">
                    <x-solar-magnifer-outline class="w-4 h-4" />
                </button>
                <a href="{{ route('superlinkiu.invoices.index') }}" class="bg-accent-200 hover:bg-accent-300 text-black-400 px-4 py-2 rounded-lg transition-colors">
                    <x-solar-refresh-outline class="w-4 h-4" />
                </a>
            </div>
        </form>
    </div>

    <!-- Tabla de Facturas -->
    <div class="bg-accent-50 rounded-lg p-0 overflow-hidden">
        <div class="border-b border-accent-100 bg-accent-50 py-4 px-6">
            <h2 class="text-lg text-black-500 mb-0">Lista de Facturas</h2>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-accent-100">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-black-400 uppercase tracking-wider">
                            Número
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-black-400 uppercase tracking-wider">
                            Tienda
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-black-400 uppercase tracking-wider">
                            Plan
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-black-400 uppercase tracking-wider">
                            Monto
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-black-400 uppercase tracking-wider">
                            Período
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-black-400 uppercase tracking-wider">
                            Estado
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-black-400 uppercase tracking-wider">
                            Vencimiento
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-black-400 uppercase tracking-wider">
                            Acciones
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-accent-50 divide-y divide-accent-100">
                    @forelse($invoices as $invoice)
                        <tr class="hover:bg-accent-100 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-black-500">{{ $invoice->invoice_number }}</div>
                                <div class="text-sm text-black-300">{{ $invoice->issue_date->format('d/m/Y') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($invoice->store)
                                    <div class="text-sm text-black-500">{{ $invoice->store->name }}</div>
                                    <div class="text-sm text-black-300">{{ $invoice->store->email }}</div>
                                @else
                                    <div class="text-sm text-gray-500">Sin tienda asignada</div>
                                    <div class="text-sm text-gray-300">-</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-black-500">{{ $invoice->plan->name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-black-500">{{ $invoice->getFormattedAmount() }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-black-500">{{ $invoice->getPeriodLabel() }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="bg-{{ $invoice->getStatusColor() }}-200 text-accent-50 px-2 py-1 rounded text-xs">
                                    {{ $invoice->getStatusLabel() }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-black-500">{{ $invoice->due_date->format('d/m/Y') }}</div>
                                @if($invoice->isOverdue())
                                    <div class="text-xs text-error-300">
                                        Vencida hace {{ $invoice->getDaysOverdue() }} días
                                    </div>
                                @elseif($invoice->isPending())
                                    <div class="text-xs text-warning-300">
                                        Vence en {{ $invoice->getDaysUntilDue() }} días
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('superlinkiu.invoices.show', $invoice) }}" 
                                       class="text-primary-300 hover:text-primary-200 transition-colors">
                                        <x-solar-eye-outline class="w-4 h-4" />
                                    </a>
                                    @if(!$invoice->isPaid())
                                        <a href="{{ route('superlinkiu.invoices.edit', $invoice) }}" 
                                           class="text-info-300 hover:text-info-200 transition-colors">
                                            <x-solar-pen-outline class="w-4 h-4" />
                                        </a>
                                    @endif
                                    @if($invoice->isPending())
                                        <button onclick="markAsPaid({{ $invoice->id }})" 
                                                class="text-success-300 hover:text-success-200 transition-colors">
                                            <x-solar-check-circle-outline class="w-4 h-4" />
                                        </button>
                                    @endif
                                    @if(!$invoice->isPaid())
                                        <form action="{{ route('superlinkiu.invoices.destroy', $invoice) }}" 
                                              method="POST" 
                                              class="inline"
                                              onsubmit="return confirm('¿Estás seguro de eliminar esta factura?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="text-error-300 hover:text-error-200 transition-colors">
                                                <x-solar-trash-bin-trash-outline class="w-4 h-4" />
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center">
                                <x-solar-document-text-outline class="w-16 h-16 mx-auto mb-4 text-black-100" />
                                <p class="text-black-300">No hay facturas registradas</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Paginación -->
    @if ($invoices->hasPages())
    <div class="mt-6">
        <ul class="pagination flex flex-wrap items-center gap-2 justify-center">
            {{-- Previous Page Link --}}
            @if ($invoices->onFirstPage())
                <li class="page-item">
                    <span class="page-link bg-accent-100 border border-accent-200 text-black-200 font-medium rounded-lg px-3 py-2 flex items-center justify-center h-[40px] cursor-not-allowed">
                        <x-solar-arrow-left-outline class="w-4 h-4" />
                    </span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link bg-accent-50 border border-accent-200 text-black-400 font-medium rounded-lg px-3 py-2 flex items-center justify-center h-[40px] hover:bg-primary-50 hover:border-primary-200 hover:text-primary-400 transition-colors" 
                       href="{{ $invoices->previousPageUrl() }}">
                        <x-solar-arrow-left-outline class="w-4 h-4" />
                    </a>
                </li>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($invoices->getUrlRange(1, $invoices->lastPage()) as $page => $url)
                @if ($page == $invoices->currentPage())
                    <li class="page-item">
                        <span class="page-link bg-primary-200 text-accent-50 font-medium rounded-lg px-3 py-2 flex items-center justify-center h-[40px] w-[40px]">
                            {{ $page }}
                        </span>
                    </li>
                @else
                    <li class="page-item">
                        <a class="page-link bg-accent-50 border border-accent-200 text-black-400 font-medium rounded-lg px-3 py-2 flex items-center justify-center h-[40px] w-[40px] hover:bg-primary-50 hover:border-primary-200 hover:text-primary-400 transition-colors" 
                           href="{{ $url }}">
                            {{ $page }}
                        </a>
                    </li>
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($invoices->hasMorePages())
                <li class="page-item">
                    <a class="page-link bg-accent-50 border border-accent-200 text-black-400 font-medium rounded-lg px-3 py-2 flex items-center justify-center h-[40px] hover:bg-primary-50 hover:border-primary-200 hover:text-primary-400 transition-colors" 
                       href="{{ $invoices->nextPageUrl() }}">
                        <x-solar-arrow-right-outline class="w-4 h-4" />
                    </a>
                </li>
            @else
                <li class="page-item">
                    <span class="page-link bg-accent-100 border border-accent-200 text-black-200 font-medium rounded-lg px-3 py-2 flex items-center justify-center h-[40px] cursor-not-allowed">
                        <x-solar-arrow-right-outline class="w-4 h-4" />
                    </span>
                </li>
            @endif
        </ul>
    </div>
    @endif
</div>

<script>
function markAsPaid(invoiceId) {
    if (confirm('¿Marcar esta factura como pagada?')) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        
        if (!csrfToken) {
            alert('Error: Token CSRF no encontrado');
            return;
        }

        fetch(`/superlinkiu/invoices/${invoiceId}/mark-as-paid`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken.getAttribute('content'),
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify({})
        })
        .then(response => {
            console.log('Response status:', response.status);
            console.log('Response headers:', response.headers);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            return response.json();
        })
        .then(data => {
            console.log('Response data:', data);
            
            if (data.success) {
                // Mostrar mensaje de éxito
                alert('Factura marcada como pagada exitosamente');
                location.reload();
            } else {
                alert('Error: ' + (data.message || 'Error desconocido'));
            }
        })
        .catch(error => {
            console.error('Error completo:', error);
            
            // Mostrar error más específico
            if (error.message.includes('404')) {
                alert('Error 404: Ruta no encontrada. Verifica que la factura existe.');
            } else if (error.message.includes('419')) {
                alert('Error 419: Token CSRF expirado. Recarga la página e intenta de nuevo.');
            } else if (error.message.includes('500')) {
                alert('Error 500: Error interno del servidor. Revisa los logs.');
            } else {
                alert('Error al marcar como pagada: ' + error.message);
            }
        });
    }
}
</script>
@endsection 