<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Shared\Models\Subscription;
use App\Shared\Models\Invoice;
use App\Shared\Models\Store;
use Carbon\Carbon;

class SyncInvoicesWithSubscriptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'billing:sync-invoices {--dry-run : Show what would be done without actually doing it}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync invoices with subscriptions and generate automatic invoices for active subscriptions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        
        if ($isDryRun) {
            $this->info('🔍 DRY RUN MODE - No changes will be made');
        }

        $this->info('🔄 Syncing invoices with subscriptions...');
        
        // Tareas de sincronización
        $results = [
            'generated_invoices' => $this->generateMissingInvoices($isDryRun),
            'updated_subscriptions' => $this->updateSubscriptionBillingDates($isDryRun),
            'expired_subscriptions' => $this->handleExpiredSubscriptions($isDryRun),
        ];

        // Resumen
        $this->newLine();
        $this->info('📊 Sync Summary:');
        foreach ($results as $task => $count) {
            $this->line("  • {$task}: {$count} items");
        }

        $total = array_sum($results);
        if ($total > 0) {
            $this->info("✅ Total items processed: {$total}");
        } else {
            $this->info("✅ Everything is already synchronized");
        }

        return 0;
    }

    /**
     * Generate missing invoices for subscriptions
     */
    private function generateMissingInvoices(bool $isDryRun): int
    {
        // Encontrar suscripciones activas que necesitan factura
        $subscriptions = Subscription::where('status', Subscription::STATUS_ACTIVE)
            ->where('next_billing_date', '<=', now()->toDateString())
            ->with(['store', 'plan'])
            ->get();

        $generated = 0;

        foreach ($subscriptions as $subscription) {
            if (!$subscription->store || !$subscription->plan) continue;

            // Verificar si ya existe una factura para este período
            $existingInvoice = Invoice::where('store_id', $subscription->store_id)
                ->where('plan_id', $subscription->plan_id)
                ->where('period', $subscription->billing_cycle)
                ->where('issue_date', '>=', $subscription->current_period_start)
                ->where('issue_date', '<=', $subscription->current_period_end)
                ->first();

            if ($existingInvoice) {
                continue; // Ya existe factura para este período
            }

            if ($isDryRun) {
                $this->line("  📄 Would generate invoice for: {$subscription->store->name} - {$subscription->plan->name}");
            } else {
                $this->generateInvoiceForSubscription($subscription);
                $this->line("  📄 Generated invoice for: {$subscription->store->name}");
            }

            $generated++;
        }

        return $generated;
    }

    /**
     * Update subscription billing dates
     */
    private function updateSubscriptionBillingDates(bool $isDryRun): int
    {
        // Encontrar suscripciones que necesitan actualizar sus fechas de facturación
        $subscriptions = Subscription::where('status', Subscription::STATUS_ACTIVE)
            ->where('next_billing_date', '<', now()->toDateString())
            ->with(['store', 'plan'])
            ->get();

        $updated = 0;

        foreach ($subscriptions as $subscription) {
            if (!$subscription->store) continue;

            $periodDays = match($subscription->billing_cycle) {
                'monthly' => 30,
                'quarterly' => 90,
                'biannual' => 180,
                default => 30
            };

            $newPeriodStart = now();
            $newPeriodEnd = now()->addDays($periodDays);
            $newBillingDate = $newPeriodEnd->copy();

            if ($isDryRun) {
                $this->line("  📅 Would update billing dates for: {$subscription->store->name}");
            } else {
                $subscription->update([
                    'current_period_start' => $newPeriodStart->toDateString(),
                    'current_period_end' => $newPeriodEnd->toDateString(),
                    'next_billing_date' => $newBillingDate->toDateString(),
                ]);
                $this->line("  📅 Updated billing dates for: {$subscription->store->name}");
            }

            $updated++;
        }

        return $updated;
    }

    /**
     * Handle expired subscriptions
     */
    private function handleExpiredSubscriptions(bool $isDryRun): int
    {
        // Encontrar suscripciones que han expirado
        $subscriptions = Subscription::where('status', Subscription::STATUS_ACTIVE)
            ->where('current_period_end', '<', now()->toDateString())
            ->with(['store'])
            ->get();

        $expired = 0;

        foreach ($subscriptions as $subscription) {
            if (!$subscription->store) continue;

            // Verificar si tiene facturas pendientes recientes
            $hasPendingInvoices = Invoice::where('store_id', $subscription->store_id)
                ->where('status', 'pending')
                ->where('due_date', '>=', now()->subDays(30))
                ->exists();

            if ($hasPendingInvoices) {
                continue; // No expirar si tiene facturas pendientes recientes
            }

            if ($isDryRun) {
                $this->line("  ⚠️  Would mark as expired: {$subscription->store->name}");
            } else {
                // Cambiar a período de gracia por 7 días
                $subscription->update([
                    'status' => Subscription::STATUS_GRACE_PERIOD,
                    'grace_period_end' => now()->addDays(7)->toDateString(),
                ]);
                $this->line("  ⚠️  Moved to grace period: {$subscription->store->name}");
            }

            $expired++;
        }

        return $expired;
    }

    /**
     * Generate invoice for a subscription
     */
    private function generateInvoiceForSubscription(Subscription $subscription): Invoice
    {
        $issueDate = now();
        $dueDate = $issueDate->copy()->addDays(15);

        return Invoice::create([
            'store_id' => $subscription->store_id,
            'plan_id' => $subscription->plan_id,
            'amount' => $subscription->next_billing_amount,
            'period' => $subscription->billing_cycle,
            'status' => 'pending',
            'issue_date' => $issueDate,
            'due_date' => $dueDate,
            'notes' => 'Factura generada automáticamente por el sistema de suscripciones',
            'metadata' => [
                'generated_by' => 'subscription_system',
                'subscription_id' => $subscription->id,
                'generated_at' => now()->toDateTimeString(),
            ]
        ]);
    }
}
