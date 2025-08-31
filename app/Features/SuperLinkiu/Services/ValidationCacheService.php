<?php

namespace App\Features\SuperLinkiu\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Service for caching validation results to improve performance
 * Requirements: Performance optimization
 */
class ValidationCacheService
{
    private const CACHE_PREFIX = 'validation:';
    private const DEFAULT_TTL = 300; // 5 minutes
    private const EMAIL_TTL = 600; // 10 minutes (emails change less frequently)
    private const SLUG_TTL = 300; // 5 minutes
    private const BILLING_TTL = 1800; // 30 minutes (billing calculations are more expensive)
    private const SUGGESTION_TTL = 900; // 15 minutes

    /**
     * Cache email validation result
     */
    public function cacheEmailValidation(string $email, ?int $storeId, array $result): void
    {
        $key = $this->getEmailCacheKey($email, $storeId);
        Cache::put($key, $result, self::EMAIL_TTL);
        
        Log::debug('Cached email validation result', [
            'email' => $email,
            'store_id' => $storeId,
            'cache_key' => $key,
            'ttl' => self::EMAIL_TTL
        ]);
    }

    /**
     * Get cached email validation result
     */
    public function getCachedEmailValidation(string $email, ?int $storeId): ?array
    {
        $key = $this->getEmailCacheKey($email, $storeId);
        $result = Cache::get($key);
        
        if ($result) {
            Log::debug('Retrieved cached email validation', [
                'email' => $email,
                'store_id' => $storeId,
                'cache_key' => $key
            ]);
        }
        
        return $result;
    }

    /**
     * Cache slug validation result
     */
    public function cacheSlugValidation(string $slug, ?int $storeId, array $result): void
    {
        $key = $this->getSlugCacheKey($slug, $storeId);
        Cache::put($key, $result, self::SLUG_TTL);
        
        Log::debug('Cached slug validation result', [
            'slug' => $slug,
            'store_id' => $storeId,
            'cache_key' => $key,
            'ttl' => self::SLUG_TTL
        ]);
    }

    /**
     * Get cached slug validation result
     */
    public function getCachedSlugValidation(string $slug, ?int $storeId): ?array
    {
        $key = $this->getSlugCacheKey($slug, $storeId);
        $result = Cache::get($key);
        
        if ($result) {
            Log::debug('Retrieved cached slug validation', [
                'slug' => $slug,
                'store_id' => $storeId,
                'cache_key' => $key
            ]);
        }
        
        return $result;
    }

    /**
     * Cache slug suggestions
     */
    public function cacheSlugSuggestions(string $baseSlug, array $suggestions): void
    {
        $key = $this->getSuggestionCacheKey($baseSlug);
        Cache::put($key, $suggestions, self::SUGGESTION_TTL);
        
        Log::debug('Cached slug suggestions', [
            'base_slug' => $baseSlug,
            'cache_key' => $key,
            'suggestions_count' => count($suggestions),
            'ttl' => self::SUGGESTION_TTL
        ]);
    }

    /**
     * Get cached slug suggestions
     */
    public function getCachedSlugSuggestions(string $baseSlug): ?array
    {
        $key = $this->getSuggestionCacheKey($baseSlug);
        $result = Cache::get($key);
        
        if ($result) {
            Log::debug('Retrieved cached slug suggestions', [
                'base_slug' => $baseSlug,
                'cache_key' => $key,
                'suggestions_count' => count($result)
            ]);
        }
        
        return $result;
    }

    /**
     * Cache billing calculation result
     */
    public function cacheBillingCalculation(int $planId, string $period, ?string $discountCode, array $result): void
    {
        $key = $this->getBillingCacheKey($planId, $period, $discountCode);
        Cache::put($key, $result, self::BILLING_TTL);
        
        Log::debug('Cached billing calculation', [
            'plan_id' => $planId,
            'period' => $period,
            'discount_code' => $discountCode,
            'cache_key' => $key,
            'ttl' => self::BILLING_TTL
        ]);
    }

    /**
     * Get cached billing calculation result
     */
    public function getCachedBillingCalculation(int $planId, string $period, ?string $discountCode): ?array
    {
        $key = $this->getBillingCacheKey($planId, $period, $discountCode);
        $result = Cache::get($key);
        
        if ($result) {
            Log::debug('Retrieved cached billing calculation', [
                'plan_id' => $planId,
                'period' => $period,
                'discount_code' => $discountCode,
                'cache_key' => $key
            ]);
        }
        
        return $result;
    }

