<?php

namespace App\Features\SuperLinkiu\Controllers;

use App\Http\Controllers\Controller;
use App\Shared\Models\Invoice;
use App\Shared\Models\Store;
use App\Shared\Models\Plan;
use App\Shared\Models\Subscription;
use Illuminate\Http\Request;
use Carbon\Carbon;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Invoice::with(['store', 'plan']);

        // Búsqueda global
        if ($search = $request->get('search')) {
            $query->where(function($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('store', function($storeQuery) use ($search) {
                      $storeQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Filtro por tienda
        if ($storeId = $request->get('store_id')) {
            $query->byStore($storeId);
        }

        // Filtro por plan
        if ($planId = $request->get('plan_id')) {
            $query->byPlan($planId);
        }

        // Filtro por estado
        if ($status = $request->get('status')) {
            switch ($status) {
                case 'pending':
                    $query->pending();
                    break;
                case 'paid':
                    $query->paid();
                    break;
                case 'overdue':
                    $query->overdue();
                    break;
                case 'cancelled':
                    $query->cancelled();
                    break;
            }
        }

        // Filtro por período
        if ($period = $request->get('period')) {
            $query->byPeriod($period);
        }

        // Filtro por rango de fechas
        if ($startDate = $request->get('start_date')) {
            $query->whereDate('issue_date', '>=', $startDate);
        }
        if ($endDate = $request->get('end_date')) {
            $query->whereDate('issue_date', '<=', $endDate);
        }

        // Ordenamiento
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Paginación
        $perPage = $request->get('per_page', 12);
        $invoices = $query->paginate($perPage)->withQueryString();

        // Obtener datos para filtros
        $stores = Store::select('id', 'name')->get();
        $plans = Plan::select('id', 'name')->get();

        // Estadísticas rápidas
        $stats = [
            'total' => Invoice::count(),
            'pending' => Invoice::pending()->count(),
            'paid' => Invoice::paid()->count(),
            'overdue' => Invoice::overdue()->count(),
            'total_amount' => Invoice::paid()->sum('amount'),
        ];

        return view('superlinkiu::invoices.index', compact(
            'invoices',
            'stores',
            'plans',
            'stats'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $stores = Store::with('plan')->get();
        $plans = Plan::active()->get();
        
        return view('superlinkiu::invoices.create', compact('stores', 'plans'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'store_id' => 'required|exists:stores,id',
            'plan_id' => 'required|exists:plans,id',
            'amount' => 'required|numeric|min:0',
            'period' => 'required|in:monthly,quarterly,biannual',
            'issue_date' => 'required|date',
            'due_date' => 'required|date|after:issue_date',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Buscar suscripción activa de la tienda
        $store = Store::find($validated['store_id']);
        $subscription = $store->subscription;
        
        // Añadir subscription_id si existe
        if ($subscription) {
            $validated['subscription_id'] = $subscription->id;
        }

        // Añadir metadata
        $validated['metadata'] = [
            'generated_by' => 'super_admin',
            'admin_id' => auth()->id(),
            'generated_from' => 'manual_creation',
        ];

        $invoice = Invoice::create($validated);

        // Si hay suscripción y esta factura es para el próximo período, actualizar fechas
        if ($subscription && $invoice->issue_date >= now()->subDays(7)) {
            $periodDays = match($validated['period']) {
                'monthly' => 30,
                'quarterly' => 90,
                'biannual' => 180,
                default => 30
            };
            
            $subscription->update([
                'billing_cycle' => $validated['period'],
                'next_billing_date' => $invoice->due_date->copy()->addDays($periodDays),
                'next_billing_amount' => $validated['amount'],
            ]);
        }

        return redirect()
            ->route('superlinkiu.invoices.index')
            ->with('success', 'Factura creada exitosamente: ' . $invoice->invoice_number);
    }

    /**
     * Display the specified resource.
     */
    public function show(Invoice $invoice)
    {
        $invoice->load(['store', 'plan']);
        
        return view('superlinkiu::invoices.show', compact('invoice'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Invoice $invoice)
    {
        $stores = Store::with('plan')->get();
        $plans = Plan::active()->get();
        
        return view('superlinkiu::invoices.edit', compact('invoice', 'stores', 'plans'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'store_id' => 'required|exists:stores,id',
            'plan_id' => 'required|exists:plans,id',
            'amount' => 'required|numeric|min:0',
            'period' => 'required|in:monthly,quarterly,biannual',
            'issue_date' => 'required|date',
            'due_date' => 'required|date|after:issue_date',
            'notes' => 'nullable|string|max:1000',
        ]);

        $invoice->update($validated);

        return redirect()
            ->route('superlinkiu.invoices.show', $invoice)
            ->with('success', 'Factura actualizada exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Invoice $invoice)
    {
        // Solo permitir eliminar facturas pendientes o canceladas
        if ($invoice->isPaid()) {
            return back()->with('error', 'No se puede eliminar una factura pagada.');
        }

        $invoiceNumber = $invoice->invoice_number;
        $invoice->delete();

        return redirect()
            ->route('superlinkiu.invoices.index')
            ->with('success', 'Factura ' . $invoiceNumber . ' eliminada exitosamente.');
    }

    /**
     * Marcar factura como pagada
     */
    public function markAsPaid(Request $request, Invoice $invoice)
    {
        try {
            // Log para debugging
            \Log::info('markAsPaid called', [
                'invoice_id' => $invoice->id,
                'invoice_status' => $invoice->status,
                'request_data' => $request->all()
            ]);

            // Verificar que la factura no esté ya pagada
            if ($invoice->isPaid()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Esta factura ya está marcada como pagada.',
                ], 400);
            }

            // Verificar que la factura no esté cancelada
            if ($invoice->isCancelled()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede marcar como pagada una factura cancelada.',
                ], 400);
            }

            $validated = $request->validate([
                'paid_date' => 'nullable|date',
            ]);

            $paidDate = $validated['paid_date'] ? Carbon::parse($validated['paid_date']) : now();
            
            // Marcar factura como pagada
            $result = $invoice->markAsPaid($paidDate);
            
            if (!$result) {
                \Log::error('Failed to mark invoice as paid', ['invoice_id' => $invoice->id]);
                return response()->json([
                    'success' => false,
                    'message' => 'Error al actualizar el estado de la factura.',
                ], 500);
            }

            \Log::info('Invoice marked as paid successfully', [
                'invoice_id' => $invoice->id,
                'new_status' => $invoice->status
            ]);

            // Si la factura está conectada a una suscripción, actualizarla
            if ($invoice->subscription) {
                $subscription = $invoice->subscription;
                
                // Si la suscripción está en período de gracia, reactivarla
                if ($subscription->status === Subscription::STATUS_GRACE_PERIOD) {
                    $subscription->update([
                        'status' => Subscription::STATUS_ACTIVE,
                        'grace_period_end' => null,
                        'metadata' => array_merge($subscription->metadata ?? [], [
                            'reactivated_by_payment' => true,
                            'reactivated_at' => now(),
                            'invoice_id' => $invoice->id,
                        ])
                    ]);
                    
                    \Log::info('Subscription reactivated by payment', [
                        'subscription_id' => $subscription->id,
                        'invoice_id' => $invoice->id
                    ]);
                }

                // Extender el período actual si la factura cubre el próximo período
                if ($subscription->status === Subscription::STATUS_ACTIVE && 
                    $invoice->issue_date >= now()->subDays(7)) {
                    
                    $periodDays = match($invoice->period) {
                        'monthly' => 30,
                        'quarterly' => 90,
                        'biannual' => 180,
                        default => 30
                    };

                    $newPeriodEnd = $subscription->current_period_end->copy()->addDays($periodDays);
                    $newBillingDate = $newPeriodEnd->copy()->addDay();

                    $subscription->update([
                        'current_period_end' => $newPeriodEnd,
                        'next_billing_date' => $newBillingDate,
                    ]);
                    
                    \Log::info('Subscription period extended', [
                        'subscription_id' => $subscription->id,
                        'new_period_end' => $newPeriodEnd->toDateString(),
                        'new_billing_date' => $newBillingDate->toDateString()
                    ]);
                }
            }

            // Recargar la factura para obtener los datos actualizados
            $invoice->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Factura marcada como pagada exitosamente.',
                'status' => $invoice->getStatusLabel(),
                'status_color' => $invoice->getStatusColor(),
                'paid_date' => $invoice->paid_date ? $invoice->paid_date->format('d/m/Y H:i') : null,
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation error in markAsPaid', [
                'invoice_id' => $invoice->id,
                'errors' => $e->errors()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Datos de entrada inválidos.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error al marcar factura como pagada', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor: ' . $e->getMessage(),
                'error_details' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Cancelar factura
     */
    public function cancel(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $invoice->cancel($validated['reason'] ?? null);

        return response()->json([
            'success' => true,
            'message' => 'Factura cancelada exitosamente.',
            'status' => $invoice->getStatusLabel(),
            'status_color' => $invoice->getStatusColor(),
        ]);
    }

    /**
     * Generar factura automática para una tienda
     */
    public function generateForStore(Request $request, Store $store)
    {
        $validated = $request->validate([
            'period' => 'required|in:monthly,quarterly,biannual',
            'issue_date' => 'nullable|date',
        ]);

        $issueDate = $validated['issue_date'] ? Carbon::parse($validated['issue_date']) : now();
        $dueDate = $issueDate->copy()->addDays(15); // 15 días para pagar

        // Calcular el monto según el período
        $amount = $store->plan->getPriceForPeriod($validated['period']);
        
        if (!$amount) {
            return back()->with('error', 'No se pudo determinar el precio para el período seleccionado.');
        }

        // Buscar suscripción activa de la tienda
        $subscription = $store->subscription;
        
        $invoice = Invoice::create([
            'store_id' => $store->id,
            'subscription_id' => $subscription ? $subscription->id : null,
            'plan_id' => $store->plan_id,
            'amount' => $amount,
            'period' => $validated['period'],
            'issue_date' => $issueDate,
            'due_date' => $dueDate,
            'notes' => 'Factura generada automáticamente desde SuperAdmin',
            'metadata' => [
                'generated_by' => 'super_admin',
                'admin_id' => auth()->id(),
                'generated_from' => 'manual',
            ]
        ]);

        // Si hay suscripción, actualizar su próxima fecha de facturación
        if ($subscription) {
            $periodDays = match($validated['period']) {
                'monthly' => 30,
                'quarterly' => 90,
                'biannual' => 180,
                default => 30
            };
            
            $subscription->update([
                'next_billing_date' => $issueDate->copy()->addDays($periodDays)->toDateString(),
                'next_billing_amount' => $amount,
            ]);
        }

        return redirect()
            ->route('superlinkiu.invoices.show', $invoice)
            ->with('success', 'Factura generada exitosamente: ' . $invoice->invoice_number);
    }

    /**
     * Actualizar facturas vencidas
     */
    public function updateOverdueInvoices()
    {
        $overdueCount = Invoice::where('status', 'pending')
            ->where('due_date', '<', now())
            ->update(['status' => 'overdue']);

        return response()->json([
            'success' => true,
            'message' => "{$overdueCount} facturas marcadas como vencidas.",
            'count' => $overdueCount,
        ]);
    }

    /**
     * Obtener estadísticas de facturación
     */
    public function getStats()
    {
        $stats = [
            'total_invoices' => Invoice::count(),
            'pending_invoices' => Invoice::pending()->count(),
            'paid_invoices' => Invoice::paid()->count(),
            'overdue_invoices' => Invoice::overdue()->count(),
            'cancelled_invoices' => Invoice::cancelled()->count(),
            'total_revenue' => Invoice::paid()->sum('amount'),
            'pending_revenue' => Invoice::pending()->sum('amount'),
            'overdue_revenue' => Invoice::overdue()->sum('amount'),
            'monthly_revenue' => Invoice::paid()
                ->whereMonth('paid_date', now()->month)
                ->whereYear('paid_date', now()->year)
                ->sum('amount'),
        ];

        return response()->json($stats);
    }
} 