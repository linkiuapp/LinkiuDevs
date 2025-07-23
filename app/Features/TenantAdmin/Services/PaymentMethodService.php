<?php

namespace App\Features\TenantAdmin\Services;

use App\Features\TenantAdmin\Models\PaymentMethod;
use App\Features\TenantAdmin\Models\PaymentMethodConfig;
use App\Shared\Models\Store;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentMethodService
{
    /**
     * Get all payment methods for a store.
     *
     * @param Store $store
     * @return Collection
     */
    public function getAvailableMethods(Store $store): Collection
    {
        return $store->paymentMethods()
            ->ordered()
            ->get();
    }

    /**
     * Get active payment methods for a store.
     *
     * @param Store $store
     * @return Collection
     */
    public function getActivePaymentMethods(Store $store): Collection
    {
        return $store->paymentMethods()
            ->active()
            ->ordered()
            ->get();
    }

    /**
     * Get payment methods filtered by delivery type.
     *
     * @param Store $store
     * @param string $deliveryType 'pickup' or 'delivery'
     * @return Collection
     */
    public function getMethodsByDeliveryType(Store $store, string $deliveryType): Collection
    {
        $methods = $this->getActivePaymentMethods($store);
        
        return $methods->filter(function ($method) use ($deliveryType) {
            return $method->isAvailableFor($deliveryType);
        })->values();
    }

    /**
     * Get the default payment method for a store.
     *
     * @param Store $store
     * @return PaymentMethod|null
     */
    public function getDefaultMethod(Store $store): ?PaymentMethod
    {
        $config = PaymentMethodConfig::getForStore($store->id);
        
        if ($config->default_method_id) {
            return PaymentMethod::find($config->default_method_id);
        }
        
        // If no default is set, return the first active method
        return $store->paymentMethods()
            ->active()
            ->ordered()
            ->first();
    }

    /**
     * Set the default payment method for a store.
     *
     * @param Store $store
     * @param int $methodId
     * @return bool
     */
    public function setDefaultMethod(Store $store, int $methodId): bool
    {
        // Verify the method exists and belongs to the store
        $method = PaymentMethod::where('id', $methodId)
            ->where('store_id', $store->id)
            ->where('is_active', true)
            ->firstOrFail();
        
        $config = PaymentMethodConfig::getForStore($store->id);
        return $config->setDefaultMethod($methodId);
    }

    /**
     * Ensure at least one payment method is active.
     *
     * @param Store $store
     * @return bool
     */
    public function ensureActiveMethod(Store $store): bool
    {
        $activeCount = $store->paymentMethods()->where('is_active', true)->count();
        
        if ($activeCount === 0) {
            // If no active methods, activate the first one
            $firstMethod = $store->paymentMethods()->first();
            
            if ($firstMethod) {
                $firstMethod->update(['is_active' => true]);
                return true;
            }
            
            return false;
        }
        
        return true;
    }

    /**
     * Toggle the active status of a payment method.
     *
     * @param PaymentMethod $method
     * @return bool
     * @throws ValidationException
     */
    public function toggleActive(PaymentMethod $method): bool
    {
        // If trying to deactivate
        if ($method->is_active) {
            // Check if this is the only active method
            $activeCount = PaymentMethod::where('store_id', $method->store_id)
                ->where('is_active', true)
                ->count();
            
            if ($activeCount <= 1) {
                throw ValidationException::withMessages([
                    'is_active' => ['Debe haber al menos un método de pago activo.']
                ]);
            }
            
            // If this is the default method, remove it as default
            $config = PaymentMethodConfig::getForStore($method->store_id);
            if ($config->default_method_id === $method->id) {
                // Find another active method to set as default
                $newDefault = PaymentMethod::where('store_id', $method->store_id)
                    ->where('is_active', true)
                    ->where('id', '!=', $method->id)
                    ->first();
                
                if ($newDefault) {
                    $config->setDefaultMethod($newDefault->id);
                } else {
                    $config->update(['default_method_id' => null]);
                }
            }
        }
        
        // Toggle the status
        $method->is_active = !$method->is_active;
        return $method->save();
    }

    /**
     * Update the order of payment methods.
     *
     * @param array $methodIds Ordered array of method IDs
     * @param int $storeId
     * @return bool
     */
    public function updateMethodOrder(array $methodIds, int $storeId): bool
    {
        DB::beginTransaction();
        
        try {
            foreach ($methodIds as $index => $id) {
                PaymentMethod::where('id', $id)
                    ->where('store_id', $storeId)
                    ->update(['sort_order' => $index + 1]);
            }
            
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
    }

    /**
     * Create a new payment method.
     *
     * @param array $data
     * @return PaymentMethod
     */
    public function createPaymentMethod(array $data): PaymentMethod
    {
        // Set default sort order if not provided
        if (!isset($data['sort_order'])) {
            $maxOrder = PaymentMethod::where('store_id', $data['store_id'])->max('sort_order') ?? 0;
            $data['sort_order'] = $maxOrder + 1;
        }
        
        // Create the payment method
        $method = PaymentMethod::create($data);
        
        // If this is the first method, ensure it's active and set as default
        $methodCount = PaymentMethod::where('store_id', $data['store_id'])->count();
        
        if ($methodCount === 1) {
            $method->update(['is_active' => true]);
            
            $config = PaymentMethodConfig::getForStore($data['store_id']);
            $config->setDefaultMethod($method->id);
        }
        
        return $method;
    }

    /**
     * Update an existing payment method.
     *
     * @param PaymentMethod $method
     * @param array $data
     * @return PaymentMethod
     */
    public function updatePaymentMethod(PaymentMethod $method, array $data): PaymentMethod
    {
        $method->update($data);
        return $method;
    }

    /**
     * Delete a payment method.
     *
     * @param PaymentMethod $method
     * @return bool
     * @throws ValidationException
     */
    public function deletePaymentMethod(PaymentMethod $method): bool
    {
        // Check if this is the only method
        $methodCount = PaymentMethod::where('store_id', $method->store_id)->count();
        
        if ($methodCount <= 1) {
            throw ValidationException::withMessages([
                'id' => ['No se puede eliminar el único método de pago.']
            ]);
        }
        
        // Check if this is the only active method
        if ($method->is_active) {
            $activeCount = PaymentMethod::where('store_id', $method->store_id)
                ->where('is_active', true)
                ->count();
            
            if ($activeCount <= 1) {
                throw ValidationException::withMessages([
                    'is_active' => ['No se puede eliminar el único método de pago activo.']
                ]);
            }
        }
        
        // If this is the default method, remove it as default
        $config = PaymentMethodConfig::getForStore($method->store_id);
        if ($config->default_method_id === $method->id) {
            // Find another active method to set as default
            $newDefault = PaymentMethod::where('store_id', $method->store_id)
                ->where('is_active', true)
                ->where('id', '!=', $method->id)
                ->first();
            
            if ($newDefault) {
                $config->setDefaultMethod($newDefault->id);
            } else {
                $config->update(['default_method_id' => null]);
            }
        }
        
        return $method->delete();
    }
}