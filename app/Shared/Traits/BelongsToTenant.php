<?php

namespace App\Shared\Traits;

use App\Shared\Scopes\TenantScope;
use App\Shared\Services\TenantService;
use Illuminate\Database\Eloquent\Model;

trait BelongsToTenant
{
    /**
     * Boot del trait para añadir el scope y eventos
     */
    protected static function bootBelongsToTenant(): void
    {
        // Añadir el TenantScope global
        static::addGlobalScope(new TenantScope());
        
        // Al crear un modelo, asignar automáticamente el tenant_id
        static::creating(function (Model $model) {
            $tenantService = app(TenantService::class);
            
            if ($tenantService->hasTenant() && !$model->tenant_id) {
                $model->tenant_id = $tenantService->getTenantId();
            }
        });
    }
    
    /**
     * Scope para incluir registros de todos los tenants (útil para super admin)
     */
    public function scopeAllTenants($query)
    {
        return $query->withoutGlobalScope(TenantScope::class);
    }
    
    /**
     * Scope para filtrar por un tenant específico
     */
    public function scopeForTenant($query, $tenantId)
    {
        return $query->withoutGlobalScope(TenantScope::class)
                     ->where('tenant_id', $tenantId);
    }
    
    /**
     * Verificar si el modelo pertenece al tenant actual
     */
    public function belongsToCurrentTenant(): bool
    {
        $tenantService = app(TenantService::class);
        
        if (!$tenantService->hasTenant()) {
            return false;
        }
        
        return $this->tenant_id === $tenantService->getTenantId();
    }
} 