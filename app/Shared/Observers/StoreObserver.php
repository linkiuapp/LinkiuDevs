<?php

namespace App\Shared\Observers;

use App\Shared\Models\Store;
use App\Shared\Models\Subscription;
use App\Shared\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class StoreObserver
{
    /**
     * Handle the Store "created" event.
     */
    public function created(Store $store): void
    {
        // Crear método de domicilio
        $domicilio = ShippingMethod::create([
            'type' => ShippingMethod::TYPE_DOMICILIO,
            'name' => 'Envío a Domicilio',
            'is_active' => false,
            'sort_order' => 1,
            'instructions' => 'Entrega en la dirección indicada',
            'store_id' => $store->id,
        ]);

        // Crear método de pickup
        $pickup = ShippingMethod::create([
            'type' => ShippingMethod::TYPE_PICKUP,
            'name' => 'Recoger en Tienda',
            'is_active' => false,
            'sort_order' => 2,
            'instructions' => 'Recoger en nuestra tienda principal',
            'store_id' => $store->id,
            'preparation_time' => '1h',
            'notification_enabled' => false,
        ]);

        // Crear configuración de envíos
        ShippingMethodConfig::create([
            'store_id' => $store->id,
            'default_method_id' => null,
            'min_active_methods' => 1,
        ]);

        // 🆕 CREAR SUSCRIPCIÓN Y PRIMERA FACTURA AUTOMÁTICAMENTE
        $this->createSubscriptionAndFirstInvoice($store);
    }

    /**
     * Handle the Store "deleting" event.
     */
    public function deleting(Store $store): void
    {
        // Las relaciones se eliminan en cascada por las foreign keys
    }

    /**
     * Crear suscripción y primera factura automáticamente
     */
    private function createSubscriptionAndFirstInvoice(Store $store): void
    {
        // Verificar que la tienda tenga un plan
        if (!$store->plan_id || !$store->plan) {
            Log::warning("Store {$store->id} created without plan - skipping automatic billing setup");
            return;
        }

        // Determinar período de facturación (usar el del request o default mensual)
        $billingCycle = request('billing_period', 'monthly');
        
        // Validar período
        if (!in_array($billingCycle, ['monthly', 'quarterly', 'biannual'])) {
            $billingCycle = 'monthly';
        }

        try {
            // Calcular fechas del período
            $periodStart = now();
            $periodDays = match($billingCycle) {
                'monthly' => 30,
                'quarterly' => 90,
                'biannual' => 180,
                default => 30
            };
            $periodEnd = $periodStart->copy()->addDays($periodDays);
            $nextBillingDate = $periodEnd->copy();

            // Obtener precio del plan para el período
            $amount = $store->plan->getPriceForPeriod($billingCycle);

            if (!$amount || $amount <= 0) {
                Log::warning("Store {$store->id} plan has no price for period {$billingCycle} - skipping billing setup");
                return;
            }

            // 1. CREAR SUSCRIPCIÓN
            $subscriptionStatus = $store->status === 'active' ? Subscription::STATUS_ACTIVE : Subscription::STATUS_SUSPENDED;
            
            Log::info("Creating subscription with status", [
                'store_status' => $store->status,
                'subscription_status' => $subscriptionStatus,
                'store_id' => $store->id
            ]);
            
            $subscription = Subscription::create([
                'store_id' => $store->id,
                'plan_id' => $store->plan_id,
                'status' => $subscriptionStatus,
                'billing_cycle' => $billingCycle,
                'current_period_start' => $periodStart->toDateString(),
                'current_period_end' => $periodEnd->toDateString(),
                'next_billing_date' => $nextBillingDate->toDateString(),
                'next_billing_amount' => $amount,
                'metadata' => [
                    'auto_created_on_store_creation' => true,
                    'created_from' => 'store_observer',
                    'original_billing_period' => $billingCycle,
                    'store_creation_date' => $store->created_at
                ]
            ]);

            // 2. GENERAR PRIMERA FACTURA
            $issueDate = now();
            $dueDate = $issueDate->copy()->addDays(15); // 15 días para pagar

            // Determinar estado de pago inicial (usar el del request o default pending)
            $initialPaymentStatus = request('initial_payment_status', 'pending');
            
            // Si está marcada como pagada, establecer fecha de pago
            $paidDate = null;
            if ($initialPaymentStatus === 'paid') {
                $paidDate = $issueDate->toDateString();
            }

            $invoice = Invoice::create([
                'store_id' => $store->id,
                'subscription_id' => $subscription->id,
                'plan_id' => $store->plan_id,
                'amount' => $amount,
                'period' => $billingCycle,
                'status' => $initialPaymentStatus,
                'issue_date' => $issueDate->toDateString(),
                'due_date' => $dueDate->toDateString(),
                'paid_date' => $paidDate,
                'notes' => $initialPaymentStatus === 'paid' 
                    ? 'Primera factura generada automáticamente al crear la tienda - Marcada como pagada' 
                    : 'Primera factura generada automáticamente al crear la tienda',
                'metadata' => [
                    'auto_generated' => true,
                    'generated_by' => 'store_observer',
                    'is_first_invoice' => true,
                    'store_creation_date' => $store->created_at,
                    'billing_cycle' => $billingCycle,
                    'initial_payment_status' => $initialPaymentStatus
                ]
            ]);

            // 3. LOG DEL PROCESO
            Log::info("✅ Auto-billing setup completed for store {$store->id}", [
                'store_name' => $store->name,
                'plan_name' => $store->plan->name,
                'billing_cycle' => $billingCycle,
                'subscription_id' => $subscription->id,
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'amount' => $amount,
                'due_date' => $dueDate->toDateString(),
                'initial_payment_status' => $initialPaymentStatus,
                'paid_date' => $paidDate
            ]);

        } catch (\Exception $e) {
            Log::error("❌ Failed to create automatic billing for store {$store->id}: " . $e->getMessage(), [
                'store_id' => $store->id,
                'store_name' => $store->name,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
} 