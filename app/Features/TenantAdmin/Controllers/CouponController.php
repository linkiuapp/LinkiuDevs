<?php

namespace App\Features\TenantAdmin\Controllers;

use App\Http\Controllers\Controller;
use App\Features\TenantAdmin\Models\Coupon;
use App\Features\TenantAdmin\Models\Category;
use App\Features\TenantAdmin\Models\Product;
use App\Shared\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CouponController extends Controller
{
    /**
     * Display a listing of the coupons.
     */
    public function index(Request $request)
    {
        // Get current store from view shared data (set by middleware)
        $store = view()->shared('currentStore');
        
        // Get coupons with filtering
        $query = $store->activeCoupons();
        
        // Filter by status
        if ($request->has('status')) {
            $status = $request->get('status');
            if ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            } elseif ($status === 'expired') {
                $query->where('end_date', '<', now());
            } elseif ($status === 'upcoming') {
                $query->where('start_date', '>', now());
            }
        }
        
        // Filter by type
        if ($request->has('type') && $request->get('type') !== '') {
            $query->where('type', $request->get('type'));
        }
        
        // Filter by discount type
        if ($request->has('discount_type') && $request->get('discount_type') !== '') {
            $query->where('discount_type', $request->get('discount_type'));
        }
        
        // Search by name or code
        if ($search = $request->get('search')) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        // Order by created_at desc
        $query->orderBy('created_at', 'desc');
        
        // Get paginated results
        $coupons = $query->paginate(10)->withQueryString();
        
        // Calculate plan limits
        $maxCoupons = $store->plan->max_active_coupons ?? 5;
        $currentCount = $store->activeCoupons()->count();
        $remainingSlots = max(0, $maxCoupons - $currentCount);
        
        // Add status info to each coupon
        foreach ($coupons as $coupon) {
            $coupon->status_info = $this->getCouponStatusInfo($coupon);
        }
        
        return view('tenant-admin::coupons.index', compact(
            'coupons',
            'store',
            'maxCoupons',
            'currentCount',
            'remainingSlots'
        ));
    }
    
    /**
     * Show the form for creating a new coupon.
     */
    public function create()
    {
        // Get current store from view shared data
        $store = view()->shared('currentStore');
        
        // Check if store can create more coupons
        $maxCoupons = $store->plan->max_active_coupons ?? 5;
        $currentCount = $store->activeCoupons()->count();
        
        if ($currentCount >= $maxCoupons) {
            return redirect()->route('tenant.admin.coupons.index', ['store' => $store->slug])
                ->with('error', "Has alcanzado el límite de cupones activos ({$maxCoupons}) para tu plan {$store->plan->name}. Actualiza tu plan para crear más cupones.");
        }
        
        // Get categories and products for selection
        $categories = $store->categories()->active()->orderBy('name')->get();
        $products = $store->products()->active()->orderBy('name')->get();
        
        return view('tenant-admin::coupons.create', compact('store', 'categories', 'products', 'maxCoupons', 'currentCount'));
    }
    
    /**
     * Store a newly created coupon in storage.
     */
    public function store(Request $request)
    {
        $store = view()->shared('currentStore');
        
        // Check limits
        $maxCoupons = $store->plan->max_active_coupons ?? 5;
        $currentCount = $store->activeCoupons()->count();
        
        if ($currentCount >= $maxCoupons) {
            return redirect()->route('tenant.admin.coupons.index', ['store' => $store->slug])
                ->with('error', "Has alcanzado el límite de cupones activos para tu plan.");
        }
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'code' => [
                'nullable',
                'string',
                'max:20',
                'alpha_dash',
                Rule::unique('coupons')->where(function ($query) use ($store) {
                    return $query->where('store_id', $store->id);
                })
            ],
            'type' => 'required|in:' . implode(',', array_keys(Coupon::TYPES)),
            'discount_type' => 'required|in:' . implode(',', array_keys(Coupon::DISCOUNT_TYPES)),
            'discount_value' => 'required|numeric|min:0.01',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'min_purchase_amount' => 'nullable|numeric|min:0',
            'max_uses' => 'nullable|integer|min:1',
            'uses_per_session' => 'nullable|integer|min:1',
            'start_date' => 'nullable|date|after_or_equal:today',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'days_of_week' => 'nullable|array',
            'days_of_week.*' => 'integer|between:0,6',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
            'is_active' => 'boolean',
            'is_public' => 'boolean',
            'is_automatic' => 'boolean',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
            'products' => 'nullable|array',
            'products.*' => 'exists:products,id',
        ]);
        
        // Custom validation for percentage discount
        if ($request->discount_type === 'percentage' && $request->discount_value > 100) {
            $validator->errors()->add('discount_value', 'El descuento porcentual no puede ser mayor al 100%.');
        }
        
        // Validate category/product selection based on type
        if ($request->type === 'categories' && empty($request->categories)) {
            $validator->errors()->add('categories', 'Debes seleccionar al menos una categoría.');
        }
        
        if ($request->type === 'products' && empty($request->products)) {
            $validator->errors()->add('products', 'Debes seleccionar al menos un producto.');
        }
        
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        
        // Create coupon
        $couponData = $request->only([
            'name', 'description', 'code', 'type', 'discount_type', 'discount_value',
            'max_discount_amount', 'min_purchase_amount', 'max_uses', 'uses_per_session',
            'start_date', 'end_date', 'days_of_week', 'start_time', 'end_time',
            'is_active', 'is_public', 'is_automatic'
        ]);
        
        $couponData['store_id'] = $store->id;
        $couponData['current_uses'] = 0;
        
        // Convert boolean fields
        $couponData['is_active'] = $request->boolean('is_active');
        $couponData['is_public'] = $request->boolean('is_public');
        $couponData['is_automatic'] = $request->boolean('is_automatic');
        
        $coupon = Coupon::create($couponData);
        
        // Attach relationships
        if ($request->type === 'categories' && $request->categories) {
            $coupon->categories()->attach($request->categories);
        }
        
        if ($request->type === 'products' && $request->products) {
            $coupon->products()->attach($request->products);
        }
        
        return redirect()
            ->route('tenant.admin.coupons.index', $store->slug)
            ->with('success', 'Cupón creado exitosamente');
    }
    
    /**
     * Display the specified coupon.
     */
    public function show(Request $request, $store, $coupon)
    {
        $store = view()->shared('currentStore');
        
        // Buscar el cupón manualmente para mayor seguridad
        $coupon = Coupon::where('id', $coupon)
            ->where('store_id', $store->id)
            ->firstOrFail();
        
        // Load relationships
        $coupon->load(['categories', 'products', 'usageLogs.order']);
        
        // Get usage statistics
        $stats = [
            'total_uses' => $coupon->current_uses,
            'remaining_uses' => $coupon->max_uses ? max(0, $coupon->max_uses - $coupon->current_uses) : '∞',
            'usage_percentage' => $coupon->max_uses ? round(($coupon->current_uses / $coupon->max_uses) * 100, 1) : 0,
            'total_discount_given' => $coupon->usageLogs()->sum('discount_applied'),
            'average_discount' => $coupon->current_uses > 0 ? $coupon->usageLogs()->avg('discount_applied') : 0,
            'orders_count' => $coupon->usageLogs()->distinct('order_id')->count(),
        ];
        
        // Get recent usage
        $recentUsage = $coupon->usageLogs()
            ->with('order')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        return view('tenant-admin::coupons.show', compact('coupon', 'store', 'stats', 'recentUsage'));
    }
    
    /**
     * Show the form for editing the specified coupon.
     */
    public function edit(Request $request, $store, $coupon)
    {
        $store = view()->shared('currentStore');
        
        // Buscar el cupón manualmente para mayor seguridad
        $coupon = Coupon::where('id', $coupon)
            ->where('store_id', $store->id)
            ->firstOrFail();
        
        // Load relationships
        $coupon->load(['categories', 'products']);
        
        // Get categories and products for selection
        $categories = $store->categories()->active()->orderBy('name')->get();
        $products = $store->products()->active()->orderBy('name')->get();
        
        return view('tenant-admin::coupons.edit', compact('coupon', 'store', 'categories', 'products'));
    }
    
    /**
     * Update the specified coupon in storage.
     */
    public function update(Request $request, $store, $coupon)
    {
        $store = view()->shared('currentStore');
        
        // Buscar el cupón manualmente para mayor seguridad
        $coupon = Coupon::where('id', $coupon)
            ->where('store_id', $store->id)
            ->firstOrFail();
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'code' => [
                'nullable',
                'string',
                'max:20',
                'alpha_dash',
                Rule::unique('coupons')->where(function ($query) use ($store) {
                    return $query->where('store_id', $store->id);
                })->ignore($coupon->id)
            ],
            'type' => 'required|in:' . implode(',', array_keys(Coupon::TYPES)),
            'discount_type' => 'required|in:' . implode(',', array_keys(Coupon::DISCOUNT_TYPES)),
            'discount_value' => 'required|numeric|min:0.01',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'min_purchase_amount' => 'nullable|numeric|min:0',
            'max_uses' => 'nullable|integer|min:1',
            'uses_per_session' => 'nullable|integer|min:1',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'days_of_week' => 'nullable|array',
            'days_of_week.*' => 'integer|between:0,6',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
            'is_active' => 'boolean',
            'is_public' => 'boolean',
            'is_automatic' => 'boolean',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
            'products' => 'nullable|array',
            'products.*' => 'exists:products,id',
        ]);
        
        // Custom validation for percentage discount
        if ($request->discount_type === 'percentage' && $request->discount_value > 100) {
            $validator->errors()->add('discount_value', 'El descuento porcentual no puede ser mayor al 100%.');
        }
        
        // Validate category/product selection based on type
        if ($request->type === 'categories' && empty($request->categories)) {
            $validator->errors()->add('categories', 'Debes seleccionar al menos una categoría.');
        }
        
        if ($request->type === 'products' && empty($request->products)) {
            $validator->errors()->add('products', 'Debes seleccionar al menos un producto.');
        }
        
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        
        // Update coupon
        $couponData = $request->only([
            'name', 'description', 'code', 'type', 'discount_type', 'discount_value',
            'max_discount_amount', 'min_purchase_amount', 'max_uses', 'uses_per_session',
            'start_date', 'end_date', 'days_of_week', 'start_time', 'end_time',
            'is_active', 'is_public', 'is_automatic'
        ]);
        
        // Convert boolean fields
        $couponData['is_active'] = $request->boolean('is_active');
        $couponData['is_public'] = $request->boolean('is_public');
        $couponData['is_automatic'] = $request->boolean('is_automatic');
        
        $coupon->update($couponData);
        
        // Update relationships
        if ($request->type === 'categories') {
            $coupon->categories()->sync($request->categories ?? []);
            $coupon->products()->detach(); // Remove product relationships
        } elseif ($request->type === 'products') {
            $coupon->products()->sync($request->products ?? []);
            $coupon->categories()->detach(); // Remove category relationships
        } else {
            // Global type - remove all relationships
            $coupon->categories()->detach();
            $coupon->products()->detach();
        }
        
        return redirect()
            ->route('tenant.admin.coupons.index', $store->slug)
            ->with('success', 'Cupón actualizado exitosamente');
    }
    
    /**
     * Remove the specified coupon from storage.
     */
    public function destroy(Request $request, Coupon $coupon)
    {
        $store = view()->shared('currentStore');
        
        // Ensure coupon belongs to current store
        if ($coupon->store_id !== $store->id) {
            abort(404);
        }
        
        // Check if coupon has been used
        if ($coupon->current_uses > 0) {
            return back()->with('error', 'No se puede eliminar un cupón que ya ha sido utilizado. Puedes desactivarlo en su lugar.');
        }
        
        $coupon->delete();
        
        return redirect()
            ->route('tenant.admin.coupons.index', $store->slug)
            ->with('success', 'Cupón eliminado exitosamente');
    }
    
    /**
     * Toggle coupon status.
     */
    public function toggleStatus(Request $request, Coupon $coupon)
    {
        $store = view()->shared('currentStore');
        
        // Ensure coupon belongs to current store
        if ($coupon->store_id !== $store->id) {
            abort(404);
        }
        
        $coupon->update(['is_active' => !$coupon->is_active]);
        
        $status = $coupon->is_active ? 'activado' : 'desactivado';
        
        return back()->with('success', "Cupón {$status} exitosamente");
    }
    
    /**
     * Duplicate the specified coupon.
     */
    public function duplicate(Request $request, $store, $coupon)
    {
        $store = view()->shared('currentStore');
        
        // Buscar el cupón manualmente para mayor seguridad
        $coupon = Coupon::where('id', $coupon)
            ->where('store_id', $store->id)
            ->firstOrFail();
        
        // Check if store can create more coupons
        $maxCoupons = $store->plan->max_active_coupons ?? 5;
        $currentCount = $store->activeCoupons()->count();
        
        if ($currentCount >= $maxCoupons) {
            return back()->with('error', "Has alcanzado el límite de cupones activos para tu plan.");
        }
        
        $newCoupon = $coupon->duplicate();
        
        return redirect()
            ->route('tenant.admin.coupons.edit', ['store' => $store->slug, 'coupon' => $newCoupon])
            ->with('success', 'Cupón duplicado exitosamente. Puedes editarlo antes de activarlo.');
    }
    
    /**
     * Get coupon status information.
     */
    private function getCouponStatusInfo(Coupon $coupon): array
    {
        $now = now();
        
        if (!$coupon->is_active) {
            return [
                'status' => 'inactive',
                'text' => 'Inactivo',
                'color' => 'text-black-300',
                'bg' => 'bg-black-50',
                'border' => 'border-black-200'
            ];
        }
        
        if ($coupon->start_date && $coupon->start_date > $now) {
            return [
                'status' => 'upcoming',
                'text' => 'Próximo',
                'color' => 'text-info-300',
                'bg' => 'bg-info-50',
                'border' => 'border-info-200'
            ];
        }
        
        if ($coupon->end_date && $coupon->end_date < $now) {
            return [
                'status' => 'expired',
                'text' => 'Expirado',
                'color' => 'text-error-300',
                'bg' => 'bg-error-50',
                'border' => 'border-error-200'
            ];
        }
        
        if ($coupon->max_uses && $coupon->current_uses >= $coupon->max_uses) {
            return [
                'status' => 'exhausted',
                'text' => 'Agotado',
                'color' => 'text-warning-300',
                'bg' => 'bg-warning-50',
                'border' => 'border-warning-200'
            ];
        }
        
        return [
            'status' => 'active',
            'text' => 'Activo',
            'color' => 'text-success-300',
            'bg' => 'bg-success-50',
            'border' => 'border-success-200'
        ];
    }
} 