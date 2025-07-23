<?php

namespace App\Features\TenantAdmin\Controllers;

use App\Http\Controllers\Controller;
use App\Features\TenantAdmin\Models\BankAccount;
use App\Features\TenantAdmin\Models\PaymentMethod;
use App\Features\TenantAdmin\Services\BankAccountService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class BankAccountController extends Controller
{
    protected $bankAccountService;
    
    public function __construct(BankAccountService $bankAccountService)
    {
        $this->bankAccountService = $bankAccountService;
    }
    
    /**
     * Validate bank account limits based on the store's subscription plan.
     * Implements requirements 2.1, 2.2, 2.3, 2.4
     * 
     * @param \App\Shared\Models\Store $store
     * @param bool $isActivating Whether we're activating an existing account
     * @return array|null Returns error data if validation fails, null if passes
     */
    protected function validatePlanLimits($store, $isActivating = false)
    {
        try {
            // Use the service to validate bank account limits
            $this->bankAccountService->validateBankAccountLimits($store, $isActivating);
            return null;
        } catch (ValidationException $e) {
            // Get plan limits information for the error message
            $maxAccounts = $this->bankAccountService->getBankAccountLimit($store);
            $planName = ucfirst(strtolower($store->plan->name ?? 'Explorer'));
            
            // Count existing accounts (all accounts for creation, only active for activation)
            $query = BankAccount::where('store_id', $store->id);
            if ($isActivating) {
                $query->where('is_active', true);
            }
            $currentCount = $query->count();
            
            // Get the error message from the exception
            $errorMessage = $e->getMessage();
            if (empty($errorMessage)) {
                $errorMessage = $isActivating
                    ? "No puedes activar más cuentas bancarias. Has alcanzado el límite de {$maxAccounts} cuentas activas para tu plan {$planName}."
                    : "Has alcanzado el límite de {$maxAccounts} cuentas bancarias para tu plan {$planName}.";
            }
            
            return [
                'error' => $errorMessage,
                'maxAccounts' => $maxAccounts,
                'currentCount' => $currentCount,
                'planName' => $planName
            ];
        }
    }
    
    /**
     * Display a listing of the bank accounts.
     * 
     * @param Request $request
     * @param string $store
     * @param PaymentMethod $paymentMethod
     * @return \Illuminate\View\View
     */
    public function index(Request $request, $store, PaymentMethod $paymentMethod)
    {
        // Get current store from view shared data (set by middleware)
        $store = view()->shared('currentStore');
        
        // Ensure payment method belongs to current store
        if ($paymentMethod->store_id !== $store->id) {
            abort(404);
        }
        
        // Ensure payment method is bank transfer type
        if (!$paymentMethod->isBankTransfer()) {
            return redirect()->route('tenant.admin.payment-methods.show', ['store' => $store->slug, 'paymentMethod' => $paymentMethod->id])
                ->with('error', 'Solo los métodos de transferencia bancaria pueden tener cuentas asociadas.');
        }
        
        // Get bank accounts for this payment method
        $bankAccounts = $paymentMethod->bankAccounts()->orderBy('is_active', 'desc')->orderBy('created_at', 'desc')->get();
        
        // Calculate plan limits
        $maxAccounts = $this->bankAccountService->getBankAccountLimit($store);
        $currentCount = BankAccount::where('store_id', $store->id)->count();
        $remainingSlots = $this->bankAccountService->getRemainingBankAccountSlots($store);
        $canCreateMore = $this->bankAccountService->canCreateBankAccount($store);
        $planName = ucfirst(strtolower($store->plan->name ?? 'Explorer'));
        
        // Check if there's a flash message about plan limits
        if (session('plan_limit_warning')) {
            session()->flash('warning', session('plan_limit_warning'));
        }
        
        return view('tenant-admin::bank-accounts.index', compact(
            'bankAccounts',
            'paymentMethod',
            'store',
            'maxAccounts',
            'currentCount',
            'remainingSlots',
            'canCreateMore',
            'planName'
        ));
    }
    
    /**
     * Show the form for creating a new bank account.
     * 
     * @param Request $request
     * @param string $store
     * @param PaymentMethod $paymentMethod
     * @return \Illuminate\View\View
     */
    public function create(Request $request, $store, PaymentMethod $paymentMethod)
    {
        // Get current store from view shared data
        $store = view()->shared('currentStore');
        
        // Ensure payment method belongs to current store
        if ($paymentMethod->store_id !== $store->id) {
            abort(404);
        }
        
        // Ensure payment method is bank transfer type
        if (!$paymentMethod->isBankTransfer()) {
            return redirect()->route('tenant.admin.payment-methods.show', ['store' => $store->slug, 'paymentMethod' => $paymentMethod->id])
                ->with('error', 'Solo los métodos de transferencia bancaria pueden tener cuentas asociadas.');
        }
        
        // Check if store can create more bank accounts based on plan limits (Requirements 2.1, 2.2, 2.3, 2.4)
        $validationResult = $this->validatePlanLimits($store);
        if ($validationResult) {
            return redirect()->route('tenant.admin.bank-accounts.index', ['store' => $store->slug, 'paymentMethod' => $paymentMethod->id])
                ->with('error', $validationResult['error']);
        }
        
        // Define account types
        $accountTypes = [
            'savings' => 'Cuenta de Ahorros',
            'checking' => 'Cuenta Corriente'
        ];
        
        return view('tenant-admin::bank-accounts.create', compact(
            'paymentMethod',
            'store',
            'accountTypes'
        ));
    }
    
    /**
     * Store a newly created bank account in storage.
     * 
     * @param Request $request
     * @param string $store
     * @param PaymentMethod $paymentMethod
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, $store, PaymentMethod $paymentMethod)
    {
        // Get current store from view shared data
        $store = view()->shared('currentStore');
        
        // Ensure payment method belongs to current store
        if ($paymentMethod->store_id !== $store->id) {
            abort(404);
        }
        
        // Ensure payment method is bank transfer type
        if (!$paymentMethod->isBankTransfer()) {
            return redirect()->route('tenant.admin.payment-methods.show', ['store' => $store->slug, 'paymentMethod' => $paymentMethod->id])
                ->with('error', 'Solo los métodos de transferencia bancaria pueden tener cuentas asociadas.');
        }
        
        // Check plan limits before proceeding (Requirements 2.1, 2.2, 2.3, 2.4)
        $validationResult = $this->validatePlanLimits($store);
        if ($validationResult) {
            return redirect()->route('tenant.admin.bank-accounts.index', ['store' => $store->slug, 'paymentMethod' => $paymentMethod->id])
                ->with('error', $validationResult['error']);
        }
        
        // Validate request
        $validator = Validator::make($request->all(), [
            'bank_name' => 'required|string|max:100',
            'account_type' => 'required|string|in:savings,checking',
            'account_number' => 'required|string|min:10|max:20',
            'account_holder' => 'required|string|max:100',
            'document_number' => 'nullable|string|max:20',
            'is_active' => 'nullable|boolean',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        
        try {
            // Prepare data for bank account
            $data = [
                'payment_method_id' => $paymentMethod->id,
                'bank_name' => $request->bank_name,
                'account_type' => $request->account_type,
                'account_number' => $request->account_number,
                'account_holder' => $request->account_holder,
                'document_number' => $request->document_number,
                'is_active' => $request->has('is_active'),
                'store_id' => $store->id,
            ];
            
            // Create bank account
            $bankAccount = $this->bankAccountService->createBankAccount($data);
            
            // Check if this was the last available slot
            $remainingSlots = $this->bankAccountService->getRemainingBankAccountSlots($store);
            $successMessage = 'Cuenta bancaria creada exitosamente.';
            
            if ($remainingSlots === 0) {
                $maxAccounts = $this->bankAccountService->getBankAccountLimit($store);
                $successMessage .= " Has alcanzado el límite de {$maxAccounts} cuentas bancarias para tu plan actual.";
            } else if ($remainingSlots === 1) {
                $successMessage .= " Te queda 1 espacio disponible para cuentas bancarias en tu plan actual.";
            }
            
            return redirect()->route('tenant.admin.bank-accounts.index', ['store' => $store->slug, 'paymentMethod' => $paymentMethod->id])
                ->with('success', $successMessage);
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al crear la cuenta bancaria: ' . $e->getMessage())->withInput();
        }
    }
    
    /**
     * Show the form for editing the specified bank account.
     * 
     * @param Request $request
     * @param string $store
     * @param PaymentMethod $paymentMethod
     * @param BankAccount $bankAccount
     * @return \Illuminate\View\View
     */
    public function edit(Request $request, $store, PaymentMethod $paymentMethod, BankAccount $bankAccount)
    {
        // Get current store from view shared data
        $store = view()->shared('currentStore');
        
        // Ensure payment method belongs to current store
        if ($paymentMethod->store_id !== $store->id) {
            abort(404);
        }
        
        // Ensure bank account belongs to this payment method
        if ($bankAccount->payment_method_id !== $paymentMethod->id) {
            abort(404);
        }
        
        // Ensure bank account belongs to current store
        if ($bankAccount->store_id !== $store->id) {
            abort(404);
        }
        
        // Define account types
        $accountTypes = [
            'savings' => 'Cuenta de Ahorros',
            'checking' => 'Cuenta Corriente'
        ];
        
        return view('tenant-admin::bank-accounts.edit', compact(
            'bankAccount',
            'paymentMethod',
            'store',
            'accountTypes'
        ));
    }
    
    /**
     * Update the specified bank account in storage.
     * 
     * @param Request $request
     * @param string $store
     * @param PaymentMethod $paymentMethod
     * @param BankAccount $bankAccount
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $store, PaymentMethod $paymentMethod, BankAccount $bankAccount)
    {
        // Get current store from view shared data
        $store = view()->shared('currentStore');
        
        // Ensure payment method belongs to current store
        if ($paymentMethod->store_id !== $store->id) {
            abort(404);
        }
        
        // Ensure bank account belongs to this payment method
        if ($bankAccount->payment_method_id !== $paymentMethod->id) {
            abort(404);
        }
        
        // Ensure bank account belongs to current store
        if ($bankAccount->store_id !== $store->id) {
            abort(404);
        }
        
        // Check if trying to activate an inactive account and validate plan limits (Requirements 2.1, 2.2, 2.3, 2.4)
        $isActivating = $request->has('is_active') && !$bankAccount->is_active;
        if ($isActivating) {
            $validationResult = $this->validatePlanLimits($store, true);
            if ($validationResult) {
                return redirect()->route('tenant.admin.bank-accounts.index', ['store' => $store->slug, 'paymentMethod' => $paymentMethod->id])
                    ->with('error', $validationResult['error']);
            }
        }
        
        // Validate request
        $validator = Validator::make($request->all(), [
            'bank_name' => 'required|string|max:100',
            'account_type' => 'required|string|in:savings,checking',
            'account_number' => 'required|string|min:10|max:20',
            'account_holder' => 'required|string|max:100',
            'document_number' => 'nullable|string|max:20',
            'is_active' => 'nullable|boolean',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        
        try {
            // Prepare data for bank account update
            $data = [
                'bank_name' => $request->bank_name,
                'account_type' => $request->account_type,
                'account_number' => $request->account_number,
                'account_holder' => $request->account_holder,
                'document_number' => $request->document_number,
                'is_active' => $request->has('is_active'),
            ];
            
            // Update bank account
            $this->bankAccountService->updateBankAccount($bankAccount, $data);
            
            // Add information about plan limits if account was activated
            $successMessage = 'Cuenta bancaria actualizada exitosamente.';
            if ($isActivating) {
                $remainingSlots = $this->bankAccountService->getRemainingBankAccountSlots($store);
                if ($remainingSlots === 0) {
                    $maxAccounts = $this->bankAccountService->getBankAccountLimit($store);
                    $successMessage .= " Has alcanzado el límite de {$maxAccounts} cuentas bancarias activas para tu plan actual.";
                }
            }
            
            return redirect()->route('tenant.admin.bank-accounts.index', ['store' => $store->slug, 'paymentMethod' => $paymentMethod->id])
                ->with('success', $successMessage);
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al actualizar la cuenta bancaria: ' . $e->getMessage())->withInput();
        }
    }
    
    /**
     * Remove the specified bank account from storage.
     * 
     * @param Request $request
     * @param string $store
     * @param PaymentMethod $paymentMethod
     * @param BankAccount $bankAccount
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request, $store, PaymentMethod $paymentMethod, BankAccount $bankAccount)
    {
        // Get current store from view shared data
        $store = view()->shared('currentStore');
        
        // Ensure payment method belongs to current store
        if ($paymentMethod->store_id !== $store->id) {
            abort(404);
        }
        
        // Ensure bank account belongs to this payment method
        if ($bankAccount->payment_method_id !== $paymentMethod->id) {
            abort(404);
        }
        
        // Ensure bank account belongs to current store
        if ($bankAccount->store_id !== $store->id) {
            abort(404);
        }
        
        try {
            // Delete bank account
            $this->bankAccountService->deleteBankAccount($bankAccount);
            
            return redirect()->route('tenant.admin.bank-accounts.index', ['store' => $store->slug, 'paymentMethod' => $paymentMethod->id])
                ->with('success', 'Cuenta bancaria eliminada exitosamente.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al eliminar la cuenta bancaria: ' . $e->getMessage());
        }
    }
    
    /**
     * Toggle the active status of the specified bank account.
     * 
     * @param Request $request
     * @param string $store
     * @param PaymentMethod $paymentMethod
     * @param BankAccount $bankAccount
     * @return \Illuminate\Http\RedirectResponse
     */
    public function toggleActive(Request $request, $store, PaymentMethod $paymentMethod, BankAccount $bankAccount)
    {
        // Get current store from view shared data
        $store = view()->shared('currentStore');
        
        // Ensure payment method belongs to current store
        if ($paymentMethod->store_id !== $store->id) {
            abort(404);
        }
        
        // Ensure bank account belongs to this payment method
        if ($bankAccount->payment_method_id !== $paymentMethod->id) {
            abort(404);
        }
        
        // Ensure bank account belongs to current store
        if ($bankAccount->store_id !== $store->id) {
            abort(404);
        }
        
        try {
            // If trying to activate, validate against plan limits (Requirements 2.1, 2.2, 2.3, 2.4)
            if (!$bankAccount->is_active) {
                $validationResult = $this->validatePlanLimits($store, true);
                if ($validationResult) {
                    return redirect()->route('tenant.admin.bank-accounts.index', ['store' => $store->slug, 'paymentMethod' => $paymentMethod->id])
                        ->with('error', $validationResult['error']);
                }
            }
            
            // Toggle active status
            $this->bankAccountService->toggleActive($bankAccount, $store);
            
            // Add information about plan limits if account was activated
            $status = $bankAccount->is_active ? 'activada' : 'desactivada';
            $successMessage = "Cuenta bancaria {$status} exitosamente.";
            
            if ($bankAccount->is_active) {
                $remainingSlots = $this->bankAccountService->getRemainingBankAccountSlots($store);
                if ($remainingSlots === 0) {
                    $maxAccounts = $this->bankAccountService->getBankAccountLimit($store);
                    $planName = ucfirst(strtolower($store->plan->name ?? 'Explorer'));
                    $successMessage .= " Has alcanzado el límite de {$maxAccounts} cuentas bancarias activas para tu plan {$planName}.";
                } else if ($remainingSlots === 1) {
                    $successMessage .= " Te queda 1 espacio disponible para cuentas bancarias en tu plan actual.";
                }
            }
            
            return redirect()->route('tenant.admin.bank-accounts.index', ['store' => $store->slug, 'paymentMethod' => $paymentMethod->id])
                ->with('success', $successMessage);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al cambiar el estado de la cuenta bancaria: ' . $e->getMessage());
        }
    }
    
    /**
     * Handle plan downgrade for the store.
     * This method is called when a store downgrades their plan.
     * 
     * @param Request $request
     * @param string $store
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handlePlanDowngrade(Request $request, $store)
    {
        // Get current store from view shared data
        $store = view()->shared('currentStore');
        
        try {
            // Handle plan downgrade
            $deactivatedCount = $this->bankAccountService->handlePlanDowngrade($store);
            
            if ($deactivatedCount > 0) {
                $message = "Se han desactivado {$deactivatedCount} cuentas bancarias debido a la reducción de tu plan. ";
                $maxAccounts = $this->bankAccountService->getBankAccountLimit($store);
                $message .= "Tu plan actual permite un máximo de {$maxAccounts} cuentas bancarias activas.";
                
                return redirect()->back()->with('warning', $message);
            }
            
            return redirect()->back()->with('info', 'No se requirieron cambios en tus cuentas bancarias.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al ajustar las cuentas bancarias: ' . $e->getMessage());
        }
    }
}