<?php

namespace App\Features\TenantAdmin\Controllers;

use App\Http\Controllers\Controller;
use App\Features\TenantAdmin\Models\Slider;
use App\Features\TenantAdmin\Models\Product;
use App\Features\TenantAdmin\Models\Category;
use App\Features\TenantAdmin\Services\SliderImageService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

class SliderController extends Controller
{
    protected SliderImageService $imageService;

    public function __construct(SliderImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    /**
     * Mostrar lista de sliders
     */
    public function index(Request $request): View
    {
        $store = view()->shared('currentStore');
        
        $query = Slider::forStore($store->id)->ordered();

        // Filtros
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        if ($request->filled('scheduling')) {
            if ($request->scheduling === 'scheduled') {
                $query->where('is_scheduled', true);
            } elseif ($request->scheduling === 'permanent') {
                $query->where('is_scheduled', false);
            }
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $sliders = $query->paginate(20);
        
        // Obtener límites del plan
        $currentCount = $store->sliders()->count();
        $maxSliders = $store->plan->max_slider ?? 1;

        return view('tenant-admin::sliders.index', compact(
            'sliders',
            'store',
            'currentCount',
            'maxSliders'
        ));
    }

    /**
     * Mostrar formulario de creación
     */
    public function create(): View
    {
        $store = view()->shared('currentStore');
        
        // Verificar límite del plan
        $currentCount = $store->sliders()->count();
        $maxSliders = $store->plan->max_slider ?? 1;
        
        if ($currentCount >= $maxSliders) {
            return redirect()->route('tenant.admin.sliders.index', $store->slug)
                           ->with('error', 'Has alcanzado el límite de sliders de tu plan.');
        }

        return view('tenant-admin::sliders.create', compact('store'));
    }

    /**
     * Guardar nuevo slider
     */
    public function store(Request $request): RedirectResponse
    {
        $store = view()->shared('currentStore');
        
        // Verificar límite del plan
        $currentCount = $store->sliders()->count();
        $maxSliders = $store->plan->max_slider ?? 1;
        
        if ($currentCount >= $maxSliders) {
            return redirect()->route('tenant.admin.sliders.index', $store->slug)
                           ->with('error', 'Has alcanzado el límite de sliders de tu plan.');
        }

        $request->validate([
            'name' => 'required|string|max:255|unique:sliders,name,NULL,id,store_id,' . $store->id,
            'description' => 'nullable|string',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'url' => 'nullable|string|max:500',
            'url_type' => 'required|in:internal,external,none',
            'is_active' => 'boolean',
            'is_scheduled' => 'boolean',
            'is_permanent' => 'boolean',
            'start_date' => 'nullable|date|after_or_equal:today',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
            'scheduled_days' => 'nullable|array',
            'scheduled_days.*' => 'boolean',
            'transition_duration' => 'required|in:3,5,7',
        ]);

        // Validar URL interna si es necesaria
        if ($request->url_type === 'internal' && $request->url) {
            if (!$this->validateInternalUrl($request->url, $store->id)) {
                return redirect()->back()
                               ->withInput()
                               ->withErrors(['url' => 'La URL interna no es válida. Debe ser una categoría o producto existente.']);
            }
        }

        try {
            $slider = new Slider();
            $slider->store_id = $store->id;
            $slider->name = $request->name;
            $slider->description = $request->description;
            $slider->url = $request->url;
            $slider->url_type = $request->url_type;
            $slider->is_active = $request->boolean('is_active', true);
            $slider->is_scheduled = $request->boolean('is_scheduled', false);
            $slider->is_permanent = $request->boolean('is_permanent', false);
            $slider->start_date = $request->start_date;
            $slider->end_date = $request->end_date;
            $slider->start_time = $request->start_time;
            $slider->end_time = $request->end_time;
            $slider->scheduled_days = $request->scheduled_days;
            $slider->transition_duration = $request->transition_duration;
            
            // Procesar imagen
            $imagePath = $this->imageService->processImage($slider, $request->file('image'));
            if (!$imagePath) {
                return redirect()->back()
                               ->withInput()
                               ->withErrors(['image' => 'Error al procesar la imagen. Verifica que sea válida y tenga las dimensiones correctas (exactamente 170x100px).']);
            }
            
            $slider->image_path = $imagePath;
            $slider->save();

            return redirect()->route('tenant.admin.sliders.index', $store->slug)
                           ->with('success', 'Slider creado exitosamente.');

        } catch (\Exception $e) {
            \Log::error('Error creando slider: ' . $e->getMessage());
            return redirect()->back()
                           ->withInput()
                           ->withErrors(['general' => 'Error al crear el slider. Inténtalo de nuevo.']);
        }
    }

    /**
     * Mostrar slider específico
     */
    public function show(Request $request, $store, $slider): View
    {
        $store = view()->shared('currentStore');
        
        $slider = Slider::where('id', $slider)
                       ->where('store_id', $store->id)
                       ->firstOrFail();

        return view('tenant-admin::sliders.show', compact('slider', 'store'));
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit(Request $request, $store, $slider): View
    {
        $store = view()->shared('currentStore');
        
        $slider = Slider::where('id', $slider)
                       ->where('store_id', $store->id)
                       ->firstOrFail();

        // Obtener límites del plan
        $currentCount = $store->sliders()->count();
        $maxSliders = $store->plan->max_slider ?? 1;

        return view('tenant-admin::sliders.edit', compact('slider', 'store', 'currentCount', 'maxSliders'));
    }

    /**
     * Actualizar slider
     */
    public function update(Request $request, $store, $slider): RedirectResponse
    {
        $store = view()->shared('currentStore');
        
        $slider = Slider::where('id', $slider)
                       ->where('store_id', $store->id)
                       ->firstOrFail();

        $request->validate([
            'name' => 'required|string|max:255|unique:sliders,name,' . $slider->id . ',id,store_id,' . $store->id,
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'url' => 'nullable|string|max:500',
            'url_type' => 'required|in:internal,external,none',
            'is_active' => 'boolean',
            'is_scheduled' => 'boolean',
            'is_permanent' => 'boolean',
            'start_date' => 'nullable|date|after_or_equal:today',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
            'scheduled_days' => 'nullable|array',
            'scheduled_days.*' => 'boolean',
            'transition_duration' => 'required|in:3,5,7',
        ]);

        // Validar URL interna si es necesaria
        if ($request->url_type === 'internal' && $request->url) {
            if (!$this->validateInternalUrl($request->url, $store->id)) {
                return redirect()->back()
                               ->withInput()
                               ->withErrors(['url' => 'La URL interna no es válida. Debe ser una categoría o producto existente.']);
            }
        }

        try {
            $slider->name = $request->name;
            $slider->description = $request->description;
            $slider->url = $request->url;
            $slider->url_type = $request->url_type;
            $slider->is_active = $request->boolean('is_active', true);
            $slider->is_scheduled = $request->boolean('is_scheduled', false);
            $slider->is_permanent = $request->boolean('is_permanent', false);
            $slider->start_date = $request->start_date;
            $slider->end_date = $request->end_date;
            $slider->start_time = $request->start_time;
            $slider->end_time = $request->end_time;
            $slider->scheduled_days = $request->scheduled_days;
            $slider->transition_duration = $request->transition_duration;
            
            // Procesar nueva imagen si se subió
            if ($request->hasFile('image')) {
                $oldImagePath = $slider->image_path;
                
                $imagePath = $this->imageService->processImage($slider, $request->file('image'));
                if (!$imagePath) {
                    return redirect()->back()
                                   ->withInput()
                                   ->withErrors(['image' => 'Error al procesar la imagen. Verifica que sea válida y tenga las dimensiones correctas (exactamente 170x100px).']);
                }
                
                $slider->image_path = $imagePath;
                
                // Eliminar imagen anterior
                if ($oldImagePath) {
                    $this->imageService->deleteImage($oldImagePath);
                }
            }
            
            $slider->save();

            return redirect()->route('tenant.admin.sliders.index', $store->slug)
                           ->with('success', 'Slider actualizado exitosamente.');

        } catch (\Exception $e) {
            \Log::error('Error actualizando slider: ' . $e->getMessage());
            return redirect()->back()
                           ->withInput()
                           ->withErrors(['general' => 'Error al actualizar el slider. Inténtalo de nuevo.']);
        }
    }

    /**
     * Eliminar slider
     */
    public function destroy(Request $request, $store, $slider): JsonResponse
    {
        $store = view()->shared('currentStore');
        
        $slider = Slider::where('id', $slider)
                       ->where('store_id', $store->id)
                       ->firstOrFail();

        try {
            $slider->delete();

            return response()->json([
                'success' => true,
                'message' => 'Slider eliminado exitosamente.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al eliminar el slider: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Duplicar slider
     */
    public function duplicate(Request $request, $store, $slider): JsonResponse
    {
        $store = view()->shared('currentStore');
        
        $slider = Slider::where('id', $slider)
                       ->where('store_id', $store->id)
                       ->firstOrFail();

        // Verificar límite del plan
        $currentCount = $store->sliders()->count();
        $maxSliders = $store->plan->max_slider ?? 1;
        
        if ($currentCount >= $maxSliders) {
            return response()->json([
                'success' => false,
                'error' => 'Has alcanzado el límite de sliders de tu plan.'
            ], 400);
        }

        try {
            $newSlider = $slider->replicate();
            $newSlider->name = $slider->name . ' (Copia)';
            $newSlider->is_active = false; // Duplicar como inactivo
            
            // Duplicar imagen
            if ($slider->image_path) {
                $newImagePath = $this->imageService->duplicateImage($slider, $newSlider);
                if ($newImagePath) {
                    $newSlider->image_path = $newImagePath;
                }
            }
            
            $newSlider->save();

            return response()->json([
                'success' => true,
                'message' => 'Slider duplicado exitosamente.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al duplicar el slider: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cambiar estado del slider
     */
    public function toggleStatus(Request $request, $store, $slider): JsonResponse
    {
        $store = view()->shared('currentStore');
        
        $slider = Slider::where('id', $slider)
                       ->where('store_id', $store->id)
                       ->firstOrFail();

        try {
            $slider->update(['is_active' => !$slider->is_active]);

            $status = $slider->is_active ? 'activado' : 'desactivado';
            
            return response()->json([
                'success' => true,
                'message' => "Slider {$status} exitosamente.",
                'is_active' => $slider->is_active
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al cambiar el estado: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar orden de sliders
     */
    public function updateOrder(Request $request): JsonResponse
    {
        $store = view()->shared('currentStore');
        
        $request->validate([
            'sliders' => 'required|array',
            'sliders.*.id' => 'required|integer|exists:sliders,id',
            'sliders.*.sort_order' => 'required|integer|min:0',
        ]);

        try {
            foreach ($request->sliders as $sliderData) {
                Slider::where('id', $sliderData['id'])
                      ->where('store_id', $store->id)
                      ->update(['sort_order' => $sliderData['sort_order']]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Orden actualizado exitosamente.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al actualizar el orden: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validar URL interna
     */
    private function validateInternalUrl(string $url, int $storeId): bool
    {
        $url = ltrim($url, '/');
        $segments = explode('/', $url);

        // Validar rutas de productos: /producto/slug
        if (isset($segments[0]) && $segments[0] === 'producto' && isset($segments[1])) {
            return Product::where('slug', $segments[1])
                         ->where('store_id', $storeId)
                         ->exists();
        }

        // Validar rutas de categorías: /categoria/slug
        if (isset($segments[0]) && $segments[0] === 'categoria' && isset($segments[1])) {
            return Category::where('slug', $segments[1])
                          ->where('store_id', $storeId)
                          ->exists();
        }

        return false;
    }
} 