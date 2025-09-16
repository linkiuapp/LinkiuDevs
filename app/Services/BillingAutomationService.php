<?php

namespace App\Services;

use App\Shared\Models\Subscription;
use App\Shared\Models\Invoice;
use App\Shared\Models\Store;
use Carbon\Carbon;

class BillingAutomationService
{
    /**
     * Generate invoice for a subscription
     */
    public function generateInvoiceForSubscription(Subscription $subscription): Invoice
    {
        $store = $subscription->store;
        $plan = $subscription->plan;

        if (!$store || !$plan) {
            throw new \Exception("Store o Plan no encontrados para suscripción {$subscription->id}");
        }

        // Calcular fechas
        $issueDate = now();
        $dueDate = $issueDate->copy()->addDays(15); // 15 días para pagar

        // Obtener precio según el período de facturación
        $amount = $plan->getPriceForPeriod($subscription->billing_cycle);
        
        if (!$amount || $amount <= 0) {
            throw new \Exception("No se pudo determinar el precio para el período {$subscription->billing_cycle} del plan {$plan->name}");
        }

        // Crear la factura
        $invoice = Invoice::create([
            'store_id' => $store->id,
            'subscription_id' => $subscription->id,
            'plan_id' => $plan->id,
            'amount' => $amount,
            'period' => $subscription->billing_cycle,
            'status' => 'pending',
            'issue_date' => $issueDate,
            'due_date' => $dueDate,
            'notes' => $this->generateInvoiceNotes($subscription),
            'metadata' => [
                'generated_by' => 'billing_automation',
                'subscription_cycle' => $subscription->billing_cycle,
                'period_start' => $subscription->current_period_start->toDateString(),
                'period_end' => $subscription->current_period_end->toDateString(),
                'auto_generated' => true,
                'generated_at' => now()->toISOString(),
            ]
        ]);

        // Log para auditoría
        \Log::info('Factura generada automáticamente', [
            'invoice_id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'store_id' => $store->id,
            'subscription_id' => $subscription->id,
            'amount' => $amount,
            'period' => $subscription->billing_cycle
        ]);

        return $invoice;
    }

