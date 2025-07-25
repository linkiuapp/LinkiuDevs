<?php

namespace App\Features\TenantAdmin\Services;

use App\Features\TenantAdmin\Models\BankAccount;
use App\Features\TenantAdmin\Models\PaymentMethod;
use App\Shared\Models\Store;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BankAccountService
{
    /**
     * Get active bank accounts for a store.
     *
     * @param Store $store
     * @return Collection
     */
    public function getActiveBankAccounts(Store $store): Collection
    {
        return BankAccount::where('store_id', $store->id)
            ->where('is_active', true)
            ->get();
    }

    /**
     * Get active bank accounts for a specific payment method.
     *
     * @param PaymentMethod $paymentMethod
     * @return Collection
     */
    public function getActiveAccountsForMethod(PaymentMethod $paymentMethod): Collection
    {
        return $paymentMethod->bankAccounts()
            ->where('is_active', true)
            ->get();
    }

    /**
     * Get the maximum number of bank accounts allowed based on the store's plan.
     * Implements requirements 2.1, 2.2, 2.3
     *
     * @param Store $store
     * @return int
     */
    public function getBankAccountLimit(Store $store): int
    {
        // Usar el límite real del plan desde la base de datos
        // Si no está definido en el plan, usar valor por defecto basado en max_sedes
        $maxAccounts = $store->plan->max_bank_accounts ?? null;
        
        if ($maxAccounts === null) {
            // Fallback: usar max_sedes como referencia si no hay campo específico
            $maxSedes = $store->plan->max_sedes ?? 1;
            return min($maxSedes, 3); // Máximo 3 cuentas bancarias
        }
        
        return $maxAccounts;
    }

    /**
     * Check if a store can create more bank accounts based on plan limits.
     *
     * @param Store $store
     * @return bool
     */
    public function canCreateBankAccount(Store $store): bool
    {
        $maxAccounts = $this->getBankAccountLimit($store);
        $currentCount = BankAccount::where('store_id', $store->id)->count();
        
        return $currentCount < $maxAccounts;
    }

    /**
     * Get the remaining bank account slots for a store.
     *
     * @param Store $store
     * @return int
     */
    public function getRemainingBankAccountSlots(Store $store): int
    {
        $maxAccounts = $this->getBankAccountLimit($store);
        $currentCount = BankAccount::where('store_id', $store->id)->count();
        
        return max(0, $maxAccounts - $currentCount);
    }

    /**
     * Validate bank account limits for a store.
     *
     * @param Store $store
     * @param bool $isActivating Whether we're activating an existing account
     * @throws ValidationException
     */
    public function validateBankAccountLimits(Store $store, bool $isActivating = false): void
    {
        $maxAccounts = $this->getBankAccountLimit($store);
        $planName = ucfirst(strtolower($store->plan->name ?? 'Explorer'));
        
        // Count existing accounts (all accounts for creation, only active for activation)
        $query = BankAccount::where('store_id', $store->id);
        if ($isActivating) {
            $query->where('is_active', true);
        }
        $currentCount = $query->count();
        
        // Check if limit is reached
        if ($currentCount >= $maxAccounts) {
            $errorMessage = $isActivating
                ? "No puedes activar más cuentas bancarias. Has alcanzado el límite de {$maxAccounts} cuentas activas para tu plan {$planName}."
                : "Has alcanzado el límite de {$maxAccounts} cuentas bancarias para tu plan {$planName}.";
            
            throw ValidationException::withMessages([
                'store_id' => [$errorMessage]
            ]);
        }
    }

    /**
     * Create a new bank account.
     * Implements requirements 2.1, 2.2, 2.3, 2.4, 2.6
     *
     * @param array $data
     * @return BankAccount
     * @throws ValidationException
     */
    public function createBankAccount(array $data): BankAccount
    {
        // Validate account number format (Requirement 2.6)
        if (!BankAccount::validateAccountNumber($data['account_number'])) {
            throw ValidationException::withMessages([
                'account_number' => ['El número de cuenta debe contener solo dígitos y tener entre 10 y 20 caracteres.']
            ]);
        }
        
        // Validate plan limits (Requirements 2.1, 2.2, 2.3, 2.4)
        $store = Store::findOrFail($data['store_id']);
        $this->validateBankAccountLimits($store, false);
        
        // Create the bank account
        return BankAccount::create($data);
    }

    /**
     * Update an existing bank account.
     *
     * @param BankAccount $bankAccount
     * @param array $data
     * @return BankAccount
     * @throws ValidationException
     */
    public function updateBankAccount(BankAccount $bankAccount, array $data): BankAccount
    {
        // Validate account number format if it's being updated
        if (isset($data['account_number']) && $data['account_number'] !== $bankAccount->account_number) {
            if (!BankAccount::validateAccountNumber($data['account_number'])) {
                throw ValidationException::withMessages([
                    'account_number' => ['El número de cuenta debe contener solo dígitos y tener entre 10 y 20 caracteres.']
                ]);
            }
        }
        
        $bankAccount->update($data);
        return $bankAccount;
    }

    /**
     * Toggle the active status of a bank account.
     *
     * @param BankAccount $bankAccount
     * @param Store $store
     * @return bool
     * @throws ValidationException
     */
    public function toggleActive(BankAccount $bankAccount, Store $store): bool
    {
        // If we're activating the account, validate against plan limits
        if (!$bankAccount->is_active) {
            $this->validateBankAccountLimits($store, true);
        }
        
        $bankAccount->is_active = !$bankAccount->is_active;
        return $bankAccount->save();
    }

    /**
     * Handle plan downgrade by deactivating excess bank accounts.
     * Implements requirement 2.7
     *
     * @param Store $store
     * @return int Number of accounts deactivated
     */
    public function handlePlanDowngrade(Store $store): int
    {
        $maxAccounts = $this->getBankAccountLimit($store);
        $deactivatedCount = 0;
        
        // Get all active accounts
        $activeAccounts = BankAccount::where('store_id', $store->id)
            ->where('is_active', true)
            ->orderBy('created_at', 'desc') // Keep newest accounts active
            ->get();
        
        // If active accounts exceed the limit, deactivate the oldest ones (Requirement 2.7)
        if ($activeAccounts->count() > $maxAccounts) {
            $accountsToDeactivate = $activeAccounts->slice($maxAccounts);
            
            DB::beginTransaction();
            try {
                foreach ($accountsToDeactivate as $account) {
                    $account->update(['is_active' => false]);
                    $deactivatedCount++;
                }
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        }
        
        return $deactivatedCount;
    }

    /**
     * Delete a bank account.
     *
     * @param BankAccount $bankAccount
     * @return bool
     */
    public function deleteBankAccount(BankAccount $bankAccount): bool
    {
        return $bankAccount->delete();
    }
}