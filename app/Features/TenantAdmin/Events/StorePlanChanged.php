<?php

namespace App\Features\TenantAdmin\Events;

use App\Shared\Models\Store;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StorePlanChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The store instance.
     *
     * @var \App\Shared\Models\Store
     */
    public $store;

    /**
     * The previous plan.
     *
     * @var string
     */
    public $previousPlan;

    /**
     * Create a new event instance.
     *
     * @param \App\Shared\Models\Store $store
     * @param string $previousPlan
     * @return void
     */
    public function __construct(Store $store, string $previousPlan)
    {
        $this->store = $store;
        $this->previousPlan = $previousPlan;
    }

    /**
     * Determine if this is a plan downgrade.
     *
     * @return bool
     */
    public function isPlanDowngrade(): bool
    {
        $planHierarchy = [
            'legend' => 3,
            'master' => 2,
            'explorer' => 1
        ];

        $previousPlanLevel = $planHierarchy[strtolower($this->previousPlan)] ?? 0;
        $currentPlanLevel = $planHierarchy[strtolower($this->store->plan->slug ?? $this->store->plan->name)] ?? 0;

        return $currentPlanLevel < $previousPlanLevel;
    }
}