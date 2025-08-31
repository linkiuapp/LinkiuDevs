/**
 * Client-side validation result caching for improved performance
 * Requirements: Performance optimization
 */
class ClientValidationCache {
    constructor() {
        this.cache = new Map();
        this.maxSize = 100; // Maximum number of cached results
        this.defaultTTL = 5 * 60 * 1000; // 5 minutes in milliseconds
        this.hitCount = 0;
        this.missCount = 0;
        
        // Different TTLs for different validation types
        this.ttlConfig = {
            email: 10 * 60 * 1000, // 10 minutes
            slug: 5 * 60 * 1000,   // 5 minutes
            suggestions: 15 * 60 * 1000, // 15 minutes
            billing: 30 * 60 * 1000      // 30 minutes
        };
        
        console.log('🚀 Client validation cache initialized', {
            maxSize: this.maxSize,
            ttlConfig: this.ttlConfig
        });
    }

    /**
     * Generate cache key for validation request
     */
    generateKey(type, params) {
        const sortedParams = Object.keys(params)
            .sort()
            .reduce((result, key) => {
                result[key] = params[key];
                return result;
            }, {});
        
        return `${type}:${JSON.stringify(sortedParams)}`;
    }

    /**
     * Get cached validation result
     */
    get(type, params) {
        const key = this.generateKey(type, params);
        const cached = this.cache.get(key);
        
        if (!cached) {
            this.missCount++;
            console.log('🔍 Cache miss', { type, key, stats: this.getStats() });
            return null;
        }
        
        // Check if expired
        if (Date.now() > cached.expiresAt) {
            this.cache.delete(key);
            this.missCount++;
            console.log('⏰ Cache expired', { type, key, age: Date.now() - cached.timestamp });
            return null;
        }
        
        this.hitCount++;
        console.log('✅ Cache hit', { 
            type, 
            key, 
            age: Date.now() - cached.timestamp,
            stats: this.getStats()
        });
        
        return cached.data;
    }

    /**
     * Store validation result in cache
     */
    set(type, params, data) {
        const key = this.generateKey(type, params);
        const ttl = this.ttlConfig[type] || this.defaultTTL;
        const timestamp = Date.now();
        
        // Implement LRU eviction if cache is full
        if (this.cache.size >= this.maxSize) {
            this.evictOldest();
        }
        
        const cacheEntry = {
            data,
            timestamp,
            expiresAt: timestamp + ttl,
            type,
            accessCount: 1
        };
        
        this.cache.set(key, cacheEntry);
        
        console.log('💾 Cached validation result', {
            type,
            key,
            ttl,
            cacheSize: this.cache.size,
            stats: this.getStats()
        });
    }

    /**
     * Evict oldest cache entry (LRU)
     */
    evictOldest() {
        let oldestKey = null;
        let oldestTimestamp = Date.now();
        
        for (const [key, entry] of this.cache.entries()) {
            if (entry.timestamp < oldestTimestamp) {
                oldestTimestamp = entry.timestamp;
                oldestKey = key;
            }
        }
        
        if (oldestKey) {
            this.cache.delete(oldestKey);
            console.log('🗑️ Evicted oldest cache entry', { key: oldestKey });
        }
    }

    /**
     * Invalidate cache entries by type or pattern
     */
    invalidate(type, pattern = null) {
        let deletedCount = 0;
        
        for (const [key, entry] of this.cache.entries()) {
            if (entry.type === type) {
                if (!pattern || key.includes(pattern)) {
                    this.cache.delete(key);
                    deletedCount++;
                }
            }
        }
        
        console.log('🧹 Cache invalidated', { 
            type, 
            pattern, 
            deletedCount,
            remainingSize: this.cache.size 
        });
    }

    /**
     * Clear all cache entries
     */
    clear() {
        const size = this.cache.size;
        this.cache.clear();
        this.hitCount = 0;
        this.missCount = 0;
        
        console.log('🧽 Cache cleared', { clearedEntries: size });
    }

    /**
     * Get cache statistics
     */
    getStats() {
        const totalRequests = this.hitCount + this.missCount;
        const hitRate = totalRequests > 0 ? (this.hitCount / totalRequests) * 100 : 0;
        
        return {
            size: this.cache.size,
            maxSize: this.maxSize,
            hitCount: this.hitCount,
            missCount: this.missCount,
            hitRate: hitRate.toFixed(2) + '%',
            totalRequests
        };
    }

