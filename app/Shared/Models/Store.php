<?php

namespace App\Shared\Models;

use App\Features\TenantAdmin\Events\StorePlanChanged;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Store extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'plan_id',
        'name',
        'slug',
        'status',
        'verified',
        'document_type',
        'document_number',
        'phone',
        'email',
        'address',
        'country',
        'department',
        'city',
        'description',
        'privacy_policy_text',
        'shipping_policy_text',
        'logo_url',
        'header_background_color',
        'header_text_title',
        'header_text_color',
        'header_short_description',
        'header_short_description_color',
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    protected $casts = [
        'verified' => 'boolean',
        'last_active_at' => 'datetime',
    ];

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    // Relaciones
    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function planExtensions()
    {
        return $this->hasMany(StorePlanExtension::class);
    }

    public function policies()
    {
        return $this->hasOne(StorePolicy::class);
    }

    public function admins()
    {
        return $this->hasMany(User::class)->where('role', 'store_admin');
    }

    /**
     * Get the design configuration for this store.
     */
    public function design()
    {
        return $this->hasOne(\App\Features\TenantAdmin\Models\StoreDesign::class);
    }

    /**
     * Get the categories for this store.
     */
    public function categories()
    {
        return $this->hasMany(\App\Features\TenantAdmin\Models\Category::class);
    }

    /**
     * Get the categories count accessor.
     */
    public function getCategoriesCountAttribute()
    {
        return $this->categories()->count();
    }

    /**
     * Get the products for this store.
     */
    public function products()
    {
        return $this->hasMany(\App\Features\TenantAdmin\Models\Product::class);
    }

    /**
     * Get the products count accessor.
     */
    public function getProductsCountAttribute()
    {
        return $this->products()->count();
    }

    /**
     * Get the sliders for this store.
     */
    public function sliders()
    {
        return $this->hasMany(\App\Features\TenantAdmin\Models\Slider::class);
    }

    /**
     * Get the sliders count accessor.
     */
    public function getSlidersCountAttribute()
    {
        return $this->sliders()->count();
    }

    /**
     * Get the variables for this store.
     */
    public function variables()
    {
        return $this->hasMany(\App\Features\TenantAdmin\Models\ProductVariable::class);
    }

    /**
     * Get the variables count accessor.
     */
    public function getVariablesCountAttribute()
    {
        return $this->variables()->count();
    }
    
    /**
     * Get the locations for this store.
     */
    public function locations()
    {
        return $this->hasMany(Location::class);
    }
    
    /**
     * Get the main location for this store.
     */
    public function mainLocation()
    {
        return $this->hasOne(Location::class)->where('is_main', true);
    }
    
    /**
     * Get the active locations for this store.
     */
    public function activeLocations()
    {
        return $this->locations()->where('is_active', true);
    }
    
    /**
     * Get the locations count accessor.
     */
    public function getLocationsCountAttribute()
    {
        return $this->locations()->count();
    }
    
    /**
     * Get the active locations count accessor.
     */
    public function getActiveLocationsCountAttribute()
    {
        return $this->activeLocations()->count();
    }
    
    /**
     * Get the invoices for this store.
     */
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
    
    /**
     * Get the payment methods for this store.
     */
    public function paymentMethods()
    {
        return $this->hasMany(\App\Features\TenantAdmin\Models\PaymentMethod::class);
    }
    
    /**
     * Get the payment methods count accessor.
     */
    public function getPaymentMethodsCountAttribute()
    {
        return $this->paymentMethods()->count();
    }
    
    /**
     * Get the bank accounts for this store.
     */
    public function bankAccounts()
    {
        return $this->hasMany(\App\Features\TenantAdmin\Models\BankAccount::class);
    }
    
    /**
     * Get the bank accounts count accessor.
     */
    public function getBankAccountsCountAttribute()
    {
        return $this->bankAccounts()->count();
    }
    
    
    /**
     * Get the simple shipping configuration for this store.
     */
    public function simpleShipping()
    {
        return $this->hasOne(\App\Features\TenantAdmin\Models\SimpleShipping::class);
    }
    

    /**
     * Get all tickets for this store.
     */
    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    /**
     * Get count of unread support responses for this store.
     * Support responses are responses from super_admin users created after
     * the store admin's last activity on the ticket.
     */
    public function getUnreadSupportResponsesCountAttribute(): int
    {
        // Contar tickets con respuestas no leídas del soporte
        return $this->tickets()
            ->with('responses.user')
            ->get()
            ->sum(function($ticket) {
                return $ticket->new_support_responses_count;
            });
    }

    /**
     * Get count of unread announcements for this store.
     */
    public function getUnreadAnnouncementsCountAttribute(): int
    {
        $storePlan = strtolower($this->plan->name ?? $this->plan->slug ?? 'explorer');
        
        return \App\Shared\Models\PlatformAnnouncement::active()
            ->forPlan($storePlan)
            ->forStore($this->id)
            ->whereDoesntHave('reads', function ($q) {
                $q->where('store_id', $this->id);
            })
            ->count();
    }

    /**
     * Get the orders for this store.
     */
    public function orders()
    {
        return $this->hasMany(\App\Shared\Models\Order::class);
    }

    /**
     * Get the pending orders count accessor.
     */
    public function getPendingOrdersCountAttribute()
    {
        return $this->orders()->where('status', 'pending')->count();
    }

    /**
     * Get the total orders count accessor.
     */
    public function getOrdersCountAttribute()
    {
        return $this->orders()->count();
    }

    /**
     * Get the active coupons for this store.
     */
    public function activeCoupons()
    {
        return $this->hasMany(\App\Features\TenantAdmin\Models\Coupon::class)->where('is_active', true);
    }

    /**
     * Get the active coupons count accessor.
     */
    public function getActiveCouponsCountAttribute()
    {
        return $this->activeCoupons()->count();
    }

    /**
     * Get the subscription for this store.
     */
    public function subscription()
    {
        return $this->hasOne(\App\Shared\Models\Subscription::class);
    }

    /**
     * Get the subscription history for this store.
     */
    public function subscriptionHistory()
    {
        return $this->hasMany(\App\Shared\Models\SubscriptionHistory::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeVerified($query)
    {
        return $query->where('verified', true);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    // Métodos de ayuda
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Get the full document (type + number)
     */
    public function getFullDocument(): ?string
    {
        if (empty($this->document_type) || empty($this->document_number)) {
            return null;
        }
        
        return $this->document_type . ' - ' . $this->document_number;
    }

    /**
     * Get the full address (address, city, department, country)
     */
    public function getFullAddress(): ?string
    {
        $addressParts = array_filter([
            $this->address,
            $this->city,
            $this->department,
            $this->country
        ]);
        
        if (empty($addressParts)) {
            return null;
        }
        
        return implode(', ', $addressParts);
    }

    /**
     * Métodos de negocio
     */
    public function activate()
    {
        $this->update(['status' => 'active']);
    }

    public function deactivate()
    {
        $this->update(['status' => 'inactive']);
    }

    public function suspend()
    {
        $this->update(['status' => 'suspended']);
    }

    public function updateStatus($status)
    {
        $this->update(['status' => $status]);
    }

    /**
     * Toggle verification status
     */
    public function toggleVerified()
    {
        $this->verified = !$this->verified;
        $this->save();
        
        return $this;
    }

    /**
     * Mark as verified
     */
    public function markAsVerified()
    {
        $this->update(['verified' => true]);
    }

    /**
     * Mark as unverified
     */
    public function markAsUnverified()
    {
        $this->update(['verified' => false]);
    }

    public function getRouteKey()
    {
        return $this->slug;
    }
    
    /**
     * Change the store's plan and trigger the StorePlanChanged event.
     * This method is used to handle plan changes and trigger necessary actions.
     *
     * @param int $planId The ID of the new plan
     * @return bool Whether the plan was changed successfully
     */
    public function changePlan(int $planId): bool
    {
        // Get the current plan before changing it
        $previousPlan = $this->plan ? ($this->plan->slug ?? $this->plan->name) : 'unknown';
        
        // Update the plan_id
        $this->plan_id = $planId;
        $result = $this->save();
        
        if ($result) {
            // Reload the plan relationship to get the new plan data
            $this->load('plan');
            
            // Dispatch the event with the store and previous plan
            event(new StorePlanChanged($this, $previousPlan));
        }
        
        return $result;
    }

    /**
     * Get the drafts associated with this store
     */
    public function drafts()
    {
        return $this->hasMany(\App\Models\StoreDraft::class);
    }
} 