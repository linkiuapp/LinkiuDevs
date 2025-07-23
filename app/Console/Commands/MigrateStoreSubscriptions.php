<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Shared\Models\Store;
use App\Shared\Models\Subscription;
use App\Shared\Models\SubscriptionHistory;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MigrateStoreSubscriptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscription:migrate {--dry-run : Show what would be migrated without actually doing it}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate existing stores to the new subscription system';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        
        if ($isDryRun) {
            $this->info('🔍 DRY RUN MODE - No data will be changed');
        } else {
            $this->warn('⚠️  LIVE MODE - Data will be modified');
            if (!$this->confirm('Are you sure you want to proceed?')) {
                $this->info('Migration cancelled.');
                return 0;
            }
        }

        // Obtener todas las tiendas que tienen un plan pero no tienen suscripción
        $stores = Store::whereNotNull('plan_id')
            ->whereDoesntHave('subscription')
            ->with('plan')
            ->get();

        if ($stores->isEmpty()) {
            $this->info('✅ No stores found that need migration.');
            return 0;
        }

        $this->info("Found {$stores->count()} stores that need migration:");
        
        $migratedCount = 0;
        $errorCount = 0;

        DB::beginTransaction();

        try {
            foreach ($stores as $store) {
                $this->line("Processing store: {$store->name} (ID: {$store->id})");
                
                if (!$store->plan) {
                    $this->error("  ❌ Store has no plan - skipping");
                    $errorCount++;
                    continue;
                }

                // Determinar fechas del período
                $periodStart = $store->created_at ?? now();
                $periodEnd = $periodStart->copy()->addDays(30); // Default mensual
                
                // Determinar siguiente fecha de facturación
                $nextBillingDate = $periodEnd->copy();
                
                // Determinar monto según el plan
                $billingCycle = 'monthly'; // Default
                $nextBillingAmount = $store->plan->getPriceForPeriod($billingCycle);

                $subscriptionData = [
                    'store_id' => $store->id,
                    'plan_id' => $store->plan_id,
                    'status' => $this->determineStatus($store),
                    'billing_cycle' => $billingCycle,
                    'current_period_start' => $periodStart->toDateString(),
                    'current_period_end' => $periodEnd->toDateString(),
                    'next_billing_date' => $nextBillingDate->toDateString(),
                    'next_billing_amount' => $nextBillingAmount,
                    'metadata' => [
                        'migrated_from' => 'store_plan_direct',
                        'original_store_status' => $store->status,
                        'migration_date' => now()->toDateTimeString()
                    ]
                ];

                $this->table(['Field', 'Value'], [
                    ['Store ID', $store->id],
                    ['Store Name', $store->name],
                    ['Plan', $store->plan->name],
                    ['Status', $subscriptionData['status']],
                    ['Billing Cycle', $subscriptionData['billing_cycle']],
                    ['Period Start', $subscriptionData['current_period_start']],
                    ['Period End', $subscriptionData['current_period_end']],
                    ['Next Billing', $subscriptionData['next_billing_date']],
                    ['Amount', '$' . number_format($subscriptionData['next_billing_amount'], 0, ',', '.')]
                ]);

                if (!$isDryRun) {
                    // Crear la suscripción
                    $subscription = Subscription::create($subscriptionData);
                    
                    // Crear registro en historial (el modelo ya lo hace automáticamente, pero añadimos info extra)
                    SubscriptionHistory::create([
                        'store_id' => $store->id,
                        'subscription_id' => $subscription->id,
                        'new_plan_id' => $store->plan_id,
                        'new_status' => $subscription->status,
                        'new_billing_cycle' => $subscription->billing_cycle,
                        'change_type' => 'creation',
                        'change_reason' => 'Migrated from legacy store-plan system',
                        'changed_by_user_id' => null,
                        'changed_by_role' => 'system',
                        'new_amount' => $nextBillingAmount,
                        'changed_at' => now(),
                        'metadata' => [
                            'migration_command' => true,
                            'original_store_created_at' => $store->created_at,
                            'original_plan_id' => $store->plan_id
                        ]
                    ]);

                    $this->info("  ✅ Successfully migrated subscription for {$store->name}");
                } else {
                    $this->info("  ✅ Would migrate subscription for {$store->name}");
                }

                $migratedCount++;
                $this->newLine();
            }

            if (!$isDryRun) {
                DB::commit();
                $this->info("🎉 Migration completed successfully!");
            } else {
                DB::rollBack();
                $this->info("🔍 Dry run completed - no data was changed");
            }

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("❌ Migration failed: " . $e->getMessage());
            $this->error("Stack trace: " . $e->getTraceAsString());
            return 1;
        }

        $this->newLine();
        $this->info("📊 Summary:");
        $this->info("  • Stores processed: {$stores->count()}");
        $this->info("  • Successfully migrated: {$migratedCount}");
        $this->info("  • Errors: {$errorCount}");

        if (!$isDryRun && $migratedCount > 0) {
            $this->newLine();
            $this->info("🔗 Next steps:");
            $this->info("  • Review migrated subscriptions in the database");
            $this->info("  • Test the billing functionality");
            $this->info("  • Update any integrations that rely on Store->Plan directly");
        }

        return 0;
    }

    /**
     * Determine subscription status based on store status
     */
    private function determineStatus(Store $store): string
    {
        return match($store->status) {
            'active' => Subscription::STATUS_ACTIVE,
            'inactive' => Subscription::STATUS_SUSPENDED,
            'suspended' => Subscription::STATUS_SUSPENDED,
            default => Subscription::STATUS_ACTIVE
        };
    }
}