    /**
     * Get detailed cache information for debugging
     */
    getDebugInfo() {
        const entries = [];
        const now = Date.now();
        
        for (const [key, entry] of this.cache.entries()) {
            entries.push({
                key,
                type: entry.type,
                age: now - entry.timestamp,
                ttl: entry.expiresAt - entry.timestamp,
                timeToExpiry: entry.expiresAt - now,
                accessCount: entry.accessCount,
                expired: now > entry.expiresAt
            });
        }
        
        return {
            stats: this.getStats(),
            entries: entries.sort((a, b) => b.age - a.age), // Sort by age, newest first
            config: {
                maxSize: this.maxSize,
                ttlConfig: this.ttlConfig
            }
        };
    }

    /**
     * Cleanup expired entries (maintenance)
     */
    cleanup() {
        const now = Date.now();
        let cleanedCount = 0;
        
        for (const [key, entry] of this.cache.entries()) {
            if (now > entry.expiresAt) {
                this.cache.delete(key);
                cleanedCount++;
            }
        }
        
        if (cleanedCount > 0) {
            console.log('🧹 Cleaned up expired cache entries', { 
                cleanedCount, 
                remainingSize: this.cache.size 
            });
        }
        
        return cleanedCount;
    }

    /**
     * Update access count for cache entry (for LRU tracking)
     */
    updateAccess(type, params) {
        const key = this.generateKey(type, params);
        const entry = this.cache.get(key);
        
        if (entry) {
            entry.accessCount++;
            entry.lastAccessed = Date.now();
        }
    }

    /**
     * Preload validation results (for predictive caching)
     */
    preload(type, paramsList) {
        console.log('🔮 Preloading validation cache', { type, count: paramsList.length });
        
        // This would typically make batch requests to the server
        // For now, we'll just log the preload request
        paramsList.forEach(params => {
            const key = this.generateKey(type, params);
            console.log('📋 Preload requested', { type, key });
        });
    }
}

/**
 * Enhanced validation engine with caching
 */
class CachedValidationEngine {
    constructor() {
        this.cache = new ClientValidationCache();
        this.pendingRequests = new Map(); // Prevent duplicate requests
        this.performanceMetrics = {
            totalRequests: 0,
            cachedResponses: 0,
            networkRequests: 0,
            averageResponseTime: 0,
            totalResponseTime: 0
        };
        
        // Setup periodic cache cleanup
        setInterval(() => {
            this.cache.cleanup();
        }, 60000); // Every minute
        
        console.log('🎯 Cached validation engine initialized');
    }

    /**
     * Validate email with caching
     */
    async validateEmail(email, storeId = null) {
        const params = { email, store_id: storeId };
        const startTime = performance.now();
        
        // Check cache first
        const cached = this.cache.get('email', params);
        if (cached) {
            this.recordMetrics(performance.now() - startTime, true);
            return cached;
        }
        
        // Check if request is already pending
        const requestKey = this.cache.generateKey('email', params);
        if (this.pendingRequests.has(requestKey)) {
            console.log('⏳ Waiting for pending email validation', { email });
            return await this.pendingRequests.get(requestKey);
        }
        
        // Make network request
        const requestPromise = this.makeValidationRequest('/api/stores/validate-email', params);
        this.pendingRequests.set(requestKey, requestPromise);
        
        try {
            const result = await requestPromise;
            
            // Cache the result
            this.cache.set('email', params, result);
            this.recordMetrics(performance.now() - startTime, false);
            
            return result;
        } finally {
            this.pendingRequests.delete(requestKey);
        }
    }

    /**
     * Validate slug with caching
     */
    async validateSlug(slug, storeId = null) {
        const params = { slug, store_id: storeId };
        const startTime = performance.now();
        
        // Check cache first
        const cached = this.cache.get('slug', params);
        if (cached) {
            this.recordMetrics(performance.now() - startTime, true);
            return cached;
        }
        
        // Check if request is already pending
        const requestKey = this.cache.generateKey('slug', params);
        if (this.pendingRequests.has(requestKey)) {
            console.log('⏳ Waiting for pending slug validation', { slug });
            return await this.pendingRequests.get(requestKey);
        }
        
        // Make network request
        const requestPromise = this.makeValidationRequest('/api/stores/validate-slug', params);
        this.pendingRequests.set(requestKey, requestPromise);
        
        try {
            const result = await requestPromise;
            
            // Cache the result
            this.cache.set('slug', params, result);
            this.recordMetrics(performance.now() - startTime, false);
            
            return result;
        } finally {
            this.pendingRequests.delete(requestKey);
        }
    }

