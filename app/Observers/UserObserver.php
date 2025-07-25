<?php

namespace App\Observers;

use App\Shared\Models\User;
use App\Shared\Models\Store;
use Illuminate\Support\Facades\Log;

class UserObserver
{
    /**
     * Handle the User "creating" event.
     */
    public function creating(User $user): void
    {
        // Si es un store_admin sin store_id, intentar asignarlo automáticamente
        if ($user->role === 'store_admin' && !$user->store_id) {
            $this->autoAssignStoreId($user, 'creating');
        }
    }

    /**
     * Handle the User "updating" event.
     */
    public function updating(User $user): void
    {
        // Si cambia el role a store_admin y no tiene store_id, intentar asignarlo
        if ($user->isDirty('role') && $user->role === 'store_admin' && !$user->store_id) {
            $this->autoAssignStoreId($user, 'updating');
        }
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        //
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        //
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        //
    }

    /**
     * Intentar asignar automáticamente un store_id a un usuario store_admin
     */
    private function autoAssignStoreId(User $user, string $event): void
    {
        Log::info("UserObserver - Intentando asignar store_id automáticamente", [
            'event' => $event,
            'user_email' => $user->email,
            'user_role' => $user->role,
            'current_store_id' => $user->store_id
        ]);

        // Estrategia 1: Si hay context de creación de tienda en la sesión/request
        $contextStoreId = $this->getContextStoreId();
        if ($contextStoreId) {
            $user->store_id = $contextStoreId;
            Log::info("UserObserver - Store ID asignado desde contexto", [
                'user_email' => $user->email,
                'store_id' => $contextStoreId
            ]);
            return;
        }

        // Estrategia 2: Si solo hay una tienda sin admin, asignar a esa
        $storeWithoutAdmin = Store::whereDoesntHave('admins')->first();
        if ($storeWithoutAdmin) {
            $user->store_id = $storeWithoutAdmin->id;
            Log::info("UserObserver - Store ID asignado a tienda sin admin", [
                'user_email' => $user->email,
                'store_id' => $storeWithoutAdmin->id,
                'store_name' => $storeWithoutAdmin->name
            ]);
            return;
        }

        // Estrategia 3: Si no se puede determinar automáticamente, registrar alerta
        Log::warning("UserObserver - No se pudo asignar store_id automáticamente", [
            'user_email' => $user->email,
            'event' => $event,
            'available_stores' => Store::count(),
            'stores_without_admin' => Store::whereDoesntHave('admins')->count()
        ]);

        // Marcar para revisión manual
        $user->setAttribute('needs_store_assignment', true);
    }

    /**
     * Obtener store_id del contexto actual (request, sesión, etc.)
     */
    private function getContextStoreId(): ?int
    {
        // Verificar si hay un store_id en el request actual
        if (request()->has('store_id')) {
            return request()->get('store_id');
        }

        // Verificar si hay contexto de creación de tienda
        if (request()->route() && request()->route()->getName() === 'superlinkiu.stores.store') {
            // Durante la creación de tienda, el store ya está disponible
            $store = request()->get('_created_store');
            if ($store) {
                return $store->id;
            }
        }

        return null;
    }
}
