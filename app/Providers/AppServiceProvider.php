<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Shared\Models\User;
use App\Shared\Models\Store;
use App\Observers\UserObserver;
use App\Shared\Observers\StoreObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Solucionar el problema de "Specified key was too long"
        \Illuminate\Support\Facades\Schema::defaultStringLength(191);
        
        // Registrar Observers
        User::observe(UserObserver::class);
        Store::observe(StoreObserver::class);
    }
}
