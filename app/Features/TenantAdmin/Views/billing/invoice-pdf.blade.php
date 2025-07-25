<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura {{ $invoice->invoice_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 13px;
            line-height: 1.5;
            color: #151515;
            background: #FBFDFF;
        }
        
        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 0;
        }
        
        .card-body {
            padding: 24px;
        }
        
        .flex {
            display: flex;
        }
        
        .flex-wrap {
            flex-wrap: wrap;
        }
        
        .justify-between {
            justify-content: space-between;
        }
        
        .items-end {
            align-items: flex-end;
        }
        
        .gap-4 {
            gap: 16px;
        }
        
        .gap-6 {
            gap: 24px;
        }
        
        .text-xl {
            font-size: 20px;
        }
        
        .text-lg {
            font-size: 18px;
        }
        
        .text-base {
            font-size: 14px;
        }
        
        .text-sm {
            font-size: 12px;
        }
        
        .font-semibold {
            font-weight: 600;
        }
        
        .text-black-500 {
            color: #151515;
        }
        
        .text-black-400 {
            color: #3A4550;
        }
        
        .text-white-200 {
            color: #D7E0E8;
        }
        
        .bg-white-100 {
            background-color: #F0F0F0;
        }
        
        .border-white-200 {
            border-color: #D7E0E8;
        }
        
        .mb-0 { margin-bottom: 0; }
        .mb-1 { margin-bottom: 4px; }
        .mb-2 { margin-bottom: 8px; }
        .mb-4 { margin-bottom: 16px; }
        .mt-8 { margin-top: 32px; }
        .mt-16 { margin-top: 64px; }
        .py-1 { padding-top: 4px; padding-bottom: 4px; }
        .py-3 { padding-top: 12px; padding-bottom: 12px; }
        .py-6 { padding-top: 24px; padding-bottom: 24px; }
        .px-4 { padding-left: 16px; padding-right: 16px; }
        .px-6 { padding-left: 24px; padding-right: 24px; }
        .ps-2 { padding-left: 8px; }
        .pl-6 { padding-left: 24px; }
        .pr-16 { padding-right: 64px; }
        .pt-2 { padding-top: 8px; }
        .pt-4 { padding-top: 16px; }
        .pb-4 { padding-bottom: 16px; }
        
        .text-center {
            text-align: center;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-left {
            text-align: left;
        }
        
        .border {
            border: 1px solid;
        }
        
        .border-b {
            border-bottom: 1px solid;
        }
        
        .border-t {
            border-top: 1px solid;
        }
        
        .border-collapse {
            border-collapse: collapse;
        }
        
        .w-full {
            width: 100%;
        }
        
        .h-12 {
            height: 48px;
        }
        
        .inline-block {
            display: inline-block;
        }
        
        table {
            border-collapse: collapse;
        }
        
        .logo {
            max-height: 48px;
            width: auto;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-paid {
            background-color: #E5FFF4;
            color: #005732;
        }
        
        .status-pending {
            background-color: #FFF9E5;
            color: #752F00;
        }
        
        .status-cancelled {
            background-color: #FFE7E9;
            color: #6E0009;
        }
        
        .status-overdue {
            background-color: #FFE7E9;
            color: #6E0009;
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <div class="card-body">
            <div class="card-body">
                <!-- Header de la factura -->
                <div class="card-body">
                    <div class="flex flex-wrap justify-between gap-4">
                        <div>
                            <h3 class="text-xl font-semibold text-black-500 mb-2">{{ $invoice->invoice_number }}</h3>
                            <p class="mb-1 text-base text-black-400">Fecha de Emisión: {{ $invoice->issue_date->format('d/m/Y') }}</p>
                            <p class="mb-0 text-base text-black-400">Fecha de Vencimiento: {{ $invoice->due_date->format('d/m/Y') }}</p>
                            <p class="mb-0 text-base">
                                <span class="status-badge status-{{ $invoice->status }}">
                                    {{ ucfirst($invoice->status) }}
                                </span>
                            </p>
                        </div>
                        <div class="text-right">
                            <h2 class="text-lg font-semibold text-black-500 mb-2">{{ config('app.name', 'LINKIU.BIO') }}</h2>
                            <p class="mb-1 text-sm text-black-400">Plataforma de Bio Links</p>
                            <p class="mb-0 text-sm text-black-400">contacto@linkiu.bio</p>
                        </div>
                    </div>
                </div>

                <!-- Información del cliente -->
                <div class="py-6 px-6">
                    <div class="flex flex-wrap justify-between gap-6">
                        <div>
                            <h4 class="text-lg font-semibold text-black-500 mb-4">Facturar A:</h4>
                            <table class="text-base text-black-400">
                                <tbody>
                                    <tr>
                                        <td class="py-1">Tienda</td>
                                        <td class="ps-2 py-1">: {{ $store->name }}</td>
                                    </tr>
                                    @if($store->owner_name)
                                    <tr>
                                        <td class="py-1">Propietario</td>
                                        <td class="ps-2 py-1">: {{ $store->owner_name }}</td>
                                    </tr>
                                    @endif
                                    @if($store->contact_email)
                                    <tr>
                                        <td class="py-1">Email</td>
                                        <td class="ps-2 py-1">: {{ $store->contact_email }}</td>
                                    </tr>
                                    @endif
                                    @if($store->phone)
                                    <tr>
                                        <td class="py-1">Teléfono</td>
                                        <td class="ps-2 py-1">: {{ $store->phone }}</td>
                                    </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                        <div>
                            <table class="text-base text-black-400">
                                <tbody>
                                    <tr>
                                        <td class="py-1">Factura</td>
                                        <td class="ps-2 py-1">: {{ $invoice->invoice_number }}</td>
                                    </tr>
                                    <tr>
                                        <td class="py-1">Fecha</td>
                                        <td class="ps-2 py-1">: {{ $invoice->issue_date->format('d M Y') }}</td>
                                    </tr>
                                    <tr>
                                        <td class="py-1">Período</td>
                                        <td class="ps-2 py-1">: {{ ucfirst($invoice->period) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="py-1">Estado</td>
                                        <td class="ps-2 py-1">: 
                                            <span class="status-badge status-{{ $invoice->status }}">
                                                {{ ucfirst($invoice->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Tabla de items -->
                    <div class="mt-8">
                        <div>
                            <table class="w-full border-collapse border border-white-200">
                                <thead>
                                    <tr class="bg-white-100">
                                        <th class="border border-white-200 px-4 py-3 text-left text-base text-black-500">Concepto</th>
                                        <th class="border border-white-200 px-4 py-3 text-left text-base text-black-500">Descripción</th>
                                        <th class="border border-white-200 px-4 py-3 text-left text-base text-black-500">Período</th>
                                        <th class="border border-white-200 px-4 py-3 text-right text-base text-black-500">Monto</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="border border-white-200 px-4 py-3 text-base text-black-400">
                                            Suscripción {{ $invoice->plan ? $invoice->plan->name : 'Plan Básico' }}
                                        </td>
                                        <td class="border border-white-200 px-4 py-3 text-base text-black-400">
                                            Plan {{ ucfirst($invoice->period) }} para tienda "{{ $store->name }}"
                                        </td>
                                        <td class="border border-white-200 px-4 py-3 text-base text-black-400">
                                            @php
                                                $startDate = $invoice->issue_date;
                                                $endDate = match($invoice->period) {
                                                    'monthly' => $startDate->copy()->addMonth()->subDay(),
                                                    'quarterly' => $startDate->copy()->addMonths(3)->subDay(),
                                                    'biannual' => $startDate->copy()->addMonths(6)->subDay(),
                                                    default => $startDate->copy()->addMonth()->subDay()
                                                };
                                            @endphp
                                            {{ $startDate->format('d/m/Y') }} - {{ $endDate->format('d/m/Y') }}
                                        </td>
                                        <td class="border border-white-200 px-4 py-3 text-base text-black-400 text-right">
                                            ${{ number_format($invoice->amount, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Totales -->
                        <div class="flex flex-wrap justify-between gap-6 mt-8">
                            <div>
                                <p class="text-base mb-2">
                                    <span class="text-black-500 font-semibold">Servicio prestado por:</span> {{ config('app.name', 'Linkiu.bio') }}
                                </p>
                                <p class="text-base mb-0 text-black-400">Gracias por confiar en nosotros</p>
                            </div>
                            <div>
                                <table class="text-base">
                                    <tbody>
                                        <tr>
                                            <td class="pr-16 py-1">Subtotal:</td>
                                            <td class="pl-6 py-1">
                                                <span class="text-black-500 font-semibold">${{ number_format($invoice->amount, 0, ',', '.') }}</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="pr-16 py-1">Descuento:</td>
                                            <td class="pl-6 py-1">
                                                <span class="text-black-500 font-semibold">$0</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="pr-16 py-1 border-b border-white-200 pb-4">IVA (19%):</td>
                                            <td class="pl-6 py-1 border-b border-white-200 pb-4">
                                                <span class="text-black-500 font-semibold">Incluido</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="pr-16 pt-4">
                                                <span class="text-black-500 font-semibold text-lg">Total:</span>
                                            </td>
                                            <td class="pl-6 pt-4">
                                                <span class="text-black-500 font-semibold text-lg">${{ number_format($invoice->amount, 0, ',', '.') }}</span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Información de pago -->
                    @if($invoice->status === 'paid' && $invoice->paid_date)
                    <div class="mt-8">
                        <h4 class="text-lg font-semibold text-black-500 mb-4">Información de Pago</h4>
                        <p class="text-base text-black-400 mb-2">
                            <span class="font-semibold">Estado:</span> 
                            <span class="status-badge status-paid">PAGADO</span>
                        </p>
                        <p class="text-base text-black-400 mb-2">
                            <span class="font-semibold">Fecha de pago:</span> {{ $invoice->paid_date->format('d/m/Y') }}
                        </p>
                        <p class="text-base text-black-400">
                            <span class="font-semibold">Método de pago:</span> Sistema
                        </p>
                    </div>
                    @endif

                    <!-- Mensaje de agradecimiento -->
                    <div class="mt-16">
                        <p class="text-center text-black-400 text-base font-semibold">¡Gracias por usar {{ config('app.name', 'Linkiu.bio') }}!</p>
                        <p class="text-center text-black-400 text-sm mt-2">Para soporte técnico, contáctanos en: contacto@linkiu.bio</p>
                    </div>

                    <!-- Footer legal -->
                    <div class="mt-16">
                        <div class="border-t border-white-200 pt-4">
                            <p class="text-sm text-black-400 text-center">
                                Esta es una factura generada automáticamente por {{ config('app.name', 'Linkiu.bio') }}<br>
                                Para consultas sobre esta factura, por favor contacta nuestro equipo de soporte.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 