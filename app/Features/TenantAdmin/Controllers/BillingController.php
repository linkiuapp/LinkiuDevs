<?php

namespace App\Features\TenantAdmin\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use App\Shared\Models\Store;
use App\Shared\Models\Subscription;
use App\Shared\Models\SubscriptionHistory;
use App\Shared\Models\Plan;
use App\Shared\Models\Invoice;
use App\Features\TenantAdmin\Models\Product;
use App\Features\TenantAdmin\Models\Category;
use App\Features\TenantAdmin\Models\ProductVariable;
use App\Features\TenantAdmin\Models\Slider;
use App\Shared\Models\Location;
use App\Features\TenantAdmin\Models\PaymentMethod;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class BillingController extends Controller
{
    /**
     * Display the billing dashboard
     */
    public function index(Request $request): View
    {
        $store = $request->route('store');
        
        // Verificar que el usuario sea admin de esta tienda
        if (!auth()->check() || 
            auth()->user()->role !== 'store_admin' || 
            auth()->user()->store_id !== $store->id) {
            abort(403);
        }

        // Obtener o crear suscripción
        $subscription = $store->subscription ?? $this->createDefaultSubscription($store);

        // Obtener planes disponibles
        $availablePlans = Plan::active()->orderBy('price')->get();

        // Calcular uso actual vs límites
        $usage = $this->calculateUsage($store);
        $limits = $this->getPlanLimits($subscription->plan);
        $percentages = $this->calculatePercentages($usage, $limits);

        // Obtener facturas recientes
        $recentInvoices = Invoice::where('store_id', $store->id)
            ->with('plan')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Obtener próxima facturación
        $nextInvoice = $this->getNextInvoiceInfo($subscription);

        // Obtener historial de cambios recientes
        $recentChanges = SubscriptionHistory::where('store_id', $store->id)
            ->with(['oldPlan', 'newPlan', 'changedByUser'])
            ->orderBy('changed_at', 'desc')
            ->limit(10)
            ->get();

        return view('tenant-admin::billing.index', compact(
            'store',
            'subscription',
            'availablePlans',
            'usage',
            'limits',
            'percentages',
            'recentInvoices',
            'nextInvoice',
            'recentChanges'
        ));
    }

    /**
     * Change subscription plan
     */
    public function changePlan(Request $request): JsonResponse
    {
        $store = $request->route('store');
        
        $validated = $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'reason' => 'nullable|string|max:500',
            'password' => 'required|string'
        ]);

        // Verificar contraseña
        if (!Hash::check($validated['password'], auth()->user()->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Contraseña incorrecta'
            ], 422);
        }

        $subscription = $store->subscription;
        if (!$subscription || !$subscription->canChangePlan()) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede cambiar el plan en este momento'
            ], 422);
        }

        $newPlan = Plan::find($validated['plan_id']);
        
        if ($newPlan->id === $subscription->plan_id) {
            return response()->json([
                'success' => false,
                'message' => 'Ya tienes este plan activo'
            ], 422);
        }

        // Realizar cambio de plan
        $success = $subscription->changePlan($newPlan, $validated['reason']);

        if ($success) {
            return response()->json([
                'success' => true,
                'message' => 'Plan cambiado exitosamente',
                'new_plan' => $newPlan->name,
                'redirect' => route('tenant.admin.billing.index', $store->slug)
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Error al cambiar el plan'
        ], 500);
    }

    /**
     * Change billing cycle
     */
    public function changeBillingCycle(Request $request): JsonResponse
    {
        $store = $request->route('store');
        
        $validated = $request->validate([
            'billing_cycle' => 'required|in:monthly,quarterly,biannual',
            'password' => 'required|string'
        ]);

        // Verificar contraseña
        if (!Hash::check($validated['password'], auth()->user()->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Contraseña incorrecta'
            ], 422);
        }

        $subscription = $store->subscription;
        if (!$subscription || !$subscription->canChangePlan()) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede cambiar el ciclo de facturación en este momento'
            ], 422);
        }

        if ($subscription->billing_cycle === $validated['billing_cycle']) {
            return response()->json([
                'success' => false,
                'message' => 'Ya tienes este ciclo de facturación activo'
            ], 422);
        }

        $success = $subscription->changeBillingCycle($validated['billing_cycle']);

        if ($success) {
            return response()->json([
                'success' => true,
                'message' => 'Ciclo de facturación cambiado exitosamente',
                'new_cycle' => $subscription->billing_cycle_label,
                'redirect' => route('tenant.admin.billing.index', $store->slug)
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Error al cambiar el ciclo de facturación'
        ], 500);
    }

    /**
     * Cancel subscription
     */
    public function cancelSubscription(Request $request): JsonResponse
    {
        $store = $request->route('store');
        
        $validated = $request->validate([
            'reason' => 'required|string|max:500',
            'password' => 'required|string'
        ]);

        // Verificar contraseña
        if (!Hash::check($validated['password'], auth()->user()->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Contraseña incorrecta'
            ], 422);
        }

        $subscription = $store->subscription;
        if (!$subscription || !$subscription->canCancel()) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede cancelar la suscripción en este momento'
            ], 422);
        }

        // Cancelar con período de gracia hasta el final del período actual
        $success = $subscription->cancel($validated['reason'], $subscription->current_period_end);

        if ($success) {
            return response()->json([
                'success' => true,
                'message' => 'Suscripción cancelada exitosamente. Mantendrás acceso hasta ' . 
                           $subscription->current_period_end->format('d/m/Y'),
                'redirect' => route('tenant.admin.billing.index', $store->slug)
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Error al cancelar la suscripción'
        ], 500);
    }

    /**
     * Reactivate subscription
     */
    public function reactivateSubscription(Request $request): JsonResponse
    {
        $store = $request->route('store');
        
        $validated = $request->validate([
            'password' => 'required|string'
        ]);

        // Verificar contraseña
        if (!Hash::check($validated['password'], auth()->user()->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Contraseña incorrecta'
            ], 422);
        }

        $subscription = $store->subscription;
        if (!$subscription || !$subscription->canReactivate()) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede reactivar la suscripción en este momento'
            ], 422);
        }

        $success = $subscription->reactivate();

        if ($success) {
            return response()->json([
                'success' => true,
                'message' => 'Suscripción reactivada exitosamente',
                'redirect' => route('tenant.admin.billing.index', $store->slug)
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Error al reactivar la suscripción'
        ], 500);
    }

    /**
     * Download invoice PDF
     */
    public function downloadInvoice(Request $request, $storeSlug, Invoice $invoice)
    {
        $store = $request->route('store');
        
        // Verificar que la factura pertenece a la tienda
        if ($invoice->store_id !== $store->id) {
            abort(404);
        }

        $pdf = Pdf::loadView('tenant-admin::billing.invoice-pdf', compact('invoice', 'store'));
        
        return $pdf->download("Factura_{$invoice->invoice_number}.pdf");
    }

    /**
     * Get usage data for the store
     */
    private function calculateUsage(Store $store): array
    {
        return [
            'products' => Product::where('store_id', $store->id)->count(),
            'categories' => Category::where('store_id', $store->id)->count(),
            'variables' => ProductVariable::where('store_id', $store->id)->count(),
            'sliders' => Slider::where('store_id', $store->id)->count(),
            'locations' => Location::where('store_id', $store->id)->count(),
            'bank_accounts' => PaymentMethod::where('store_id', $store->id)
                ->where('type', 'bank_transfer')
                ->whereHas('bankAccounts')
                ->count(),
            'coupons' => 0, // TODO: Implementar cuando tengamos cupones
            'active_promotions' => 0 // TODO: Implementar cuando tengamos promociones
        ];
    }

    /**
     * Get plan limits
     */
    private function getPlanLimits(Plan $plan): array
    {
        return [
            'products' => $plan->max_products,
            'categories' => $plan->max_categories,
            'variables' => $plan->max_variables ?? intval(($plan->max_products ?? 50) / 3),
            'sliders' => $plan->max_slider,
            'locations' => $plan->max_sedes,
            'bank_accounts' => $plan->max_bank_accounts ?? min($plan->max_sedes ?? 1, 3),
            'coupons' => $plan->max_active_coupons,
            'active_promotions' => $plan->max_active_promotions
        ];
    }

    /**
     * Calculate usage percentages
     */
    private function calculatePercentages(array $usage, array $limits): array
    {
        $percentages = [];
        
        foreach ($usage as $key => $value) {
            $limit = $limits[$key] ?? 1;
            $percentages[$key] = $limit > 0 ? min(round(($value / $limit) * 100), 100) : 0;
        }
        
        return $percentages;
    }

    /**
     * Get next invoice information
     */
    private function getNextInvoiceInfo(Subscription $subscription): array
    {
        return [
            'date' => $subscription->next_billing_date,
            'amount' => $subscription->next_billing_amount,
            'plan' => $subscription->plan->name,
            'cycle' => $subscription->billing_cycle_label,
            'days_until' => $subscription->days_until_next_billing
        ];
    }

    /**
     * Create default subscription for stores without one
     */
    private function createDefaultSubscription(Store $store): Subscription
    {
        return Subscription::create([
            'store_id' => $store->id,
            'plan_id' => $store->plan_id,
            'status' => $store->status === 'active' ? Subscription::STATUS_ACTIVE : Subscription::STATUS_SUSPENDED,
            'billing_cycle' => Subscription::BILLING_CYCLE_MONTHLY,
            'current_period_start' => $store->created_at ?? now(),
            'current_period_end' => ($store->created_at ?? now())->addDays(30),
            'next_billing_date' => ($store->created_at ?? now())->addDays(30),
            'next_billing_amount' => $store->plan->getPriceForPeriod('monthly'),
            'metadata' => [
                'created_from_legacy_store' => true,
                'auto_created_at' => now()
            ]
        ]);
    }
} 