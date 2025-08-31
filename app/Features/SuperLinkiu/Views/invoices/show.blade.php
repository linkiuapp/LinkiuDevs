@extends('shared::layouts.admin')

@section('title', 'Factura #' . $invoice->invoice_number)

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('superlinkiu.invoices.index') }}" 
               class="bg-accent-100 hover:bg-accent-200 text-black-400 px-4 py-2 rounded-lg transition-colors flex items-center gap-2">
                <x-solar-arrow-left-outline class="w-4 h-4" />
                Volver
            </a>
            <h1 class="text-lg font-bold text-black-400">Factura #{{ $invoice->invoice_number }}</h1>
            <span class="px-3 py-1 rounded-full text-xs font-medium {{ $invoice->getStatusColorClass() }}">
                {{ $invoice->getStatusLabel() }}
            </span>
        </div>
        
        <div class="flex items-center gap-3">
            @if(!$invoice->isPaid() && !$invoice->isCancelled())
                <button onclick="markAsPaid({{ $invoice->id }})" 
                        class="bg-success-300 hover:bg-success-400 text-accent-50 px-4 py-2 rounded-lg transition-colors flex items-center gap-2">
                    <x-solar-check-circle-outline class="w-4 h-4" />
                    Marcar como Pagada
                </button>
                
                <button onclick="cancelInvoice({{ $invoice->id }})" 
                        class="bg-error-300 hover:bg-error-400 text-accent-50 px-4 py-2 rounded-lg transition-colors flex items-center gap-2">
                    <x-solar-close-circle-outline class="w-4 h-4" />
                    Cancelar Factura
                </button>
            @endif
            
            <a href="{{ route('superlinkiu.invoices.edit', $invoice) }}" 
               class="bg-primary-200 hover:bg-primary-300 text-accent-50 px-4 py-2 rounded-lg transition-colors flex items-center gap-2">
                <x-solar-pen-outline class="w-4 h-4" />
                Editar
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Información Principal -->
        <div class="lg:col-span-2">
            <!-- Detalles de la Factura -->
            <div class="bg-accent-50 rounded-lg p-0 overflow-hidden shadow-sm mb-6">
                <div class="border-b border-accent-100 bg-accent-50 py-4 px-6">
                    <h2 class="text-lg text-black-500 mb-0 font-semibold">Detalles de la Factura</h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-sm font-semibold text-black-400 mb-3">Información Básica</h3>
                            <div class="space-y-3">
                                <div>
                                    <span class="text-sm text-black-300">Número:</span>
                                    <span class="text-sm font-medium text-black-500 ml-2">#{{ $invoice->invoice_number }}</span>
                                </div>
                                <div>
                                    <span class="text-sm text-black-300">Estado:</span>
                                    <span class="ml-2 px-2 py-1 rounded-full text-xs font-medium {{ $invoice->getStatusColorClass() }}">
                                        {{ $invoice->getStatusLabel() }}
                                    </span>
                                </div>
                                <div>
                                    <span class="text-sm text-black-300">Monto:</span>
                                    <span class="text-lg font-bold text-primary-300 ml-2">${{ number_format($invoice->amount, 0, ',', '.') }}</span>
                                </div>
                                <div>
                                    <span class="text-sm text-black-300">Período:</span>
                                    <span class="text-sm font-medium text-black-500 ml-2">{{ $invoice->getPeriodLabel() }}</span>
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <h3 class="text-sm font-semibold text-black-400 mb-3">Fechas Importantes</h3>
                            <div class="space-y-3">
                                <div>
                                    <span class="text-sm text-black-300">Fecha de Emisión:</span>
                                    <span class="text-sm font-medium text-black-500 ml-2">{{ $invoice->issue_date->format('d/m/Y') }}</span>
                                </div>
                                <div>
                                    <span class="text-sm text-black-300">Fecha de Vencimiento:</span>
                                    <span class="text-sm font-medium text-black-500 ml-2">{{ $invoice->due_date->format('d/m/Y') }}</span>
                                </div>
                                @if($invoice->paid_date)
                                    <div>
                                        <span class="text-sm text-black-300">Fecha de Pago:</span>
                                        <span class="text-sm font-medium text-success-300 ml-2">{{ $invoice->paid_date->format('d/m/Y H:i') }}</span>
                                    </div>
                                @endif
                                <div>
                                    <span class="text-sm text-black-300">Creada:</span>
                                    <span class="text-sm text-black-200 ml-2">{{ $invoice->created_at->format('d/m/Y H:i') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    @if($invoice->notes)
                        <div class="mt-6 pt-6 border-t border-accent-100">
                            <h3 class="text-sm font-semibold text-black-400 mb-2">Notas</h3>
                            <p class="text-sm text-black-300 bg-accent-100 p-3 rounded-lg">{{ $invoice->notes }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Historial de Estados -->
            @if($invoice->status_history)
                <div class="bg-accent-50 rounded-lg p-0 overflow-hidden shadow-sm">
                    <div class="border-b border-accent-100 bg-accent-50 py-4 px-6">
                        <h2 class="text-lg text-black-500 mb-0 font-semibold">Historial de Estados</h2>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            @foreach($invoice->status_history as $history)
                                <div class="flex items-center gap-3 p-3 bg-accent-100 rounded-lg">
                                    <div class="w-2 h-2 rounded-full {{ $history['status'] === 'paid' ? 'bg-success-300' : ($history['status'] === 'cancelled' ? 'bg-error-300' : 'bg-warning-300') }}"></div>
                                    <div class="flex-1">
                                        <div class="text-sm font-medium text-black-500">{{ $history['status_label'] }}</div>
                                        <div class="text-xs text-black-300">{{ $history['changed_at'] }}</div>
                                    </div>
                                    @if(isset($history['reason']))
                                        <div class="text-xs text-black-200">{{ $history['reason'] }}</div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-1">
            <!-- Información de la Tienda -->
            <div class="bg-accent-50 rounded-lg p-0 overflow-hidden shadow-sm mb-6">
                <div class="border-b border-accent-100 bg-accent-50 py-4 px-6">
                    <h2 class="text-lg text-black-500 mb-0 font-semibold">Tienda</h2>
                </div>
                <div class="p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 bg-primary-200 rounded-full flex items-center justify-center">
                            <span class="text-accent-50 font-medium">{{ substr($invoice->store->name, 0, 1) }}</span>
                        </div>
                        <div>
                            <h3 class="font-semibold text-black-500">{{ $invoice->store->name }}</h3>
                            <p class="text-sm text-black-300">{{ $invoice->store->email }}</p>
                        </div>
                    </div>
                    
                    <div class="space-y-2">
                        @if($invoice->store->document_number)
                            <div class="text-sm">
                                <span class="text-black-300">{{ strtoupper($invoice->store->document_type) }}:</span>
                                <span class="text-black-500 ml-1">{{ $invoice->store->document_number }}</span>
                            </div>
                        @endif
                        @if($invoice->store->phone)
                            <div class="text-sm">
                                <span class="text-black-300">Teléfono:</span>
                                <span class="text-black-500 ml-1">{{ $invoice->store->phone }}</span>
                            </div>
                        @endif
                        @if($invoice->store->city || $invoice->store->department)
                            <div class="text-sm">
                                <span class="text-black-300">Ubicación:</span>
                                <span class="text-black-500 ml-1">
                                    {{ $invoice->store->city }}@if($invoice->store->city && $invoice->store->department), @endif{{ $invoice->store->department }}
                                </span>
                            </div>
                        @endif
                    </div>
                    
                    <div class="mt-4 pt-4 border-t border-accent-100">
                        <a href="{{ route('superlinkiu.stores.show', $invoice->store) }}" 
                           class="text-primary-200 hover:text-primary-300 text-sm font-medium transition-colors">
                            Ver tienda completa →
                        </a>
                    </div>
                </div>
            </div>

            <!-- Información del Plan -->
            <div class="bg-accent-50 rounded-lg p-0 overflow-hidden shadow-sm mb-6">
                <div class="border-b border-accent-100 bg-accent-50 py-4 px-6">
                    <h2 class="text-lg text-black-500 mb-0 font-semibold">Plan</h2>
                </div>
                <div class="p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 bg-secondary-200 rounded-full flex items-center justify-center">
                            <x-solar-crown-outline class="w-5 h-5 text-accent-50" />
                        </div>
                        <div>
                            <h3 class="font-semibold text-black-500">{{ $invoice->plan->name }}</h3>
                            <p class="text-sm text-black-300">Plan {{ ucfirst($invoice->period) }}</p>
                        </div>
                    </div>
                    
                    @if($invoice->plan->description)
                        <p class="text-sm text-black-300 mb-4">{{ $invoice->plan->description }}</p>
                    @endif
                    
                    <div class="space-y-2">
                        <div class="text-sm">
                            <span class="text-black-300">Precio base:</span>
                            <span class="text-black-500 ml-1">${{ number_format($invoice->plan->price, 0, ',', '.') }}</span>
                        </div>
                        <div class="text-sm">
                            <span class="text-black-300">Duración:</span>
                            <span class="text-black-500 ml-1">{{ $invoice->plan->duration_in_days }} días</span>
                        </div>
                    </div>
                    
                    <div class="mt-4 pt-4 border-t border-accent-100">
                        <a href="{{ route('superlinkiu.plans.show', $invoice->plan) }}" 
                           class="text-primary-200 hover:text-primary-300 text-sm font-medium transition-colors">
                            Ver plan completo →
                        </a>
                    </div>
                </div>
            </div>

            <!-- Acciones Rápidas -->
            <div class="bg-accent-50 rounded-lg p-0 overflow-hidden shadow-sm">
                <div class="border-b border-accent-100 bg-accent-50 py-4 px-6">
                    <h2 class="text-lg text-black-500 mb-0 font-semibold">Acciones</h2>
                </div>
                <div class="p-6">
                    <div class="space-y-3">
                        @if(!$invoice->isPaid() && !$invoice->isCancelled())
                            <button onclick="markAsPaid({{ $invoice->id }})" 
                                    class="w-full bg-success-300 hover:bg-success-400 text-accent-50 px-4 py-2 rounded-lg transition-colors flex items-center justify-center gap-2">
                                <x-solar-check-circle-outline class="w-4 h-4" />
                                Marcar como Pagada
                            </button>
                            
                            <button onclick="cancelInvoice({{ $invoice->id }})" 
                                    class="w-full bg-error-300 hover:bg-error-400 text-accent-50 px-4 py-2 rounded-lg transition-colors flex items-center justify-center gap-2">
                                <x-solar-close-circle-outline class="w-4 h-4" />
                                Cancelar Factura
                            </button>
                        @endif
                        
                        <a href="{{ route('superlinkiu.invoices.edit', $invoice) }}" 
                           class="w-full bg-primary-200 hover:bg-primary-300 text-accent-50 px-4 py-2 rounded-lg transition-colors flex items-center justify-center gap-2">
                            <x-solar-pen-outline class="w-4 h-4" />
                            Editar Factura
                        </a>
                        
                        @if(!$invoice->isPaid())
                            <button onclick="deleteInvoice({{ $invoice->id }})" 
                                    class="w-full bg-accent-100 hover:bg-error-100 text-error-300 border border-error-200 hover:border-error-300 px-4 py-2 rounded-lg transition-colors flex items-center justify-center gap-2">
                                <x-solar-trash-bin-trash-outline class="w-4 h-4" />
                                Eliminar Factura
                            </button>
                        @endif
                        
                        <a href="{{ route('superlinkiu.invoices.generate-for-store', $invoice->store) }}" 
                           class="w-full bg-info-200 hover:bg-info-300 text-accent-50 px-4 py-2 rounded-lg transition-colors flex items-center justify-center gap-2">
                            <x-solar-add-circle-outline class="w-4 h-4" />
                            Generar Nueva Factura
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modales -->
<!-- Modal Marcar como Pagada -->
<div id="markAsPaidModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-50">
    <div class="bg-accent-50 rounded-lg p-6 w-full max-w-md">
        <h3 class="text-lg font-semibold text-black-500 mb-4">Marcar como Pagada</h3>
        <form id="markAsPaidForm">
            <div class="mb-4">
                <label for="paid_date" class="block text-sm font-medium text-black-400 mb-2">
                    Fecha de Pago (opcional)
                </label>
                <input type="date" 
                       id="paid_date" 
                       name="paid_date"
                       class="w-full px-3 py-2 border border-accent-200 rounded-lg focus:ring-2 focus:ring-primary-200 focus:border-primary-300"
                       value="{{ now()->format('Y-m-d') }}">
            </div>
            <div class="flex gap-3 justify-end">
                <button type="button" onclick="closeModal('markAsPaidModal')" 
                        class="px-4 py-2 text-black-400 hover:text-black-500">
                    Cancelar
                </button>
                <button type="submit" 
                        class="px-4 py-2 bg-success-300 text-accent-50 rounded-lg hover:bg-success-400">
                    Confirmar Pago
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Cancelar Factura -->
<div id="cancelInvoiceModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-50">
    <div class="bg-accent-50 rounded-lg p-6 w-full max-w-md">
        <h3 class="text-lg font-semibold text-error-300 mb-4">Cancelar Factura</h3>
        <form id="cancelInvoiceForm">
            <div class="mb-4">
                <label for="reason" class="block text-sm font-medium text-black-400 mb-2">
                    Razón de cancelación (opcional)
                </label>
                <textarea id="reason" 
                          name="reason"
                          rows="3"
                          class="w-full px-3 py-2 border border-accent-200 rounded-lg focus:ring-2 focus:ring-primary-200 focus:border-primary-300"
                          placeholder="Describe la razón de la cancelación..."></textarea>
            </div>
            <div class="flex gap-3 justify-end">
                <button type="button" onclick="closeModal('cancelInvoiceModal')" 
                        class="px-4 py-2 text-black-400 hover:text-black-500">
                    Cancelar
                </button>
                <button type="submit" 
                        class="px-4 py-2 bg-error-300 text-accent-50 rounded-lg hover:bg-error-400">
                    Confirmar Cancelación
                </button>
            </div>
        </form>
    </div>
</div>

<script>
let currentInvoiceId = null;

function markAsPaid(invoiceId) {
    currentInvoiceId = invoiceId;
    document.getElementById('markAsPaidModal').classList.remove('hidden');
    document.getElementById('markAsPaidModal').classList.add('flex');
}

function cancelInvoice(invoiceId) {
    currentInvoiceId = invoiceId;
    document.getElementById('cancelInvoiceModal').classList.remove('hidden');
    document.getElementById('cancelInvoiceModal').classList.add('flex');
}

function deleteInvoice(invoiceId) {
    if (confirm('¿Estás seguro de que quieres eliminar esta factura? Esta acción no se puede deshacer.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/superlinkiu/invoices/${invoiceId}`;
        form.innerHTML = `
            @csrf
            @method('DELETE')
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
    document.getElementById(modalId).classList.remove('flex');
    currentInvoiceId = null;
}

// Manejar formulario de marcar como pagada
document.getElementById('markAsPaidForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const paidDate = document.getElementById('paid_date').value;
    
    fetch(`/superlinkiu/invoices/${currentInvoiceId}/mark-as-paid`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            paid_date: paidDate
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al procesar la solicitud');
    });
});

// Manejar formulario de cancelar factura
document.getElementById('cancelInvoiceForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const reason = document.getElementById('reason').value;
    
    fetch(`/superlinkiu/invoices/${currentInvoiceId}/cancel`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            reason: reason
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al procesar la solicitud');
    });
});

// Cerrar modales al hacer click fuera
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('bg-black')) {
        const modals = document.querySelectorAll('[id$="Modal"]');
        modals.forEach(modal => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        });
        currentInvoiceId = null;
    }
});
</script>
@endsection 