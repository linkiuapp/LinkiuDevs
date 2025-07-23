<?php

namespace App\Features\TenantAdmin\Policies;

use App\Features\TenantAdmin\Models\BankAccount;
use App\Features\TenantAdmin\Services\BankAccountService;
use App\Shared\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class BankAccountPolicy
{
    use HandlesAuthorization;

    /**
     * The bank account service instance.
     *
     * @var \App\Features\TenantAdmin\Services\BankAccountService
     */
    protected $bankAccountService;

    /**
     * Create a new policy instance.
     *
     * @param \App\Features\TenantAdmin\Services\BankAccountService $bankAccountService
     * @return void
     */
    public function __construct(BankAccountService $bankAccountService)
    {
        $this->bankAccountService = $bankAccountService;
    }

    /**
     * Determine whether the user can view any bank accounts.
     *
     * @param  \App\Shared\Models\User  $user
     * @return bool
     */
    public function viewAny(User $user)
    {
        // All authenticated store admins can view bank accounts
        return $user->role === 'store_admin';
    }

    /**
     * Determine whether the user can view the bank account.
     *
     * @param  \App\Shared\Models\User  $user
     * @param  \App\Features\TenantAdmin\Models\BankAccount  $bankAccount
     * @return bool
     */
    public function view(User $user, BankAccount $bankAccount)
    {
        // User can only view bank accounts that belong to their store
        return $user->role === 'store_admin' && $bankAccount->store_id === $user->store_id;
    }

    /**
     * Determine whether the user can create bank accounts.
     * Implements requirements 2.1, 2.2, 2.3, 2.4
     *
     * @param  \App\Shared\Models\User  $user
     * @return bool
     */
    public function create(User $user)
    {
        // User must be a store admin
        if ($user->role !== 'store_admin') {
            return false;
        }

        // Get the store associated with the user
        $store = $user->store;
        if (!$store) {
            return false;
        }

        // Check if the user can create more bank accounts based on their plan
        return $this->bankAccountService->canCreateBankAccount($store);
    }

    /**
     * Determine whether the user can update the bank account.
     *
     * @param  \App\Shared\Models\User  $user
     * @param  \App\Features\TenantAdmin\Models\BankAccount  $bankAccount
     * @return bool
     */
    public function update(User $user, BankAccount $bankAccount)
    {
        // User can only update bank accounts that belong to their store
        return $user->role === 'store_admin' && $bankAccount->store_id === $user->store_id;
    }

    /**
     * Determine whether the user can delete the bank account.
     *
     * @param  \App\Shared\Models\User  $user
     * @param  \App\Features\TenantAdmin\Models\BankAccount  $bankAccount
     * @return bool
     */
    public function delete(User $user, BankAccount $bankAccount)
    {
        // User can only delete bank accounts that belong to their store
        return $user->role === 'store_admin' && $bankAccount->store_id === $user->store_id;
    }

    /**
     * Determine whether the user can activate the bank account.
     * Implements requirements 2.1, 2.2, 2.3, 2.4
     *
     * @param  \App\Shared\Models\User  $user
     * @param  \App\Features\TenantAdmin\Models\BankAccount  $bankAccount
     * @return bool
     */
    public function activate(User $user, BankAccount $bankAccount)
    {
        // User must be a store admin and the bank account must belong to their store
        if ($user->role !== 'store_admin' || $bankAccount->store_id !== $user->store_id) {
            return false;
        }

        // If the bank account is already active, allow the action
        if ($bankAccount->is_active) {
            return true;
        }

        // Get the store associated with the user
        $store = $user->store;
        if (!$store) {
            return false;
        }

        // Check if activating this account would exceed the plan limit
        $activeCount = BankAccount::where('store_id', $store->id)
            ->where('is_active', true)
            ->count();

        $maxAccounts = $this->bankAccountService->getBankAccountLimit($store);

        // If activating this account would exceed the limit, deny the action
        return $activeCount < $maxAccounts;
    }

    /**
     * Determine whether the user can deactivate the bank account.
     *
     * @param  \App\Shared\Models\User  $user
     * @param  \App\Features\TenantAdmin\Models\BankAccount  $bankAccount
     * @return bool
     */
    public function deactivate(User $user, BankAccount $bankAccount)
    {
        // User can only deactivate bank accounts that belong to their store
        return $user->role === 'store_admin' && $bankAccount->store_id === $user->store_id;
    }

    /**
     * Determine whether the user exceeds the bank account limit for their plan.
     * Implements requirements 2.1, 2.2, 2.3, 2.4
     *
     * @param  \App\Shared\Models\User  $user
     * @return bool
     */
    public function exceedsLimit(User $user)
    {
        // User must be a store admin
        if ($user->role !== 'store_admin') {
            return true;
        }

        // Get the store associated with the user
        $store = $user->store;
        if (!$store) {
            return true;
        }

        // Check if the user has reached the limit of bank accounts for their plan
        return !$this->bankAccountService->canCreateBankAccount($store);
    }
}