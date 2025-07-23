<?php

namespace App\Shared\Services;

use App\Shared\Models\Store;
use Illuminate\Support\Facades\Auth;

class TenantService
{
    protected ?string $tenantId = null;
    protected ?Store $tenant = null;
    
    /**
     * Establecer el tenant actual
     */
    public function setTenant(?Store $tenant): void
    {
        $this->tenant = $tenant;
        $this->tenantId = $tenant?->tenant_id;
        
        // Guardar en sesión para persistencia
        if ($tenant) {
            session(['current_tenant_id' => $this->tenantId]);
            session(['current_store_id' => $tenant->id]);
        } else {
            session()->forget(['current_tenant_id', 'current_store_id']);
        }
    }
    
    /**
     * Establecer tenant por ID
     */
    public function setTenantById(string $tenantId): void
    {
        $this->tenantId = $tenantId;
        session(['current_tenant_id' => $tenantId]);
    }
    
    /**
     * Obtener el tenant actual
     */
    public function getTenant(): ?Store
    {
        if (!$this->tenant && $this->tenantId) {
            $this->tenant = Store::where('tenant_id', $this->tenantId)->first();
        }
        
        return $this->tenant;
    }
    
    /**
     * Obtener el ID del tenant actual
     */
    public function getTenantId(): ?string
    {
        // Si no hay tenant en memoria, intentar recuperar de la sesión
        if (!$this->tenantId) {
            $this->tenantId = session('current_tenant_id');
        }
        
        // Si el usuario autenticado es store_admin, usar su tenant_id
        if (!$this->tenantId && Auth::check() && Auth::user()->isStoreAdmin()) {
            $this->tenantId = Auth::user()->tenant_id;
        }
        
        return $this->tenantId;
    }
    
    /**
     * Verificar si hay un tenant activo
     */
    public function hasTenant(): bool
    {
        return $this->getTenantId() !== null;
    }
    
    /**
     * Limpiar el tenant actual
     */
    public function clearTenant(): void
    {
        $this->tenant = null;
        $this->tenantId = null;
        session()->forget(['current_tenant_id', 'current_store_id']);
    }
    
    /**
     * Ejecutar código sin restricciones de tenant (útil para super admin)
     */
    public function withoutTenant(callable $callback)
    {
        $originalTenantId = $this->tenantId;
        $originalTenant = $this->tenant;
        
        $this->clearTenant();
        
        try {
            return $callback();
        } finally {
            $this->tenantId = $originalTenantId;
            $this->tenant = $originalTenant;
        }
    }
} 