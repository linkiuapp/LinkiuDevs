/**
 * Lazy loading system for template configurations
 * Requirements: Performance optimization - Add lazy loading for template configurations
 */
class TemplateLazyLoader {
    constructor() {
        this.cache = new Map();
        this.loadingPromises = new Map();
        this.preloadQueue = [];
        this.isPreloading = false;
        
        // Configuration
        this.config = {
            preloadDelay: 100, // ms to wait before preloading
            maxConcurrentLoads: 3,
            cacheTimeout: 10 * 60 * 1000, // 10 minutes
            retryAttempts: 3,
            retryDelay: 1000 // 1 second
        };
        
        console.log('📦 Template lazy loader initialized', this.config);
    }

    /**
     * Load template configuration with lazy loading
     */
    async loadTemplate(templateId) {
        console.log('🔄 Loading template', { templateId });
        
        // Check cache first
        const cached = this.getCachedTemplate(templateId);
        if (cached) {
            console.log('✅ Template loaded from cache', { templateId });
            return cached;
        }
        
        // Check if already loading
        if (this.loadingPromises.has(templateId)) {
            console.log('⏳ Template already loading, waiting...', { templateId });
            return await this.loadingPromises.get(templateId);
        }
        
        // Start loading
        const loadPromise = this.fetchTemplate(templateId);
        this.loadingPromises.set(templateId, loadPromise);
        
        try {
            const template = await loadPromise;
            this.cacheTemplate(templateId, template);
            console.log('✅ Template loaded and cached', { templateId });
            return template;
        } catch (error) {
            console.error('❌ Failed to load template', { templateId, error });
            throw error;
        } finally {
            this.loadingPromises.delete(templateId);
        }
    }

    /**
     * Load multiple templates in batch
     */
    async loadTemplates(templateIds) {
        console.log('📦 Loading multiple templates', { templateIds, count: templateIds.length });
        
        const loadPromises = templateIds.map(id => this.loadTemplate(id));
        
        try {
            const results = await Promise.allSettled(loadPromises);
            const successful = results.filter(r => r.status === 'fulfilled').length;
            const failed = results.filter(r => r.status === 'rejected').length;
            
            console.log('📊 Batch template loading completed', { 
                total: templateIds.length, 
                successful, 
                failed 
            });
            
            return results.map(result => 
                result.status === 'fulfilled' ? result.value : null
            );
        } catch (error) {
            console.error('❌ Batch template loading failed', { templateIds, error });
            throw error;
        }
    }

    /**
     * Preload templates based on usage patterns
     */
    async preloadTemplates(templateIds, priority = 'normal') {
        if (priority === 'high') {
            // Load immediately
            return await this.loadTemplates(templateIds);
        }
        
        // Add to preload queue
        templateIds.forEach(id => {
            if (!this.preloadQueue.includes(id) && !this.cache.has(id)) {
                this.preloadQueue.push(id);
            }
        });
        
        console.log('📋 Templates queued for preloading', { 
            templateIds, 
            queueSize: this.preloadQueue.length 
        });
        
        // Start preloading if not already running
        if (!this.isPreloading) {
            setTimeout(() => this.processPreloadQueue(), this.config.preloadDelay);
        }
    }

    /**
     * Process the preload queue
     */
    async processPreloadQueue() {
        if (this.preloadQueue.length === 0 || this.isPreloading) {
            return;
        }
        
        this.isPreloading = true;
        console.log('🚀 Processing preload queue', { queueSize: this.preloadQueue.length });
        
        try {
            while (this.preloadQueue.length > 0) {
                // Process in batches to avoid overwhelming the server
                const batch = this.preloadQueue.splice(0, this.config.maxConcurrentLoads);
                
                console.log('📦 Preloading batch', { batch, remaining: this.preloadQueue.length });
                
                await Promise.allSettled(
                    batch.map(templateId => this.loadTemplate(templateId))
                );
                
                // Small delay between batches
                if (this.preloadQueue.length > 0) {
                    await new Promise(resolve => setTimeout(resolve, 100));
                }
            }
        } catch (error) {
            console.error('❌ Preload queue processing failed', { error });
        } finally {
            this.isPreloading = false;
            console.log('✅ Preload queue processing completed');
        }
    }

    /**
     * Get template from cache
     */
    getCachedTemplate(templateId) {
        const cached = this.cache.get(templateId);
        
        if (!cached) {
            return null;
        }
        
        // Check if expired
        if (Date.now() > cached.expiresAt) {
            this.cache.delete(templateId);
            console.log('⏰ Cached template expired', { templateId });
            return null;
        }
        
        // Update access time for LRU
        cached.lastAccessed = Date.now();
        cached.accessCount++;
        
        return cached.data;
    }

    /**
     * Cache template data
     */
    cacheTemplate(templateId, data) {
        const cacheEntry = {
            data,
            timestamp: Date.now(),
            expiresAt: Date.now() + this.config.cacheTimeout,
            lastAccessed: Date.now(),
            accessCount: 1
        };
        
        this.cache.set(templateId, cacheEntry);
        
        // Implement cache size limit (LRU eviction)
        if (this.cache.size > 20) { // Max 20 templates in cache
            this.evictLeastRecentlyUsed();
        }
        
        console.log('💾 Template cached', { 
            templateId, 
            cacheSize: this.cache.size,
            expiresIn: this.config.cacheTimeout / 1000 + 's'
        });
    }

