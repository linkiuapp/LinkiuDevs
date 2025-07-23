<?php

namespace App\Features\TenantAdmin\Controllers;

use App\Features\TenantAdmin\Models\Product;
use App\Features\TenantAdmin\Models\ProductImage;
use App\Features\TenantAdmin\Models\Category;
use App\Features\TenantAdmin\Models\ProductVariable;
use App\Features\TenantAdmin\Services\ProductImageService;
use App\Shared\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    protected $imageService;

    public function __construct(ProductImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    /**
     * Mostrar lista de productos
     */
    public function index(Request $request): View
    {
        // Obtener la tienda actual desde el contexto compartido
        $store = view()->shared('currentStore');
        
        // Obtener límites del plan
        $plan = $store->plan;
        $currentCount = Product::byStore($store->id)->count();
        $maxProducts = $plan->max_products ?? 0;
        
        // Obtener categorías para los filtros
        $categories = Category::where('store_id', $store->id)->get();
        
        // Consulta base de productos
        $query = Product::byStore($store->id)
            ->with(['images', 'categories', 'variants'])
            ->orderBy('created_at', 'desc');

        // Aplicar filtros
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('sku', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->active();
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        if ($request->filled('type')) {
            if ($request->type === 'simple') {
                $query->simple();
            } elseif ($request->type === 'variable') {
                $query->variable();
            }
        }

        if ($request->filled('category')) {
            $query->whereHas('categories', function ($q) use ($request) {
                $q->where('categories.id', $request->category);
            });
        }

        $products = $query->paginate(20);
        
        return view('tenant-admin::products.index', compact('products', 'store', 'currentCount', 'maxProducts', 'categories'));
    }

    /**
     * Mostrar formulario de creación
     */
    public function create(Request $request): View
    {
        // Obtener la tienda actual desde el contexto compartido
        $store = view()->shared('currentStore');
        
        // Verificar límites del plan
        $plan = $store->plan;
        $currentCount = Product::byStore($store->id)->count();
        $maxProducts = $plan->max_products ?? 0;

        if ($currentCount >= $maxProducts) {
            return redirect()->route('tenant.admin.products.index', $store->slug)
                ->with('error', 'Has alcanzado el límite de productos para tu plan actual.');
        }

        $categories = Category::where('store_id', $store->id)->get();
        $variables = ProductVariable::where('store_id', $store->id)->get();

        return view('tenant-admin::products.create', compact(
            'store',
            'categories',
            'variables',
            'currentCount',
            'maxProducts'
        ));
    }

    /**
     * Guardar nuevo producto
     */
    public function store(Request $request): RedirectResponse
    {
        // Obtener la tienda actual desde el contexto compartido
        $store = view()->shared('currentStore');
        
        // Validación básica
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'sku' => 'nullable|string|max:100',
            'is_active' => 'boolean',
        ]);

        // Crear producto
        $product = Product::create([
            'store_id' => $store->id,
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'sku' => $request->sku,
            'is_active' => $request->boolean('is_active', true),
            'type' => $request->type ?? 'simple',
        ]);

        // Procesar imágenes si se subieron
        if ($request->hasFile('images')) {
            $images = $request->file('images');
            $this->imageService->processImages($product, $images);
        }

        // Asignar categorías si se seleccionaron
        if ($request->has('categories')) {
            $product->categories()->sync($request->categories);
        }

        return redirect()->route('tenant.admin.products.index', $store->slug)
            ->with('success', 'Producto creado exitosamente.');
    }

    /**
     * Mostrar producto específico
     */
    public function show(Request $request, $store, $product): View
    {
        // Obtener la tienda actual desde el contexto compartido
        $store = view()->shared('currentStore');
        
        // Buscar el producto manualmente
        $product = Product::where('id', $product)
            ->where('store_id', $store->id)
            ->firstOrFail();
        
        $product->load(['images', 'categories', 'variants']);
        
        return view('tenant-admin::products.show', compact('product', 'store'));
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit(Request $request, $store, $product): View
    {
        // Obtener la tienda actual desde el contexto compartido
        $store = view()->shared('currentStore');
        
        // Buscar el producto manualmente
        $product = Product::where('id', $product)
            ->where('store_id', $store->id)
            ->firstOrFail();
        
        $product->load(['images', 'categories', 'variants']);
        $categories = Category::where('store_id', $store->id)->get();
        $variables = ProductVariable::where('store_id', $store->id)->get();

        return view('tenant-admin::products.edit', compact(
            'product',
            'store',
            'categories',
            'variables'
        ));
    }

    /**
     * Actualizar producto
     */
    public function update(Request $request, $store, $product): RedirectResponse
    {
        // Obtener la tienda actual desde el contexto compartido
        $store = view()->shared('currentStore');
        
        // Buscar el producto manualmente
        $product = Product::where('id', $product)
            ->where('store_id', $store->id)
            ->firstOrFail();
        
        // Validación básica
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'sku' => 'nullable|string|max:100',
            'is_active' => 'boolean',
        ]);

        // Actualizar producto
        $product->update([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'sku' => $request->sku,
            'is_active' => $request->boolean('is_active', true),
        ]);

        // Eliminar imágenes marcadas para eliminación
        if ($request->has('delete_images')) {
            $imageIds = $request->delete_images;
            foreach ($imageIds as $imageId) {
                // Validar que el ID no sea null o vacío
                if (!empty($imageId) && is_numeric($imageId)) {
                    $image = ProductImage::where('id', $imageId)
                        ->where('product_id', $product->id)
                        ->first();
                    if ($image) {
                        $this->imageService->deleteImage($image);
                    }
                }
            }
        }

        // Procesar nuevas imágenes si se subieron
        if ($request->hasFile('images')) {
            $images = $request->file('images');
            $this->imageService->processImages($product, $images);
        }

        // Actualizar categorías si se seleccionaron
        if ($request->has('categories')) {
            $product->categories()->sync($request->categories);
        }

        return redirect()->route('tenant.admin.products.index', $store->slug)
            ->with('success', 'Producto actualizado exitosamente.');
    }

    /**
     * Eliminar producto
     */
    public function destroy(Request $request, $store, $product): JsonResponse
    {
        // Obtener la tienda actual desde el contexto compartido
        $store = view()->shared('currentStore');
        
        // Buscar el producto manualmente
        $product = Product::where('id', $product)
            ->where('store_id', $store->id)
            ->firstOrFail();
        
        try {
            $product->delete();

            return response()->json([
                'success' => true,
                'message' => 'Producto eliminado exitosamente.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al eliminar el producto: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Duplicar producto
     */
    public function duplicate(Request $request, $store, $product): JsonResponse
    {
        // Obtener la tienda actual desde el contexto compartido
        $store = view()->shared('currentStore');
        
        // Buscar el producto manualmente
        $product = Product::where('id', $product)
            ->where('store_id', $store->id)
            ->firstOrFail();
        
        // Validar el nombre del nuevo producto
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        try {
            $newProduct = $product->replicate();
            $newProduct->name = $request->name;
            $newProduct->slug = null; // Reset slug para que se genere uno nuevo
            $newProduct->sku = null; // Reset SKU to avoid duplicates
            $newProduct->main_image_id = null; // Reset main image
            $newProduct->save();

            // Duplicar las relaciones si es necesario
            if ($product->categories()->exists()) {
                $newProduct->categories()->sync($product->categories->pluck('id'));
            }

            return response()->json([
                'success' => true,
                'message' => 'Producto duplicado exitosamente.',
                'product' => $newProduct
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al duplicar el producto: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cambiar estado del producto
     */
    public function toggleStatus(Request $request, $store, $product): JsonResponse
    {
        // Obtener la tienda actual desde el contexto compartido
        $store = view()->shared('currentStore');
        
        // Buscar el producto manualmente
        $product = Product::where('id', $product)
            ->where('store_id', $store->id)
            ->firstOrFail();
        
        try {
            $product->update(['is_active' => !$product->is_active]);

            $status = $product->is_active ? 'activado' : 'desactivado';
            
            return response()->json([
                'success' => true,
                'message' => "Producto {$status} exitosamente.",
                'is_active' => $product->is_active
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al cambiar el estado: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Establecer imagen principal del producto
     */
    public function setMainImage(Request $request, $product): RedirectResponse
    {
        // Obtener la tienda actual desde el contexto compartido
        $store = view()->shared('currentStore');
        
        // Buscar el producto manualmente
        $product = Product::where('id', $product)
            ->where('store_id', $store->id)
            ->firstOrFail();

        // Buscar la imagen
        $image = ProductImage::where('id', $request->image_id)
            ->where('product_id', $product->id)
            ->firstOrFail();

        // Establecer como imagen principal
        $this->imageService->setMainImage($product, $image);

        return redirect()->route('tenant.admin.products.edit', [$store->slug, $product->id])
            ->with('success', 'Imagen principal establecida exitosamente.');
    }
} 