<?php

namespace App\Features\TenantAdmin\Controllers;

use App\Http\Controllers\Controller;
use App\Shared\Models\Invoice;
use App\Shared\Models\BillingSetting;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController extends Controller
{
    /**
     * Mostrar vista de factura
     */
    public function show(Request $request, $storeSlug, Invoice $invoice)
    {
        $store = $request->route('store');
        
        // Verificar que la factura pertenece a la tienda actual
        if ($invoice->store_id !== $store->id) {
            abort(403);
        }
        
        $billingSettings = BillingSetting::getInstance();
        
        return view('tenant-admin::billing.invoice-view', compact('invoice', 'billingSettings', 'store'));
    }
    
    /**
     * Descargar factura como PDF
     */
    public function downloadPdf(Request $request, $storeSlug, Invoice $invoice)
    {
        $store = $request->route('store');
        
        // Verificar que la factura pertenece a la tienda actual
        if ($invoice->store_id !== $store->id) {
            abort(403);
        }
        
        $billingSettings = BillingSetting::getInstance();
        
        // Generar PDF
        $pdf = Pdf::loadView('tenant-admin::billing.invoice-pdf', compact('invoice', 'billingSettings'));
        
        // Configurar tamaño y orientación
        $pdf->setPaper('A4', 'portrait');
        
        // Nombre del archivo
        $filename = 'factura-' . $invoice->invoice_number . '.pdf';
        
        return $pdf->download($filename);
    }
    
    /**
     * Ver factura en modal (AJAX)
     */
    public function preview(Request $request, $storeSlug, Invoice $invoice)
    {
        $store = $request->route('store');
        
        // Verificar que la factura pertenece a la tienda actual
        if ($invoice->store_id !== $store->id) {
            abort(403);
        }
        
        $billingSettings = BillingSetting::getInstance();
        
        return view('tenant-admin::billing.invoice-preview', compact('invoice', 'billingSettings', 'store'));
    }
}
