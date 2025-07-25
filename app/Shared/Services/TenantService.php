<?php

namespace App\Shared\Services;

use App\Shared\Models\Store;
use Illuminate\Support\Facades\Auth;

class TenantService
{
    protected ?int $storeId = null;
    protected ?Store $tenant = null;
    
    /**
     * Establecer el tenant actual
     */
    public function setTenant(?Store $tenant): void
    {
        $this->tenant = $tenant;
        $this->storeId = $tenant?->id;
        
        // Guardar en sesión para persistencia
        if ($tenant) {
            session(['current_store_id' => $tenant->id]);
        } else {
            session()->forget(['current_store_id']);
        }
    }
    
    /**
     * Establecer tenant por store ID
     */
    public function setTenantById(int $storeId): void
    {
        $this->storeId = $storeId;
        session(['current_store_id' => $storeId]);
        
        // Limpiar el objeto tenant para que se recargue
        $this->tenant = null;
    }
    
    /**
     * Obtener el tenant actual
     */
    public function getTenant(): ?Store
    {
        if (!$this->tenant && $this->storeId) {
            $this->tenant = Store::find($this->storeId);
        }
        
        return $this->tenant;
    }
    
    /**
     * Obtener el ID del store actual
     */
    public function getStoreId(): ?int
    {
        // Si no hay store en memoria, intentar recuperar de la sesión
        if (!$this->storeId) {
            $this->storeId = session('current_store_id');
        }
        
        // Si el usuario autenticado es store_admin, usar su store_id
        if (!$this->storeId && Auth::check() && Auth::user()->isStoreAdmin()) {
            $this->storeId = Auth::user()->store_id;
        }
        
        return $this->storeId;
    }
    
    /**
     * Verificar si hay un tenant activo
     */
    public function hasTenant(): bool
    {
        return $this->getStoreId() !== null;
    }
    
    /**
     * Limpiar el tenant actual
     */
    public function clearTenant(): void
    {
        $this->tenant = null;
        $this->storeId = null;
        session()->forget(['current_store_id']);
    }
    
    /**
     * Ejecutar código sin restricciones de tenant (útil para super admin)
     */
    public function withoutTenant(callable $callback)
    {
        $originalStoreId = $this->storeId;
        $originalTenant = $this->tenant;
        
        $this->clearTenant();
        
        try {
            return $callback();
        } finally {
            $this->storeId = $originalStoreId;
            $this->tenant = $originalTenant;
        }
    }

    /**
     * Métodos de compatibilidad (deprecated)
     * @deprecated Use getStoreId() instead
     */
    public function getTenantId(): ?int
    {
        return $this->getStoreId();
    }
} 