    /**
     * Fetch template from server with retry logic
     */
    async fetchTemplate(templateId, attempt = 1) {
        try {
            console.log('🌐 Fetching template from server', { templateId, attempt });
            
            const response = await fetch(`/api/templates/${templateId}/config`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                }
            });
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const result = await response.json();
            
            if (!result.success) {
                throw new Error(result.message || 'Failed to fetch template');
            }
            
            return result.data;
        } catch (error) {
            console.error('❌ Template fetch failed', { templateId, attempt, error });
            
            // Retry logic
            if (attempt < this.config.retryAttempts) {
                console.log('🔄 Retrying template fetch', { templateId, attempt: attempt + 1 });
                await new Promise(resolve => 
                    setTimeout(resolve, this.config.retryDelay * attempt)
                );
                return await this.fetchTemplate(templateId, attempt + 1);
            }
            
            throw error;
        }
    }

    /**
     * Evict least recently used template from cache
     */
    evictLeastRecentlyUsed() {
        let lruKey = null;
        let lruTime = Date.now();
        
        for (const [key, entry] of this.cache.entries()) {
            if (entry.lastAccessed < lruTime) {
                lruTime = entry.lastAccessed;
                lruKey = key;
            }
        }
        
        if (lruKey) {
            this.cache.delete(lruKey);
            console.log('🗑️ Evicted LRU template from cache', { templateId: lruKey });
        }
    }

    /**
     * Prefetch templates based on user behavior
     */
    predictivePreload(currentTemplateId, userBehavior = {}) {
        // Simple prediction based on common patterns
        const predictions = [];
        
        // If user is on basic template, they might want complete next
        if (currentTemplateId === 'basic') {
            predictions.push('complete', 'enterprise');
        }
        
        // If user is on complete template, they might want enterprise
        if (currentTemplateId === 'complete') {
            predictions.push('enterprise');
        }
        
        // Add templates based on user behavior patterns
        if (userBehavior.hasViewedEnterprise) {
            predictions.push('enterprise');
        }
        
        if (userBehavior.isFirstTime) {
            predictions.push('basic', 'complete');
        }
        
        // Remove duplicates and current template
        const uniquePredictions = [...new Set(predictions)]
            .filter(id => id !== currentTemplateId);
        
        if (uniquePredictions.length > 0) {
            console.log('🔮 Predictive preloading', { 
                currentTemplateId, 
                predictions: uniquePredictions 
            });
            this.preloadTemplates(uniquePredictions, 'normal');
        }
    }

    /**
     * Get cache statistics
     */
    getCacheStats() {
        const entries = Array.from(this.cache.entries());
        const now = Date.now();
        
        return {
            totalCached: this.cache.size,
            preloadQueueSize: this.preloadQueue.length,
            isPreloading: this.isPreloading,
            loadingCount: this.loadingPromises.size,
            entries: entries.map(([id, entry]) => ({
                templateId: id,
                age: now - entry.timestamp,
                timeToExpiry: entry.expiresAt - now,
                accessCount: entry.accessCount,
                lastAccessed: now - entry.lastAccessed
            }))
        };
    }

    /**
     * Clear cache and reset state
     */
    clearCache() {
        const size = this.cache.size;
        this.cache.clear();
        this.preloadQueue.length = 0;
        this.loadingPromises.clear();
        this.isPreloading = false;
        
        console.log('🧽 Template cache cleared', { clearedEntries: size });
    }

    /**
     * Warm up cache with essential templates
     */
    async warmUpCache(essentialTemplates = ['basic', 'complete', 'enterprise']) {
        console.log('🔥 Warming up template cache', { essentialTemplates });
        
        try {
            await this.loadTemplates(essentialTemplates);
            console.log('✅ Cache warm-up completed');
        } catch (error) {
            console.error('❌ Cache warm-up failed', { error });
        }
    }

    /**
     * Get template with fallback to basic configuration
     */
    async getTemplateWithFallback(templateId, fallbackId = 'basic') {
        try {
            return await this.loadTemplate(templateId);
        } catch (error) {
            console.warn('⚠️ Template load failed, using fallback', { 
                templateId, 
                fallbackId, 
                error 
            });
            
            try {
                return await this.loadTemplate(fallbackId);
            } catch (fallbackError) {
                console.error('❌ Fallback template also failed', { 
                    fallbackId, 
                    fallbackError 
                });
                
                // Return minimal template structure
                return {
                    id: templateId,
                    name: 'Template',
                    description: 'Basic template configuration',
                    fields: [],
                    validation: {},
                    error: 'Failed to load template configuration'
                };
            }
        }
    }
}

// Initialize global template loader
window.templateLoader = new TemplateLazyLoader();

// Auto-warm cache on page load
document.addEventListener('DOMContentLoaded', () => {
    // Warm up cache with essential templates after a short delay
    setTimeout(() => {
        window.templateLoader.warmUpCache();
    }, 1000);
});

console.log('📦 Template lazy loader system loaded');