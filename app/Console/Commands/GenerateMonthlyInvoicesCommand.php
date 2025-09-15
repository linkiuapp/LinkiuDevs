<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Shared\Models\Subscription;
use App\Shared\Models\Invoice;
use App\Services\BillingAutomationService;
use Carbon\Carbon;

class GenerateMonthlyInvoicesCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'billing:generate-monthly {--dry-run : Run without making changes} {--store= : Generate for specific store ID}';

    /**
     * The console command description.
     */
    protected $description = 'Generate monthly invoices for active subscriptions';

    protected $billingService;

    public function __construct(BillingAutomationService $billingService)
    {
        parent::__construct();
        $this->billingService = $billingService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        $storeId = $this->option('store');

        $this->info('🏦 Iniciando generación de facturas mensuales...');
        
        if ($isDryRun) {
            $this->warn('⚠️  MODO DRY-RUN: No se realizarán cambios reales');
        }

        if ($storeId) {
            $this->info("🎯 Generando solo para tienda ID: {$storeId}");
        }

        // Obtener suscripciones que necesitan factura
        $subscriptions = $this->getSubscriptionsNeedingInvoice($storeId);
        
        if ($subscriptions->isEmpty()) {
            $this->info('✅ No hay suscripciones que necesiten factura hoy.');
            return;
        }

        $this->info("📄 Encontradas {$subscriptions->count()} suscripciones que necesitan factura:");

        $generated = 0;
        $errors = 0;

        foreach ($subscriptions as $subscription) {
            try {
                $this->processSubscription($subscription, $isDryRun);
                $generated++;
            } catch (\Exception $e) {
                $errors++;
                $this->error("❌ Error procesando suscripción {$subscription->id}: " . $e->getMessage());
                \Log::error('Error en generación de factura', [
                    'subscription_id' => $subscription->id,
                    'store_id' => $subscription->store_id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Mostrar resumen
        $this->info("\n📊 RESUMEN:");
        $this->info("✅ Facturas generadas: {$generated}");
        
        if ($errors > 0) {
            $this->error("❌ Errores: {$errors}");
        }

        if ($isDryRun) {
            $this->warn("⚠️  Esto fue una simulación - no se guardaron cambios");
        } else {
            $this->info("🎉 ¡Proceso completado exitosamente!");
        }
    }

    /**
     * Get subscriptions that need invoice generation
     */
    private function getSubscriptionsNeedingInvoice($storeId = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = Subscription::with(['store', 'plan'])
            ->where('status', Subscription::STATUS_ACTIVE)
            ->where('next_billing_date', '<=', now()->toDateString());

        if ($storeId) {
            $query->where('store_id', $storeId);
        }

        return $query->get();
    }

    /**
     * Process individual subscription
     */
    private function processSubscription(Subscription $subscription, bool $isDryRun): void
    {
        $store = $subscription->store;
        $plan = $subscription->plan;

        if (!$store || !$plan) {
            throw new \Exception("Store o Plan no encontrados para suscripción {$subscription->id}");
        }

        $this->line("  📄 {$store->name} - {$plan->name} ({$subscription->billing_cycle})");

        // Verificar si ya existe factura para este período
        $existingInvoice = Invoice::where('store_id', $subscription->store_id)
            ->where('subscription_id', $subscription->id)
            ->where('period', $subscription->billing_cycle)
            ->where('issue_date', '>=', $subscription->current_period_start)
            ->where('issue_date', '<=', $subscription->current_period_end)
            ->first();

        if ($existingInvoice) {
            $this->warn("    ⏭️  Ya existe factura #{$existingInvoice->invoice_number} para este período");
            return;
        }

        if ($isDryRun) {
            $this->line("    🔮 Se generaría factura por: " . $plan->getFormattedPriceForPeriod($subscription->billing_cycle));
            return;
        }

        // Generar factura real
        $invoice = $this->billingService->generateInvoiceForSubscription($subscription);
        
        $this->info("    ✅ Factura #{$invoice->invoice_number} generada por {$invoice->getFormattedAmount()}");
        
        // Actualizar fechas de suscripción
        $this->updateSubscriptionDates($subscription);
    }

    /**
     * Update subscription billing dates after invoice generation
     */
    private function updateSubscriptionDates(Subscription $subscription): void
    {
        $periodDays = match($subscription->billing_cycle) {
            'monthly' => 30,
            'quarterly' => 90,
            'biannual' => 180,
            default => 30
        };

        $newPeriodStart = $subscription->current_period_end->copy()->addDay();
        $newPeriodEnd = $newPeriodStart->copy()->addDays($periodDays - 1);
        $nextBillingDate = $newPeriodEnd->copy()->addDay();

        $subscription->update([
            'current_period_start' => $newPeriodStart,
            'current_period_end' => $newPeriodEnd,
            'next_billing_date' => $nextBillingDate,
            'next_billing_amount' => $subscription->plan->getPriceForPeriod($subscription->billing_cycle)
        ]);

        $this->line("    📅 Próxima facturación: {$nextBillingDate->format('d/m/Y')}");
    }
}

