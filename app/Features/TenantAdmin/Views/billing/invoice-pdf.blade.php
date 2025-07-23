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
            font-size: 14px;
            line-height: 1.4;
            color: #333;
            background: #fff;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #7432F8;
            padding-bottom: 20px;
        }
        
        .logo {
            color: #7432F8;
            font-size: 24px;
            font-weight: bold;
        }
        
        .invoice-info {
            text-align: right;
        }
        
        .invoice-title {
            font-size: 28px;
            color: #7432F8;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .invoice-number {
            font-size: 16px;
            color: #666;
        }
        
        .details-section {
            display: flex;
            justify-content: space-between;
            margin: 30px 0;
        }
        
        .company-details,
        .client-details {
            width: 48%;
        }
        
        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: #7432F8;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 1px solid #eee;
        }
        
        .detail-line {
            margin: 5px 0;
        }
        
        .detail-label {
            font-weight: bold;
            color: #555;
        }
        
        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            margin: 30px 0;
        }
        
        .invoice-table th {
            background: #7432F8;
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: bold;
        }
        
        .invoice-table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }
        
        .invoice-table tr:nth-child(even) {
            background: #f9f9f9;
        }
        
        .text-center {
            text-align: center;
        }
        
        .text-right {
            text-align: right;
        }
        
        .totals-section {
            margin-top: 30px;
            text-align: right;
        }
        
        .total-line {
            margin: 5px 0;
            padding: 5px 0;
        }
        
        .total-label {
            display: inline-block;
            width: 120px;
            font-weight: bold;
        }
        
        .total-amount {
            display: inline-block;
            width: 100px;
            text-align: right;
        }
        
        .grand-total {
            border-top: 2px solid #7432F8;
            padding-top: 10px;
            font-size: 18px;
            font-weight: bold;
            color: #7432F8;
        }
        
        .payment-info {
            margin-top: 40px;
            padding: 20px;
            background: #f5f5f5;
            border-radius: 5px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-paid {
            background: #00B341;
            color: white;
        }
        
        .status-pending {
            background: #FFAA00;
            color: white;
        }
        
        .status-overdue {
            background: #FF1B2D;
            color: white;
        }
        
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            text-align: center;
            color: #666;
            font-size: 12px;
        }
        
        .footer-logo {
            color: #7432F8;
            font-weight: bold;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="logo">
                LINKIU.BIO
            </div>
            <div class="invoice-info">
                <div class="invoice-title">FACTURA</div>
                <div class="invoice-number">#{{ $invoice->invoice_number }}</div>
                <div style="margin-top: 10px;">
                    <span class="status-badge status-{{ $invoice->status === 'paid' ? 'paid' : ($invoice->status === 'pending' ? 'pending' : 'overdue') }}">
                        @if($invoice->status === 'paid') PAGADA
                        @elseif($invoice->status === 'pending') PENDIENTE
                        @elseif($invoice->status === 'overdue') VENCIDA
                        @else CANCELADA @endif
                    </span>
                </div>
            </div>
        </div>

        <!-- Details Section -->
        <div class="details-section">
            <!-- Company Details -->
            <div class="company-details">
                <div class="section-title">DATOS DE LINKIU.BIO</div>
                <div class="detail-line">
                    <strong>Linkiu.bio SAS</strong>
                </div>
                <div class="detail-line">
                    NIT: 123.456.789-0
                </div>
                <div class="detail-line">
                    Dirección: Calle 123 #45-67
                </div>
                <div class="detail-line">
                    Bogotá, Colombia
                </div>
                <div class="detail-line">
                    Email: facturacion@linkiu.bio
                </div>
                <div class="detail-line">
                    Teléfono: +57 (1) 234-5678
                </div>
            </div>

            <!-- Client Details -->
            <div class="client-details">
                <div class="section-title">FACTURAR A</div>
                <div class="detail-line">
                    <strong>{{ $store->name }}</strong>
                </div>
                @if($store->document_type && $store->document_number)
                    <div class="detail-line">
                        {{ strtoupper($store->document_type) }}: {{ $store->document_number }}
                    </div>
                @endif
                @if($store->address)
                    <div class="detail-line">{{ $store->address }}</div>
                @endif
                @if($store->city || $store->department)
                    <div class="detail-line">
                        {{ $store->city }}@if($store->city && $store->department), @endif{{ $store->department }}
                    </div>
                @endif
                @if($store->email)
                    <div class="detail-line">{{ $store->email }}</div>
                @endif
                @if($store->phone)
                    <div class="detail-line">{{ $store->phone }}</div>
                @endif
            </div>
        </div>

        <!-- Invoice Details -->
        <div style="margin: 20px 0;">
            <div style="display: flex; justify-content: space-between;">
                <div>
                    <div class="detail-line">
                        <span class="detail-label">Fecha de Emisión:</span> {{ $invoice->issue_date->format('d/m/Y') }}
                    </div>
                    <div class="detail-line">
                        <span class="detail-label">Fecha de Vencimiento:</span> {{ $invoice->due_date->format('d/m/Y') }}
                    </div>
                    @if($invoice->paid_date)
                        <div class="detail-line">
                            <span class="detail-label">Fecha de Pago:</span> {{ $invoice->paid_date->format('d/m/Y') }}
                        </div>
                    @endif
                </div>
                <div>
                    <div class="detail-line">
                        <span class="detail-label">Período:</span> {{ ucfirst($invoice->period) }}
                    </div>
                    <div class="detail-line">
                        <span class="detail-label">Plan:</span> {{ $invoice->plan->name }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Invoice Table -->
        <table class="invoice-table">
            <thead>
                <tr>
                    <th>Descripción</th>
                    <th class="text-center">Período</th>
                    <th class="text-center">Cantidad</th>
                    <th class="text-right">Valor Unitario</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <strong>Suscripción Plan {{ $invoice->plan->name }}</strong>
                        <br>
                        <small style="color: #666;">
                            Acceso completo a las funcionalidades del plan {{ $invoice->plan->name }}
                            @if($invoice->plan->description)
                                <br>{{ $invoice->plan->description }}
                            @endif
                        </small>
                    </td>
                    <td class="text-center">
                        {{ ucfirst($invoice->period) }}
                        <br>
                        <small style="color: #666;">
                            {{ $invoice->issue_date->format('d/m/Y') }} - 
                            {{ $invoice->issue_date->addMonth()->format('d/m/Y') }}
                        </small>
                    </td>
                    <td class="text-center">1</td>
                    <td class="text-right">${{ number_format($invoice->amount, 0, ',', '.') }}</td>
                    <td class="text-right"><strong>${{ number_format($invoice->amount, 0, ',', '.') }}</strong></td>
                </tr>
            </tbody>
        </table>

        <!-- Totals -->
        <div class="totals-section">
            <div class="total-line">
                <span class="total-label">Subtotal:</span>
                <span class="total-amount">${{ number_format($invoice->amount, 0, ',', '.') }}</span>
            </div>
            <div class="total-line">
                <span class="total-label">IVA (0%):</span>
                <span class="total-amount">$0</span>
            </div>
            <div class="total-line grand-total">
                <span class="total-label">TOTAL:</span>
                <span class="total-amount">${{ number_format($invoice->amount, 0, ',', '.') }}</span>
            </div>
        </div>

        <!-- Payment Information -->
        @if($invoice->status === 'pending' || $invoice->status === 'overdue')
            <div class="payment-info">
                <div class="section-title">INFORMACIÓN DE PAGO</div>
                <div class="detail-line">
                    <span class="detail-label">Banco:</span> Banco Ejemplo
                </div>
                <div class="detail-line">
                    <span class="detail-label">Cuenta Corriente:</span> 123-456789-01
                </div>
                <div class="detail-line">
                    <span class="detail-label">A nombre de:</span> Linkiu.bio SAS
                </div>
                <div class="detail-line">
                    <span class="detail-label">NIT:</span> 123.456.789-0
                </div>
                <div style="margin-top: 15px; color: #666; font-size: 12px;">
                    Por favor envía el comprobante de pago a: pagos@linkiu.bio
                </div>
            </div>
        @endif

        <!-- Notes -->
        @if($invoice->notes)
            <div style="margin-top: 30px;">
                <div class="section-title">NOTAS</div>
                <div style="padding: 10px; background: #f9f9f9; border-radius: 5px;">
                    {{ $invoice->notes }}
                </div>
            </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <div class="footer-logo">LINKIU.BIO</div>
            <div>Plataforma de comercio electrónico para emprendedores</div>
            <div>www.linkiu.bio | soporte@linkiu.bio</div>
            <div style="margin-top: 10px; font-size: 10px;">
                Este documento fue generado electrónicamente el {{ now()->format('d/m/Y H:i:s') }}
            </div>
        </div>
    </div>
</body>
</html> 