    /**
     * Get slug suggestions with caching
     */
    async getSuggestSlug(slug) {
        const params = { slug };
        const startTime = performance.now();
        
        // Check cache first
        const cached = this.cache.get('suggestions', params);
        if (cached) {
            this.recordMetrics(performance.now() - startTime, true);
            return cached;
        }
        
        // Check if request is already pending
        const requestKey = this.cache.generateKey('suggestions', params);
        if (this.pendingRequests.has(requestKey)) {
            console.log('⏳ Waiting for pending slug suggestions', { slug });
            return await this.pendingRequests.get(requestKey);
        }
        
        // Make network request
        const requestPromise = this.makeValidationRequest('/api/stores/suggest-slug', params);
        this.pendingRequests.set(requestKey, requestPromise);
        
        try {
            const result = await requestPromise;
            
            // Cache the result
            this.cache.set('suggestions', params, result);
            this.recordMetrics(performance.now() - startTime, false);
            
            return result;
        } finally {
            this.pendingRequests.delete(requestKey);
        }
    }

    /**
     * Calculate billing with caching
     */
    async calculateBilling(planId, billingPeriod, discountCode = null) {
        const params = { plan_id: planId, billing_period: billingPeriod, discount_code: discountCode };
        const startTime = performance.now();
        
        // Check cache first
        const cached = this.cache.get('billing', params);
        if (cached) {
            this.recordMetrics(performance.now() - startTime, true);
            return cached;
        }
        
        // Check if request is already pending
        const requestKey = this.cache.generateKey('billing', params);
        if (this.pendingRequests.has(requestKey)) {
            console.log('⏳ Waiting for pending billing calculation', { planId, billingPeriod });
            return await this.pendingRequests.get(requestKey);
        }
        
        // Make network request
        const requestPromise = this.makeValidationRequest('/api/stores/calculate-billing', params);
        this.pendingRequests.set(requestKey, requestPromise);
        
        try {
            const result = await requestPromise;
            
            // Cache the result
            this.cache.set('billing', params, result);
            this.recordMetrics(performance.now() - startTime, false);
            
            return result;
        } finally {
            this.pendingRequests.delete(requestKey);
        }
    }

    /**
     * Make validation request to server
     */
    async makeValidationRequest(endpoint, params) {
        try {
            const response = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                },
                body: JSON.stringify(params)
            });
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const result = await response.json();
            
            if (!result.success) {
                throw new Error(result.message || 'Validation request failed');
            }
            
            return result.data;
        } catch (error) {
            console.error('❌ Validation request failed', { endpoint, params, error });
            throw error;
        }
    }

    /**
     * Record performance metrics
     */
    recordMetrics(responseTime, fromCache) {
        this.performanceMetrics.totalRequests++;
        this.performanceMetrics.totalResponseTime += responseTime;
        this.performanceMetrics.averageResponseTime = 
            this.performanceMetrics.totalResponseTime / this.performanceMetrics.totalRequests;
        
        if (fromCache) {
            this.performanceMetrics.cachedResponses++;
        } else {
            this.performanceMetrics.networkRequests++;
        }
    }

    /**
     * Get performance statistics
     */
    getPerformanceStats() {
        const cacheStats = this.cache.getStats();
        
        return {
            cache: cacheStats,
            performance: {
                ...this.performanceMetrics,
                cacheEfficiency: this.performanceMetrics.totalRequests > 0 
                    ? (this.performanceMetrics.cachedResponses / this.performanceMetrics.totalRequests) * 100 
                    : 0
            },
            pendingRequests: this.pendingRequests.size
        };
    }

    /**
     * Invalidate cache when data changes
     */
    invalidateCache(type, pattern = null) {
        this.cache.invalidate(type, pattern);
    }

    /**
     * Clear all caches
     */
    clearCache() {
        this.cache.clear();
        this.pendingRequests.clear();
        this.performanceMetrics = {
            totalRequests: 0,
            cachedResponses: 0,
            networkRequests: 0,
            averageResponseTime: 0,
            totalResponseTime: 0
        };
    }
}

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { ClientValidationCache, CachedValidationEngine };
} else {
    window.ClientValidationCache = ClientValidationCache;
    window.CachedValidationEngine = CachedValidationEngine;
}

// Initialize global instance
window.validationEngine = new CachedValidationEngine();

console.log('🎯 Client validation cache system loaded');