<?php

namespace App\Services;

use App\Shared\Models\Store;
use Illuminate\Support\Facades\Cache;

class PlanUsageService
{
    /**
     * Calculate current usage for all resources
     */
    public function calculateUsage(Store $store): array
    {
        return Cache::remember("store.{$store->id}.usage", 300, function() use ($store) {
            return [
                // PRODUCTOS
                'products' => $store->products()->count(),
                'categories' => $store->categories()->count(),
                'variables' => $store->variables()->count(),
                'product_images' => $this->calculateAverageProductImages($store),
                
                // DISEÑO Y MARKETING
                'sliders' => $store->sliders()->count(),
                'active_coupons' => $store->activeCoupons()->count(),
                
                // ENVÍOS Y LOGÍSTICA
                'locations' => $store->locations()->count(),
                'delivery_zones' => $store->simpleShipping ? $store->simpleShipping->zones()->count() : 0,
                
                // PAGOS
                'payment_methods' => $store->paymentMethods()->active()->count(),
                'bank_accounts' => $store->bankAccounts()->count(),
                
                // VENTAS Y PEDIDOS
                'order_history_months' => $store->plan->order_history_months ?? 6,
                
                // ADMINISTRACIÓN
                'admins' => $store->admins()->count(),
                'tickets_this_month' => $store->tickets()->whereMonth('created_at', now()->month)->count(),
            ];
        });
    }

    /**
     * Get usage percentages for UI display
     */
    public function getUsagePercentages(Store $store): array
    {
        $usage = $this->calculateUsage($store);
        $limits = $this->getPlanLimits($store);
        $percentages = [];

        foreach ($usage as $resource => $used) {
            $limit = $limits[$resource] ?? 1;
            $percentages[$resource] = $limit > 0 ? min(100, ($used / $limit) * 100) : 0;
        }

        return $percentages;
    }

    /**
     * Get plan limits for a store
     */
    public function getPlanLimits(Store $store): array
    {
        $plan = $store->plan;
        
        return [
            // PRODUCTOS
            'products' => $plan->max_products ?? 25,
            'categories' => $plan->max_categories ?? 10,
            'variables' => $plan->max_variables ?? 15,
            'product_images' => $plan->max_product_images ?? 5,
            
            // DISEÑO Y MARKETING
            'sliders' => $plan->max_sliders ?? 3,
            'active_coupons' => $plan->max_active_coupons ?? 5,
            
            // ENVÍOS Y LOGÍSTICA
            'locations' => $plan->max_locations ?? 1,
            'delivery_zones' => $plan->max_delivery_zones ?? 3,
            
            // PAGOS
            'payment_methods' => $plan->max_payment_methods ?? 4,
            'bank_accounts' => $plan->max_bank_accounts ?? 2,
            
            // VENTAS Y PEDIDOS
            'order_history_months' => $plan->order_history_months ?? 6,
            
            // ADMINISTRACIÓN
            'admins' => $plan->max_admins ?? 1,
            'tickets_this_month' => $plan->max_tickets_per_month ?? 5,
        ];
    }

    /**
     * Check if store is near limit for a resource
     */
    public function isNearLimit(Store $store, string $resource, int $threshold = 80): bool
    {
        $percentages = $this->getUsagePercentages($store);
        return ($percentages[$resource] ?? 0) >= $threshold;
    }

    /**
     * Check if store can use more of a resource
     */
    public function canUseResource(Store $store, string $resource): bool
    {
        $usage = $this->calculateUsage($store);
        $limits = $this->getPlanLimits($store);
        
        $used = $usage[$resource] ?? 0;
        $limit = $limits[$resource] ?? 1;
        
        return $used < $limit;
    }

    /**
     * Get overall usage percentage (highest among all resources)
     */
    public function getOverallUsagePercentage(Store $store): float
    {
        $percentages = $this->getUsagePercentages($store);
        
        // Tomar los recursos más importantes para el cálculo general
        $mainResources = ['products', 'categories', 'delivery_zones', 'active_coupons'];
        $mainPercentages = array_intersect_key($percentages, array_flip($mainResources));
        
        return !empty($mainPercentages) ? max($mainPercentages) : 0;
    }

    /**
     * Clear usage cache for a store
     */
    public function clearUsageCache(Store $store): void
    {
        Cache::forget("store.{$store->id}.usage");
    }

    /**
     * Calculate average product images per product
     */
    private function calculateAverageProductImages(Store $store): int
    {
        $totalProducts = $store->products()->count();
        if ($totalProducts === 0) return 0;
        
        $totalImages = $store->products()->withCount('images')->get()->sum('images_count');
        return (int) ceil($totalImages / $totalProducts);
    }
}