    /**
     * Process automatic payment confirmation (when SuperAdmin marks as paid)
     */
    public function processPaymentConfirmation(Invoice $invoice): array
    {
        $results = [
            'subscription_updated' => false,
            'store_reactivated' => false,
            'next_invoice_scheduled' => false,
            'notifications_sent' => false
        ];

        try {
            // 1. Actualizar suscripción asociada
            if ($invoice->subscription) {
                $this->updateSubscriptionAfterPayment($invoice->subscription, $invoice);
                $results['subscription_updated'] = true;
            }

            // 2. Reactivar tienda si estaba suspendida por falta de pago
            if ($this->shouldReactivateStore($invoice->store)) {
                $this->reactivateStore($invoice->store);
                $results['store_reactivated'] = true;
            }

            // 3. Programar próxima facturación si es necesario
            if ($this->shouldScheduleNextInvoice($invoice)) {
                $this->scheduleNextInvoice($invoice);
                $results['next_invoice_scheduled'] = true;
            }

            return $results;

        } catch (\Exception $e) {
            \Log::error('Error procesando confirmación de pago automática', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Check if store should be reactivated after payment
     */
    private function shouldReactivateStore(Store $store): bool
    {
        return $store->status === 'suspended' && 
               isset($store->suspension_reason) && 
               $store->suspension_reason === 'billing_overdue';
    }

    /**
     * Reactivate a suspended store after payment
     */
    private function reactivateStore(Store $store): void
    {
        $store->update([
            'status' => 'active',
            'suspension_reason' => null,
            'suspended_at' => null,
            'suspended_invoice_id' => null,
            'reactivated_at' => now()
        ]);

        // Reactivar suscripción también
        if ($store->subscription && $store->subscription->status === Subscription::STATUS_SUSPENDED) {
            $store->subscription->update([
                'status' => Subscription::STATUS_ACTIVE,
                'grace_period_end' => null,
                'metadata' => array_merge($store->subscription->metadata ?? [], [
                    'reactivated_by_payment' => true,
                    'reactivated_at' => now()->toISOString(),
                    'auto_reactivated' => true
                ])
            ]);
        }

        \Log::info('Tienda reactivada automáticamente tras pago', [
            'store_id' => $store->id,
            'store_name' => $store->name,
            'reactivated_at' => now()
        ]);
    }

    /**
     * Update subscription after successful payment
     */
    private function updateSubscriptionAfterPayment(Subscription $subscription, Invoice $invoice): void
    {
        // Solo actualizar si el pago cubre el período actual o futuro
        $now = now();
        $invoicePeriodStart = Carbon::parse($invoice->metadata['period_start'] ?? $subscription->current_period_start);
        
        if ($invoicePeriodStart->greaterThanOrEqualTo($now->subDays(7))) {
            // Extender período actual
            $periodDays = $this->getPeriodDays($subscription->billing_cycle);
            $newPeriodEnd = $subscription->current_period_end->copy()->addDays($periodDays);
            $nextBillingDate = $newPeriodEnd->copy()->addDay();

            $subscription->update([
                'current_period_end' => $newPeriodEnd,
                'next_billing_date' => $nextBillingDate,
                'next_billing_amount' => $subscription->plan->getPriceForPeriod($subscription->billing_cycle),
                'metadata' => array_merge($subscription->metadata ?? [], [
                    'last_payment_processed' => now()->toISOString(),
                    'invoice_paid' => $invoice->invoice_number,
                    'period_extended_to' => $newPeriodEnd->toDateString()
                ])
            ]);

            \Log::info('Período de suscripción extendido tras pago', [
                'subscription_id' => $subscription->id,
                'new_period_end' => $newPeriodEnd->toDateString(),
                'next_billing_date' => $nextBillingDate->toDateString(),
                'invoice_number' => $invoice->invoice_number
            ]);
        }
    }

    /**
     * Check if next invoice should be scheduled
     */
    private function shouldScheduleNextInvoice(Invoice $invoice): bool
    {
        // Solo programar si no hay facturas pendientes futuras
        return !Invoice::where('store_id', $invoice->store_id)
            ->where('status', 'pending')
            ->where('due_date', '>', now())
            ->exists();
    }

    /**
     * Schedule next invoice generation
     */
    private function scheduleNextInvoice(Invoice $invoice): void
    {
        // Esta funcionalidad se maneja via el comando automático diario
        // Aquí solo registramos que se debe programar
        \Log::info('Próxima factura será generada automáticamente', [
            'store_id' => $invoice->store_id,
            'current_invoice' => $invoice->invoice_number,
            'next_billing_check' => now()->addDays(30)->toDateString()
        ]);
    }

    /**
     * Get period days for billing cycle
     */
    private function getPeriodDays(string $billingCycle): int
    {
        return match($billingCycle) {
            'monthly' => 30,
            'quarterly' => 90,
            'biannual' => 180,
            default => 30
        };
    }

    /**
     * Generate invoice notes based on subscription
     */
    private function generateInvoiceNotes(Subscription $subscription): string
    {
        $store = $subscription->store;
        $plan = $subscription->plan;
        $period = match($subscription->billing_cycle) {
            'monthly' => 'mensual',
            'quarterly' => 'trimestral', 
            'biannual' => 'semestral',
            default => $subscription->billing_cycle
        };

        return "Facturación {$period} - Plan {$plan->name} para tienda {$store->name}. " .
               "Período: {$subscription->current_period_start->format('d/m/Y')} - {$subscription->current_period_end->format('d/m/Y')}. " .
               "Generada automáticamente por el sistema.";
    }

    /**
     * Get billing statistics for dashboard
     */
    public function getBillingStatistics(): array
    {
        $now = now();
        $currentMonth = $now->format('Y-m');
        
        return [
            // Facturas del mes actual
            'current_month' => [
                'total' => Invoice::whereYear('issue_date', $now->year)
                    ->whereMonth('issue_date', $now->month)
                    ->count(),
                'pending' => Invoice::whereYear('issue_date', $now->year)
                    ->whereMonth('issue_date', $now->month)
                    ->where('status', 'pending')
                    ->count(),
                'paid' => Invoice::whereYear('issue_date', $now->year)
                    ->whereMonth('issue_date', $now->month)
                    ->where('status', 'paid')
                    ->count(),
                'overdue' => Invoice::whereYear('issue_date', $now->year)
                    ->whereMonth('issue_date', $now->month)
                    ->where('status', 'overdue')
                    ->count(),
                'revenue' => Invoice::whereYear('issue_date', $now->year)
                    ->whereMonth('issue_date', $now->month)
                    ->where('status', 'paid')
                    ->sum('amount')
            ],
            
            // Próximos vencimientos (próximos 7 días)
            'upcoming_due' => Invoice::where('status', 'pending')
                ->whereBetween('due_date', [$now->toDateString(), $now->addDays(7)->toDateString()])
                ->count(),
            
            // Facturas vencidas por período
            'overdue_breakdown' => [
                '1_7_days' => Invoice::where('status', 'overdue')
                    ->whereBetween('due_date', [$now->subDays(7)->toDateString(), $now->subDays(1)->toDateString()])
                    ->count(),
                '8_15_days' => Invoice::where('status', 'overdue')
                    ->whereBetween('due_date', [$now->subDays(15)->toDateString(), $now->subDays(8)->toDateString()])
                    ->count(),
                '16_30_days' => Invoice::where('status', 'overdue')
                    ->whereBetween('due_date', [$now->subDays(30)->toDateString(), $now->subDays(16)->toDateString()])
                    ->count(),
                'over_30_days' => Invoice::where('status', 'overdue')
                    ->where('due_date', '<', $now->subDays(30)->toDateString())
                    ->count()
            ],

            // Tiendas suspendidas por billing
            'suspended_stores' => Store::where('status', 'suspended')
                ->where('suspension_reason', 'billing_overdue')
                ->count(),

            // Próximas facturas a generar (próximos 3 días)
            'upcoming_generation' => Subscription::where('status', Subscription::STATUS_ACTIVE)
                ->whereBetween('next_billing_date', [$now->toDateString(), $now->addDays(3)->toDateString()])
                ->count()
        ];
    }
}








