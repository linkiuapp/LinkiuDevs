<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Factura {{ $invoice->invoice_number }}</title>
    <style>
        /* ===== INSPIRADO EN ANVIL INVOICE TEMPLATE ===== */
        * {
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Helvetica Neue', 'Helvetica', Arial, sans-serif;
            color: #555;
            margin: 0;
            padding: 0;
            background: #ffffff;
        }
        
        .invoice-box {
            max-width: 800px;
            margin: auto;
            padding: 30px;
            line-height: 24px;
            font-size: 16px;
            color: #555;
        }
        
        /* HEADER */
        .invoice-header {
            margin-bottom: 40px;
        }
        
        .invoice-header table {
            width: 100%;
            border: none;
        }
        
        .invoice-header td {
            vertical-align: top;
            padding: 0;
            border: none;
        }
        
        .invoice-title {
            font-size: 45px;
            line-height: 45px;
            color: #333;
            font-weight: normal;
            margin: 0;
        }
        
        .invoice-number {
            font-size: 14px;
            color: #888;
            margin-top: 5px;
        }
        
        .company-details {
            text-align: right;
        }
        
        .company-logo {
            max-width: 150px;
            max-height: 50px;
            margin-bottom: 10px;
        }
        
        .company-info {
            font-size: 14px;
            color: #666;
            line-height: 20px;
        }
        
        .company-name {
            font-weight: bold;
            color: #333;
            font-size: 16px;
            margin-bottom: 5px;
        }
        
        /* BILLING SECTION */
        .billing-section {
            margin: 40px 0;
        }
        
        .billing-section table {
            width: 100%;
            border: none;
        }
        
        .billing-section td {
            vertical-align: top;
            padding: 0;
            border: none;
            width: 50%;
        }
        
        .bill-to,
        .ship-to {
            padding-right: 40px;
        }
        
        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #333;
            text-transform: uppercase;
            margin-bottom: 10px;
            border-bottom: 2px solid #eee;
            padding-bottom: 5px;
        }
        
        .address-info {
            font-size: 14px;
            line-height: 20px;
            color: #666;
        }
        
        .address-info strong {
            color: #333;
        }
        
        /* ITEMS TABLE */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 40px 0;
        }
        
        .items-table th {
            background: #eee;
            border-bottom: 1px solid #ddd;
            padding: 15px;
            text-align: left;
            font-weight: bold;
            font-size: 14px;
            color: #333;
        }
        
        .items-table td {
            padding: 15px;
            border-bottom: 1px solid #eee;
            font-size: 14px;
        }
        
        .items-table th:last-child,
        .items-table td:last-child {
            text-align: right;
        }
        
        .items-table th:nth-child(3),
        .items-table td:nth-child(3) {
            text-align: center;
        }
        
        .item-description {
            color: #333;
            font-weight: 500;
        }
        
        .item-details {
            color: #888;
            font-size: 12px;
            margin-top: 5px;
        }
        
        /* TOTALS */
        .totals-section {
            margin-top: 40px;
            float: right;
            width: 300px;
        }
        
        .totals-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .totals-table td {
            padding: 8px 0;
            border: none;
            font-size: 14px;
        }
        
        .totals-table .label {
            text-align: left;
            color: #666;
        }
        
        .totals-table .amount {
            text-align: right;
            color: #333;
            font-weight: 500;
        }
        
        .totals-table .total-row {
            border-top: 2px solid #eee;
            font-weight: bold;
            font-size: 16px;
            color: #333;
        }
        
        .totals-table .total-row td {
            padding-top: 15px;
        }
        
        /* FOOTER INFO */
        .footer-info {
            margin-top: 60px;
            clear: both;
        }
        
        .footer-section {
            margin-bottom: 30px;
        }
        
        .footer-title {
            font-size: 14px;
            font-weight: bold;
            color: #333;
            text-transform: uppercase;
            margin-bottom: 10px;
            border-bottom: 2px solid #eee;
            padding-bottom: 5px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-paid {
            background: #d4edda;
            color: #155724;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-overdue {
            background: #f8d7da;
            color: #721c24;
        }
        
        .notes-section {
            background: #f8f9fa;
            padding: 20px;
            border-left: 4px solid #007bff;
            margin-top: 40px;
            font-size: 14px;
            color: #666;
        }
        
        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }
        
        /* PDF OPTIMIZATIONS */
        @media print {
            .invoice-box {
                border: none;
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
    <div class="invoice-box">
        <!-- HEADER SECTION -->
        <div class="invoice-header">
            <table>
                <tr>
                    <td style="width: 60%;">
                        <h1 class="invoice-title">FACTURA</h1>
                        <div class="invoice-number">#{{ $invoice->invoice_number }}</div>
                    </td>
                    <td class="company-details">
                        @if($billingSettings->logo_url)
                            @php
                                $logoUrl = $billingSettings->logo_url;
                                
                                // Para PDF, usar ruta absoluta del sistema
                                if (strpos($logoUrl, '/storage/') === 0) {
                                    $logoPath = public_path(ltrim($logoUrl, '/'));
                                    if (file_exists($logoPath)) {
                                        $logoUrl = $logoPath;
                                    } else {
                                        $logoUrl = asset($logoUrl);
                                    }
                                } else {
                                    $logoUrl = asset($logoUrl);
                                }
                                
                                // Verificar en storage
                                if (!file_exists($logoUrl)) {
                                    $storagePath = storage_path('app/public/' . ltrim($billingSettings->logo_url, '/storage/'));
                                    if (file_exists($storagePath)) {
                                        $logoUrl = $storagePath;
                                    }
                                }
                            @endphp
                            <img src="{{ $logoUrl }}" alt="Logo" class="company-logo">
                        @endif
                        <div class="company-name">{{ $billingSettings->company_name ?? 'Linkiu.bio' }}</div>
                        <div class="company-info">
                            @if($billingSettings->company_address)
                                {{ $billingSettings->company_address }}<br>
                            @endif
                            @if($billingSettings->tax_id)
                                {{ $billingSettings->tax_id }}<br>
                            @endif
                            @if($billingSettings->phone)
                                {{ $billingSettings->phone }}<br>
                            @endif
                            @if($billingSettings->email)
                                {{ $billingSettings->email }}
                            @endif
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <!-- BILLING SECTION -->
        <div class="billing-section">
            <table>
                <tr>
                    <td class="bill-to">
                        <div class="section-title">Facturado a:</div>
                        <div class="address-info">
                            <strong>{{ $invoice->store->name }}</strong><br>
                            {{ $invoice->store->owner_name }}<br>
                            {{ $invoice->store->owner_email }}<br>
                            @if($invoice->store->phone)
                                {{ $invoice->store->phone }}
                            @endif
                        </div>
                    </td>
                    <td class="ship-to">
                        <div class="section-title">Detalles de la factura:</div>
                        <div class="address-info">
                            <strong>Fecha de emisión:</strong> {{ $invoice->issue_date->format('d/m/Y') }}<br>
                            <strong>Fecha de vencimiento:</strong> {{ $invoice->due_date->format('d/m/Y') }}<br>
                            <strong>Plan:</strong> {{ $invoice->plan->name ?? 'N/A' }}<br>
                            <strong>Período:</strong> {{ ucfirst($invoice->billing_period ?? 'Mensual') }}
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <!-- ITEMS TABLE -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 10%;">#</th>
                    <th style="width: 50%;">Descripción</th>
                    <th style="width: 10%;">Cant.</th>
                    <th style="width: 15%;">Precio Unit.</th>
                    <th style="width: 15%;">Total</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1</td>
                    <td>
                        <div class="item-description">{{ $invoice->plan->name ?? 'Plan de suscripción' }}</div>
                        <div class="item-details">
                            Suscripción {{ strtolower($invoice->billing_period ?? 'mensual') }}
                            @if($invoice->period_start && $invoice->period_end)
                                ({{ $invoice->period_start->format('d/m/Y') }} - {{ $invoice->period_end->format('d/m/Y') }})
                            @endif
                        </div>
                    </td>
                    <td style="text-align: center;">1</td>
                    <td style="text-align: right;">${{ number_format($invoice->amount, 0, ',', '.') }}</td>
                    <td style="text-align: right;">${{ number_format($invoice->amount, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        <!-- TOTALS SECTION -->
        <div class="totals-section">
            <table class="totals-table">
                <tr>
                    <td class="label">Subtotal:</td>
                    <td class="amount">${{ number_format($invoice->amount, 0, ',', '.') }} COP</td>
                </tr>
                <tr>
                    <td class="label">Descuentos:</td>
                    <td class="amount">$0 COP</td>
                </tr>
                <tr>
                    <td class="label">Impuestos:</td>
                    <td class="amount">$0 COP</td>
                </tr>
                <tr class="total-row">
                    <td class="label">TOTAL:</td>
                    <td class="amount">${{ number_format($invoice->amount, 0, ',', '.') }} COP</td>
                </tr>
            </table>
        </div>

        <div class="clearfix"></div>

        <!-- FOOTER INFO -->
        <div class="footer-info">
            <div class="footer-section">
                <div class="footer-title">Estado del pago</div>
                <span class="status-badge 
                    @if($invoice->status === 'paid') status-paid
                    @elseif($invoice->status === 'pending') status-pending
                    @else status-overdue @endif">
                    @if($invoice->status === 'paid')
                        Pagada
                    @elseif($invoice->status === 'pending')
                        Pendiente
                    @else
                        Vencida
                    @endif
                </span>
            </div>

            @if($billingSettings->footer_text)
                <div class="notes-section">
                    {{ $billingSettings->footer_text }}
                </div>
            @endif
        </div>
    </div>
</body>
</html>