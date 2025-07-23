<?php

namespace App\Features\TenantAdmin\Listeners;

use App\Features\TenantAdmin\Events\StorePlanChanged;
use App\Features\TenantAdmin\Services\BankAccountService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class HandleBankAccountsOnPlanChange implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * The bank account service instance.
     *
     * @var \App\Features\TenantAdmin\Services\BankAccountService
     */
    protected $bankAccountService;

    /**
     * Create the event listener.
     *
     * @param \App\Features\TenantAdmin\Services\BankAccountService $bankAccountService
     * @return void
     */
    public function __construct(BankAccountService $bankAccountService)
    {
        $this->bankAccountService = $bankAccountService;
    }

    /**
     * Handle the event.
     *
     * @param  \App\Features\TenantAdmin\Events\StorePlanChanged  $event
     * @return void
     */
    public function handle(StorePlanChanged $event)
    {
        // Only handle plan downgrades
        if (!$event->isPlanDowngrade()) {
            return;
        }

        try {
            // Get the store from the event
            $store = $event->store;
            
            // Handle plan downgrade for bank accounts
            $deactivatedCount = $this->bankAccountService->handlePlanDowngrade($store);
            
            if ($deactivatedCount > 0) {
                Log::info("Plan downgrade: {$deactivatedCount} bank accounts deactivated for store {$store->name} (ID: {$store->id})");
                
                // You could also send a notification to the store admin here
                // Notification::send($store->admin, new BankAccountsDeactivatedNotification($deactivatedCount));
            }
        } catch (\Exception $e) {
            Log::error("Error handling bank accounts on plan change: " . $e->getMessage(), [
                'store_id' => $event->store->id,
                'previous_plan' => $event->previousPlan,
                'current_plan' => $event->store->plan->name ?? 'unknown'
            ]);
        }
    }
}