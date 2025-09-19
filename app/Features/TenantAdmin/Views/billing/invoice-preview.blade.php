<!-- Vista previa de factura para modal - Solo contenido -->
<div class="bg-white">
    <!-- Header de la factura -->
    <div class="flex justify-between items-start mb-6 pb-4 border-b border-gray-200">
        <div>
            <h2 class="text-xl font-bold text-black-500 mb-1">Factura #{{ $invoice->invoice_number }}</h2>
            <p class="text-sm text-black-300 mb-1">Fecha de emisión: {{ $invoice->issue_date->format('d/m/Y') }}</p>
            <p class="text-sm text-black-300">Fecha de vencimiento: {{ $invoice->due_date->format('d/m/Y') }}</p>
        </div>
        <div class="text-right">
            @if($billingSettings->logo_url)
                @php
                    $logoUrl = $billingSettings->logo_url;
                    if (strpos($logoUrl, '/storage/') === 0) {
                        $logoUrl = asset($logoUrl);
                    } elseif (!filter_var($logoUrl, FILTER_VALIDATE_URL)) {
                        $logoUrl = asset('storage/' . ltrim($logoUrl, '/storage/'));
                    }
                @endphp
                <img src="{{ $logoUrl }}" alt="Logo" class="h-16 mb-3 ml-auto">
            @endif
            <div class="text-sm text-black-400">
                <p class="font-semibold text-black-500">{{ $billingSettings->company_name ?? 'Linkiu.bio' }}</p>
                @if($billingSettings->company_address)
                    <p>{{ $billingSettings->company_address }}</p>
                @endif
                @if($billingSettings->tax_id)
                    <p>{{ $billingSettings->tax_id }}</p>
                @endif
                @if($billingSettings->phone)
                    <p>{{ $billingSettings->phone }}</p>
                @endif
                @if($billingSettings->email)
                    <p>{{ $billingSettings->email }}</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Información del cliente -->
    <div class="mb-6">
        <h3 class="text-lg font-semibold text-black-500 mb-3">Facturado a:</h3>
        <div class="bg-gray-50 p-4 rounded-lg">
            <p class="font-semibold text-black-500">{{ $invoice->store->owner_name ?? $invoice->store->name }}</p>
            @if($invoice->store->email)
                <p class="text-sm text-black-400">{{ $invoice->store->email }}</p>
            @endif
            @if($invoice->store->phone)
                <p class="text-sm text-black-400">{{ $invoice->store->phone }}</p>
            @endif
            @if($invoice->store->address)
                <p class="text-sm text-black-400">{{ $invoice->store->address }}</p>
            @endif
        </div>
    </div>

    <!-- Detalles del servicio -->
    <div class="mb-6">
        <h3 class="text-lg font-semibold text-black-500 mb-3">Detalles del servicio:</h3>
        <div class="border border-gray-200 rounded-lg overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-black-500">Descripción</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-black-500">Plan</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-black-500">Período</th>
                        <th class="px-4 py-3 text-right text-sm font-semibold text-black-500">Monto</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="border-t border-gray-200">
                        <td class="px-4 py-3 text-sm text-black-400">
                            Suscripción plan {{ $invoice->plan->name ?? 'N/A' }}
                            <br>
                            <span class="text-xs text-black-300">Servicio de tienda online</span>
                        </td>
                        <td class="px-4 py-3 text-sm text-black-400">{{ $invoice->plan->name ?? 'N/A' }}</td>
                        <td class="px-4 py-3 text-sm text-black-400">
                            @if($invoice->billing_period === 'monthly')
                                Mensual
                            @elseif($invoice->billing_period === 'quarterly')
                                Trimestral
                            @elseif($invoice->billing_period === 'biannual')
                                Semestral
                            @else
                                {{ ucfirst($invoice->billing_period ?? 'Mensual') }}
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right text-sm font-semibold text-black-500">
                            ${{ number_format($invoice->amount, 0, ',', '.') }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Totales -->
    <div class="flex justify-end mb-6">
        <div class="w-64">
            <div class="bg-gray-50 p-4 rounded-lg">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-sm text-black-400">Subtotal:</span>
                    <span class="text-sm font-semibold text-black-500">${{ number_format($invoice->amount, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between items-center mb-2">
                    <span class="text-sm text-black-400">IVA (0%):</span>
                    <span class="text-sm font-semibold text-black-500">$0</span>
                </div>
                <div class="border-t border-gray-200 pt-2">
                    <div class="flex justify-between items-center">
                        <span class="text-base font-semibold text-black-500">Total:</span>
                        <span class="text-lg font-bold text-primary-400">${{ number_format($invoice->amount, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Estado de la factura -->
    <div class="mb-6">
        <div class="flex items-center">
            <span class="text-sm text-black-400 mr-2">Estado:</span>
            <span class="px-3 py-1 rounded-full text-xs font-semibold 
                @if($invoice->status === 'paid')
                    bg-success-100 text-success-400
                @elseif($invoice->status === 'pending')
                    bg-warning-100 text-warning-400
                @elseif($invoice->status === 'overdue')
                    bg-error-100 text-error-400
                @else
                    bg-gray-100 text-gray-600
                @endif
            ">
                @if($invoice->status === 'paid')
                    Pagada
                @elseif($invoice->status === 'pending')
                    Pendiente
                @elseif($invoice->status === 'overdue')
                    Vencida
                @else
                    {{ ucfirst($invoice->status ?? 'Desconocido') }}
                @endif
            </span>
        </div>
    </div>

    <!-- Footer -->
    <div class="text-center text-xs text-black-300 pt-4 border-t border-gray-200">
        @if($billingSettings->footer_text)
            <p class="mb-2">{{ $billingSettings->footer_text }}</p>
        @endif
        <p class="font-semibold">¡Gracias por confiar en Linkiu.bio!</p>
    </div>
</div>
