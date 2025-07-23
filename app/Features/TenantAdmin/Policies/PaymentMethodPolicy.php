<?php

namespace App\Features\TenantAdmin\Policies;

use App\Features\TenantAdmin\Models\PaymentMethod;
use App\Shared\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PaymentMethodPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any payment methods.
     *
     * @param  \App\Shared\Models\User  $user
     * @return bool
     */
    public function viewAny(User $user)
    {
        // All authenticated store admins can view payment methods
        return $user->role === 'store_admin';
    }

    /**
     * Determine whether the user can view the payment method.
     *
     * @param  \App\Shared\Models\User  $user
     * @param  \App\Features\TenantAdmin\Models\PaymentMethod  $paymentMethod
     * @return bool
     */
    public function view(User $user, PaymentMethod $paymentMethod)
    {
        // User can only view payment methods that belong to their store
        return $user->role === 'store_admin' && $paymentMethod->store_id === $user->store_id;
    }

    /**
     * Determine whether the user can create payment methods.
     *
     * @param  \App\Shared\Models\User  $user
     * @return bool
     */
    public function create(User $user)
    {
        // All authenticated store admins can create payment methods
        return $user->role === 'store_admin';
    }

    /**
     * Determine whether the user can update the payment method.
     *
     * @param  \App\Shared\Models\User  $user
     * @param  \App\Features\TenantAdmin\Models\PaymentMethod  $paymentMethod
     * @return bool
     */
    public function update(User $user, PaymentMethod $paymentMethod)
    {
        // User can only update payment methods that belong to their store
        return $user->role === 'store_admin' && $paymentMethod->store_id === $user->store_id;
    }

    /**
     * Determine whether the user can delete the payment method.
     *
     * @param  \App\Shared\Models\User  $user
     * @param  \App\Features\TenantAdmin\Models\PaymentMethod  $paymentMethod
     * @return bool
     */
    public function delete(User $user, PaymentMethod $paymentMethod)
    {
        // User can only delete payment methods that belong to their store
        // Additionally, check if there are other active payment methods to prevent deleting the last one
        if ($user->role !== 'store_admin' || $paymentMethod->store_id !== $user->store_id) {
            return false;
        }

        // Count active payment methods for this store
        $activeCount = PaymentMethod::where('store_id', $paymentMethod->store_id)
            ->where('is_active', true)
            ->count();

        // If this is the only active method and it's active, don't allow deletion
        if ($activeCount <= 1 && $paymentMethod->is_active) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can toggle the active status of the payment method.
     *
     * @param  \App\Shared\Models\User  $user
     * @param  \App\Features\TenantAdmin\Models\PaymentMethod  $paymentMethod
     * @return bool
     */
    public function toggleActive(User $user, PaymentMethod $paymentMethod)
    {
        // User can only toggle payment methods that belong to their store
        if ($user->role !== 'store_admin' || $paymentMethod->store_id !== $user->store_id) {
            return false;
        }

        // If trying to deactivate, check if there are other active payment methods
        if ($paymentMethod->is_active) {
            $activeCount = PaymentMethod::where('store_id', $paymentMethod->store_id)
                ->where('is_active', true)
                ->count();

            // If this is the only active method, don't allow deactivation
            if ($activeCount <= 1) {
                return false;
            }
        }

        return true;
    }

    /**
     * Determine whether the user can update the order of payment methods.
     *
     * @param  \App\Shared\Models\User  $user
     * @return bool
     */
    public function updateOrder(User $user)
    {
        // All authenticated store admins can update the order of payment methods
        return $user->role === 'store_admin';
    }
}