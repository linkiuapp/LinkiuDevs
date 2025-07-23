<?php

namespace App\Shared\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use App\Shared\Services\TenantService;

class TenantScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        $tenantService = app(TenantService::class);
        
        // Solo aplicar el scope si hay un tenant activo
        if ($tenantService->hasTenant()) {
            $builder->where($model->getTable() . '.tenant_id', $tenantService->getTenantId());
        }
    }
} 