    /**
     * Invalidate email validation cache when a new user/store is created
     */
    public function invalidateEmailCache(string $email): void
    {
        // Invalidate all variations of this email (with and without store_id)
        $patterns = [
            $this->getEmailCacheKey($email, null),
            // We can't easily invalidate all store_id variations, but new creations will override cache
        ];
        
        foreach ($patterns as $pattern) {
            Cache::forget($pattern);
        }
        
        Log::debug('Invalidated email validation cache', [
            'email' => $email,
            'patterns' => $patterns
        ]);
    }

    /**
     * Invalidate slug validation cache when a new store is created
     */
    public function invalidateSlugCache(string $slug): void
    {
        // Invalidate all variations of this slug
        $patterns = [
            $this->getSlugCacheKey($slug, null),
            // We can't easily invalidate all store_id variations, but new creations will override cache
        ];
        
        foreach ($patterns as $pattern) {
            Cache::forget($pattern);
        }
        
        // Also invalidate suggestions that might include this slug
        $this->invalidateSlugSuggestionsContaining($slug);
        
        Log::debug('Invalidated slug validation cache', [
            'slug' => $slug,
            'patterns' => $patterns
        ]);
    }

    /**
     * Invalidate billing cache when plan prices change
     */
    public function invalidateBillingCacheForPlan(int $planId): void
    {
        // We can't easily pattern-match cache keys, so we'll use cache tags if available
        // For now, we'll just log the invalidation request
        Log::info('Billing cache invalidation requested for plan', [
            'plan_id' => $planId,
            'note' => 'Manual cache clearing may be required'
        ]);
    }

    /**
     * Clear all validation caches (for maintenance)
     */
    public function clearAllValidationCaches(): void
    {
        $patterns = [
            self::CACHE_PREFIX . 'email:*',
            self::CACHE_PREFIX . 'slug:*',
            self::CACHE_PREFIX . 'suggestions:*',
            self::CACHE_PREFIX . 'billing:*'
        ];
        
        // Since Laravel doesn't support pattern deletion by default,
        // we'll use cache tags if available or flush specific keys
        foreach ($patterns as $pattern) {
            // This would require a cache driver that supports pattern deletion
            // For now, we'll just log the request
            Log::info('Cache pattern clear requested', ['pattern' => $pattern]);
        }
    }

    /**
     * Get cache statistics for monitoring
     */
    public function getCacheStatistics(): array
    {
        // This would require implementing cache hit/miss tracking
        // For now, return basic info
        return [
            'cache_prefix' => self::CACHE_PREFIX,
            'ttl_settings' => [
                'email' => self::EMAIL_TTL,
                'slug' => self::SLUG_TTL,
                'suggestions' => self::SUGGESTION_TTL,
                'billing' => self::BILLING_TTL
            ],
            'timestamp' => now()->toISOString()
        ];
    }

    /**
     * Generate cache key for email validation
     */
    private function getEmailCacheKey(string $email, ?int $storeId): string
    {
        $email = strtolower(trim($email));
        $suffix = $storeId ? ":{$storeId}" : ':new';
        return self::CACHE_PREFIX . "email:{$email}{$suffix}";
    }

    /**
     * Generate cache key for slug validation
     */
    private function getSlugCacheKey(string $slug, ?int $storeId): string
    {
        $slug = strtolower(trim($slug));
        $suffix = $storeId ? ":{$storeId}" : ':new';
        return self::CACHE_PREFIX . "slug:{$slug}{$suffix}";
    }

    /**
     * Generate cache key for slug suggestions
     */
    private function getSuggestionCacheKey(string $baseSlug): string
    {
        $baseSlug = strtolower(trim($baseSlug));
        return self::CACHE_PREFIX . "suggestions:{$baseSlug}";
    }

    /**
     * Generate cache key for billing calculation
     */
    private function getBillingCacheKey(int $planId, string $period, ?string $discountCode): string
    {
        $discountSuffix = $discountCode ? ":{$discountCode}" : '';
        return self::CACHE_PREFIX . "billing:{$planId}:{$period}{$discountSuffix}";
    }

    /**
     * Invalidate slug suggestions that might contain the given slug
     */
    private function invalidateSlugSuggestionsContaining(string $slug): void
    {
        // This is a simplified approach - in a real implementation,
        // you might want to track which suggestions contain which slugs
        Log::debug('Slug suggestions invalidation requested', [
            'slug' => $slug,
            'note' => 'Suggestions cache may need manual clearing'
        ]);
    }
}