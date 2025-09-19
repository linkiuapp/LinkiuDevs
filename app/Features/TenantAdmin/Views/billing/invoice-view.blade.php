@extends('shared::layouts.tenant-admin')

@section('title', 'Factura #' . $invoice->invoice_number)

@section('content')
<div class="p-6">
    <!-- Header -->
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-black-500">Factura #{{ $invoice->invoice_number }}</h1>
            <p class="text-black-300 mt-1">Gestiona y descarga tu factura</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('tenant.admin.billing.index', ['store' => $invoice->store->slug]) }}" 
               class="btn-secondary">
                <x-solar-arrow-left-outline class="w-4 h-4 mr-2" />
                Volver
            </a>
            <a href="{{ route('tenant.admin.invoices.download', ['store' => $invoice->store->slug, 'invoice' => $invoice]) }}" 
               class="btn-warning text-white">
                <x-solar-download-minimalistic-outline class="w-4 h-4 mr-2" />
                Descargar PDF
            </a>
            <button onclick="printInvoice()" class="btn-info text-white">
                <x-solar-printer-minimalistic-outline class="w-4 h-4 mr-2" />
                Imprimir
            </button>
        </div>
    </div>

    <!-- Factura -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="p-8" id="invoice-content">
            <!-- Header de la factura -->
            <div class="flex flex-wrap justify-between gap-6 border-b border-gray-200 pb-6 mb-6">
                <div>
                    <h2 class="text-xl font-bold text-black-500 mb-1">Factura #{{ $invoice->invoice_number }}</h2>
                    <p class="text-sm text-black-300 mb-1">Fecha de emisión: {{ $invoice->issue_date->format('d/m/Y') }}</p>
                    <p class="text-sm text-black-300">Fecha de vencimiento: {{ $invoice->due_date->format('d/m/Y') }}</p>
                </div>
                <div class="text-right">
                    @if($billingSettings->logo_url)
                        @php
                            $logoUrl = $billingSettings->logo_url;
                            // Si la URL comienza con /storage/, convertir a URL completa
                            if (strpos($logoUrl, '/storage/') === 0) {
                                $logoUrl = asset($logoUrl);
                            }
                            // O si es solo el path del storage, agregar asset
                            if (!filter_var($logoUrl, FILTER_VALIDATE_URL)) {
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
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                <div>
                    <h6 class="text-base font-semibold text-black-500 mb-3">Facturado a:</h6>
                    <div class="text-sm text-black-400 space-y-1">
                        <p><strong>Tienda:</strong> {{ $invoice->store->name }}</p>
                        <p><strong>Propietario:</strong> {{ $invoice->store->owner_name }}</p>
                        <p><strong>Email:</strong> {{ $invoice->store->owner_email }}</p>
                        @if($invoice->store->phone)
                            <p><strong>Teléfono:</strong> {{ $invoice->store->phone }}</p>
                        @endif
                    </div>
                </div>
                <div>
                    <div class="text-sm text-black-400 space-y-1">
                        <p><strong>Fecha de emisión:</strong> {{ $invoice->issue_date->format('d \\d\\e F, Y') }}</p>
                        <p><strong>ID de factura:</strong> #{{ $invoice->invoice_number }}</p>
                        <p><strong>Plan:</strong> {{ $invoice->plan->name ?? 'N/A' }}</p>
                        <p><strong>Período:</strong> {{ ucfirst($invoice->billing_period ?? 'Mensual') }}</p>
                    </div>
                </div>
            </div>

            <!-- Detalle de servicios -->
            <div class="mb-8">
                <div class="overflow-x-auto">
                    <table class="w-full border border-gray-200 rounded-lg">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ítem</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descripción</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Cantidad</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Precio Unit.</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white">
                            <tr>
                                <td class="px-6 py-4 text-sm font-medium text-black-500">01</td>
                                <td class="px-6 py-4 text-sm text-black-400">
                                    <div>
                                        <p class="font-medium">{{ $invoice->plan->name ?? 'Plan de suscripción' }}</p>
                                        <p class="text-xs text-black-300">
                                            Suscripción {{ strtolower($invoice->billing_period ?? 'mensual') }}
                                            @if($invoice->period_start && $invoice->period_end)
                                                <br>{{ $invoice->period_start->format('d/m/Y') }} - {{ $invoice->period_end->format('d/m/Y') }}
                                            @endif
                                        </p>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-black-400 text-center">1</td>
                                <td class="px-6 py-4 text-sm text-black-400 text-right">${{ number_format($invoice->amount, 0, ',', '.') }} COP</td>
                                <td class="px-6 py-4 text-sm font-medium text-black-500 text-right">${{ number_format($invoice->amount, 0, ',', '.') }} COP</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Totales -->
                <div class="flex justify-end mt-6">
                    <div class="w-80">
                        <div class="space-y-2">
                            <div class="flex justify-between py-2">
                                <span class="text-black-400">Subtotal:</span>
                                <span class="font-medium text-black-500">${{ number_format($invoice->amount, 0, ',', '.') }} COP</span>
                            </div>
                            <div class="flex justify-between py-2">
                                <span class="text-black-400">Descuento:</span>
                                <span class="font-medium text-black-500">$0 COP</span>
                            </div>
                            <div class="flex justify-between py-2 border-t border-gray-200 pt-4">
                                <span class="font-semibold text-black-500">Total:</span>
                                <span class="text-xl font-bold text-black-500">${{ number_format($invoice->amount, 0, ',', '.') }} COP</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Estado y método de pago -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                <div>
                    <h6 class="text-base font-semibold text-black-500 mb-3">Estado del pago:</h6>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                        @if($invoice->status === 'paid') bg-success-100 text-success-700
                        @elseif($invoice->status === 'pending') bg-warning-100 text-warning-700
                        @else bg-error-100 text-error-700 @endif">
                        @if($invoice->status === 'paid')
                            <x-solar-check-circle-outline class="w-4 h-4 mr-1" />
                            Pagada
                        @elseif($invoice->status === 'pending')
                            <x-solar-clock-circle-outline class="w-4 h-4 mr-1" />
                            Pendiente
                        @else
                            <x-solar-close-circle-outline class="w-4 h-4 mr-1" />
                            Vencida
                        @endif
                    </span>
                </div>
                @if($invoice->payment_method)
                <div>
                    <h6 class="text-base font-semibold text-black-500 mb-3">Método de pago:</h6>
                    <p class="text-sm text-black-400">{{ $invoice->payment_method }}</p>
                </div>
                @endif
            </div>

            <!-- Footer -->
            @if($billingSettings->footer_text)
            <div class="mt-12 pt-6 border-t border-gray-200">
                <p class="text-center text-black-300 text-sm">{{ $billingSettings->footer_text }}</p>
            </div>
            @endif

            <div class="mt-8 pt-6 border-t border-gray-200">
                <p class="text-center text-black-300 text-sm font-semibold">¡Gracias por confiar en Linkiu.bio!</p>
            </div>
        </div>
    </div>
</div>

<script>
function printInvoice() {
    var printContents = document.getElementById('invoice-content').innerHTML;
    var originalContents = document.body.innerHTML;
    
    document.body.innerHTML = printContents;
    window.print();
    document.body.innerHTML = originalContents;
    location.reload();
}
</script>
@endsection
