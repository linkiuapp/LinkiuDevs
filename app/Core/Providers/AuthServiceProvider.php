<?php

namespace App\Core\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Features\TenantAdmin\Models\PaymentMethod;
use App\Features\TenantAdmin\Policies\PaymentMethodPolicy;
use App\Features\TenantAdmin\Models\BankAccount;
use App\Features\TenantAdmin\Policies\BankAccountPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        PaymentMethod::class => PaymentMethodPolicy::class,
        BankAccount::class => BankAccountPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Define gates here if needed
    }
}