<?php

namespace App\Features\TenantAdmin\Controllers;

use App\Features\TenantAdmin\Models\StoreDesign;
use App\Features\TenantAdmin\Models\StoreDesignHistory;
use App\Features\TenantAdmin\Services\StoreDesignImageService;
use App\Http\Controllers\Controller;
use App\Shared\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class StoreDesignController extends Controller
{
    public function __construct(
        protected StoreDesignImageService $imageService
    ) {}

    /**
     * Muestra la interfaz principal de diseño de la tienda
     */
    public function index(Request $request)
    {
        $store = $request->route('store');
        
        // Verificar que store es una instancia de Store
        if (!($store instanceof Store)) {
            abort(404, 'Tienda no encontrada');
        }

        $design = StoreDesign::firstOrCreate(
            ['store_id' => $store->id],
            [
                'header_background_color' => '#FFFFFF',
                'header_text_color' => '#000000',
                'header_description_color' => '#666666',
                'is_published' => false
            ]
        );

        $history = StoreDesignHistory::where('store_design_id', $design->id)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Pasar los datos iniciales del diseño como array para Alpine.js
        $initialDesign = [
            'bgColor' => $design->header_background_color ?? '#FFFFFF',
            'textColor' => $design->header_text_color ?? '#000000', 
            'descriptionColor' => $design->header_description_color ?? '#666666',
            'logo' => $design->logo_url,
            'logo_webp' => $design->logo_webp_url,
            'favicon' => $design->favicon_url
        ];

        return view('tenant-admin::store-design.index', [
            'store' => $store,
            'design' => $design,
            'history' => $history,
            'initialDesign' => $initialDesign
        ]);
    }

    /**
     * Actualiza el diseño de la tienda
     */
    /*public function update(Request $request)
    {
        try {
            $store = $request->route('store');
            
            $validated = $request->validate([
                'header_background_color' => 'required|string|regex:/^#[A-Fa-f0-9]{6}$/',
                'header_text_color' => 'required|string|regex:/^#[A-Fa-f0-9]{6}$/',
                'header_description_color' => 'required|string|regex:/^#[A-Fa-f0-9]{6}$/',
            ]);

            DB::beginTransaction();
            try {
                $design = StoreDesign::where('store_id', $store->id)->firstOrFail();
                
                // Guardar diseño actual en historial antes de actualizar
                if ($user = auth()->user()) {
                    StoreDesignHistory::create([
                        'store_design_id' => $design->id,
                        'data' => json_encode($design->toArray()),
                        'created_by' => $user->id,
                        'note' => 'Actualización manual del diseño'
                    ]);
                }

                // Actualizar diseño
                $design->update($validated);
                
                DB::commit();

                return response()->json([
                    'message' => 'Diseño actualizado correctamente',
                    'design' => $design
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error al actualizar diseño de tienda', [
                'store_id' => $store->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al actualizar el diseño'
            ], 500);
        }
    }*/

public function update(Request $request)
{
    try {
        $store = $request->route('store');
        
        Log::info('Store Design Update Request:', [
            'has_logo_base64' => $request->has('logo_base64'),
            'logo_base64_length' => $request->has('logo_base64') ? strlen($request->logo_base64) : 0,
            'logo_base64_preview' => $request->has('logo_base64') ? substr($request->logo_base64, 0, 50) . '...' : null,
            'has_favicon_base64' => $request->has('favicon_base64'),
            'all_keys' => array_keys($request->all()),
            'validated_data' => $validated ?? []
        ]);
        
        $validated = $request->validate([
            'header_background_color' => 'required|string|regex:/^#[A-Fa-f0-9]{6}$/',
            'header_text_color' => 'required|string|regex:/^#[A-Fa-f0-9]{6}$/',
            'header_description_color' => 'required|string|regex:/^#[A-Fa-f0-9]{6}$/',
            'logo_base64' => 'nullable|string',
            'favicon_base64' => 'nullable|string',
            'logo_url' => 'nullable|string',
            'favicon_url' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $design = StoreDesign::where('store_id', $store->id)->firstOrFail();
            
            if ($user = auth()->user()) {
                StoreDesignHistory::create([
                    'store_design_id' => $design->id,
                    'data' => json_encode($design->toArray()),
                    'created_by' => $user->id,
                    'note' => 'Actualización manual del diseño'
                ]);
            }

            // Manejar logo base64
            if ($request->has('logo_base64') && $request->logo_base64) {
                Log::info('Processing logo base64');
                $logoUrls = $this->handleBase64Image($request->logo_base64, 'logo', $store->id);
                Log::info('Logo URLs generated:', $logoUrls);
                $validated = array_merge($validated, $logoUrls);
            } elseif ($request->input('logo_url') === '') {
                Log::info('Removing logo');
                $this->imageService->cleanOldImages($store->id, 'logo');
                $validated['logo_url'] = null;
                $validated['logo_webp_url'] = null;
            }

            // Manejar favicon base64
            if ($request->has('favicon_base64') && $request->favicon_base64) {
                $faviconUrl = $this->handleBase64Image($request->favicon_base64, 'favicon', $store->id);
                $validated = array_merge($validated, $faviconUrl);
            } elseif ($request->input('favicon_url') === '') {
                $this->imageService->cleanOldImages($store->id, 'favicon');
                $validated['favicon_url'] = null;
            }

            $design->update($validated);
            $design->refresh(); // Recargar el modelo con los datos actualizados
            DB::commit();
            
            Log::info('Design after update:', $design->toArray());

            return response()->json([
                'message' => 'Diseño actualizado correctamente',
                'design' => $design
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    } catch (ValidationException $e) {
        return response()->json([
            'message' => 'Error de validación',
            'errors' => $e->errors()
        ], 422);
    } catch (\Exception $e) {
        Log::error('Error al actualizar diseño de tienda', [
            'store_id' => $store->id,
            'error' => $e->getMessage()
        ]);
        return response()->json([
            'message' => 'Error al actualizar el diseño'
        ], 500);
    }
}

    /**
     * Manejar imagen base64 (usado para logos y favicons)
     */
    private function handleBase64Image(string $base64, string $type, int $storeId): array
    {
        try {
            // Extraer el tipo de imagen y los datos
            if (preg_match('/^data:image\/(\w+);base64,/', $base64, $matches)) {
                $extension = $matches[1];
                $base64 = substr($base64, strpos($base64, ',') + 1);
            } else {
                throw new \Exception('Formato de imagen base64 inválido');
            }

            // Decodificar base64
            $imageData = base64_decode($base64);
            if ($imageData === false) {
                throw new \Exception('No se pudo decodificar la imagen base64');
            }

            // Limpiar imágenes antiguas
            $this->imageService->cleanOldImages($storeId, $type);

            // Generar nombre de archivo
            $filename = $type . '_' . time() . '.' . $extension;
            $relativePath = 'store-design/' . $storeId . '/' . $filename;
            
            // ✅ Crear directorio si no existe
            $destinationPath = public_path('storage/store-design/' . $storeId);
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }
            
            // ✅ GUARDAR usando método estándar
            $fullPath = public_path('storage/' . $relativePath);
            file_put_contents($fullPath, $imageData);

            // ✅ Retornar URLs usando método estándar
            if ($type === 'logo') {
                return [
                    'logo_url' => asset('storage/' . $relativePath),
                    'logo_webp_url' => null
                ];
            } else {
                return [
                    'favicon_url' => asset('storage/' . $relativePath)
                ];
            }
        } catch (\Exception $e) {
            Log::error('Error handling base64 image:', [
                'error' => $e->getMessage(),
                'type' => $type,
                'store_id' => $storeId
            ]);
            throw $e;
        }
    }

    /**
     * Maneja la subida del logo de la tienda
     */
    public function uploadLogo(Request $request)
    {
        try {
            $store = $request->route('store');
            
            $validator = Validator::make($request->all(), [
                'logo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Archivo inválido',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            try {
                $design = StoreDesign::where('store_id', $store->id)->firstOrFail();
                
                // Limpiar imágenes antiguas
                $this->imageService->cleanOldImages($store->id, 'logo');
                
                // Procesar y guardar nuevo logo
                $logoUrls = $this->imageService->handleLogo($request->file('logo'), $store->id);
                
                // Actualizar diseño
                $design->update($logoUrls);
                
                DB::commit();

                return response()->json([
                    'message' => 'Logo actualizado correctamente',
                    'urls' => $logoUrls
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Error al subir logo', [
                'store_id' => $store->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al subir el logo'
            ], 500);
        }
    }

    /**
     * Maneja la subida del favicon de la tienda
     */
    public function uploadFavicon(Request $request)
    {
        try {
            $store = $request->route('store');
            
            $validator = Validator::make($request->all(), [
                'favicon' => 'required|image|mimes:jpeg,png,jpg,gif|max:1024'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Archivo inválido',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            try {
                $design = StoreDesign::where('store_id', $store->id)->firstOrFail();
                
                // Limpiar favicon antiguo
                $this->imageService->cleanOldImages($store->id, 'favicon');
                
                // Procesar y guardar nuevo favicon
                $faviconUrl = $this->imageService->handleFavicon($request->file('favicon'), $store->id);
                
                // Actualizar diseño
                $design->update($faviconUrl);
                
                DB::commit();

                return response()->json([
                    'message' => 'Favicon actualizado correctamente',
                    'url' => $faviconUrl
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Error al subir favicon', [
                'store_id' => $store->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al subir el favicon'
            ], 500);
        }
    }

    /**
     * Publica los cambios de diseño actuales
     */
    public function publish(Request $request)
    {
        try {
            $store = $request->route('store');
            $design = StoreDesign::where('store_id', $store->id)->firstOrFail();
            
            // Validar datos
            $validated = $request->validate([
                'header_background_color' => 'nullable|string|regex:/^#[A-Fa-f0-9]{6}$/',
                'header_text_color' => 'nullable|string|regex:/^#[A-Fa-f0-9]{6}$/',
                'header_description_color' => 'nullable|string|regex:/^#[A-Fa-f0-9]{6}$/',
                'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'favicon' => 'nullable|image|mimes:jpeg,png,jpg,gif,ico|max:1024',
                'store_name' => 'nullable|string|max:40|regex:/^[a-zA-Z0-9\s\-áéíóúñÁÉÍÓÚÑüÜ\.]*$/',
                'store_description' => 'nullable|string|max:50|regex:/^[a-zA-Z0-9\s\-áéíóúñÁÉÍÓÚÑüÜ\.,¿?!:]*$/',
            ], [
                'store_name.regex' => 'El nombre solo puede contener letras, números, espacios, guiones, acentos y puntos.',
                'store_name.max' => 'El nombre no puede superar los 40 caracteres.',
                'store_description.regex' => 'La descripción contiene caracteres no permitidos.',
                'store_description.max' => 'La descripción no puede superar los 50 caracteres.',
            ]);

            DB::beginTransaction();
            try {
                // Guardar historial
                if ($user = auth()->user()) {
                    StoreDesignHistory::create([
                        'store_design_id' => $design->id,
                        'data' => json_encode($design->toArray()),
                        'created_by' => $user->id,
                        'note' => 'Publicación del diseño'
                    ]);
                }

                $updateData = [];

                // Actualizar colores si se proporcionan
                if ($request->has('header_background_color')) {
                    $updateData['header_background_color'] = $validated['header_background_color'];
                }
                if ($request->has('header_text_color')) {
                    $updateData['header_text_color'] = $validated['header_text_color'];
                }
                if ($request->has('header_description_color')) {
                    $updateData['header_description_color'] = $validated['header_description_color'];
                }

                // Manejar logo
                if ($request->hasFile('logo')) {
                    // Limpiar logo anterior
                    $this->imageService->cleanOldImages($store->id, 'logo');
                    
                    // Subir nuevo logo
                    $logoUrls = $this->imageService->handleLogo($request->file('logo'), $store->id);
                    $updateData = array_merge($updateData, $logoUrls);
                }

                // Manejar favicon
                if ($request->hasFile('favicon')) {
                    // Limpiar favicon anterior
                    $this->imageService->cleanOldImages($store->id, 'favicon');
                    
                    // Subir nuevo favicon
                    $faviconUrl = $this->imageService->handleFavicon($request->file('favicon'), $store->id);
                    $updateData = array_merge($updateData, $faviconUrl);
                }

                // Marcar como publicado
                $updateData['is_published'] = true;

                // Actualizar diseño
                $design->update($updateData);

                // Actualizar datos de la tienda si se proporcionan
                $storeUpdateData = [];
                if ($request->filled('store_name')) {
                    // Evitar re-guardar mismo nombre para no invalidar cache
                    if ($store->name !== $validated['store_name']) {
                        $storeUpdateData['name'] = $validated['store_name'];
                    }
                }
                if ($request->has('store_description')) {
                    // Permitimos vacío para limpiar descripción
                    $storeUpdateData['description'] = $validated['store_description'] ?? null;
                }

                if (!empty($storeUpdateData)) {
                    $store->update($storeUpdateData);
                    Log::info('Store info updated', [
                        'store_id' => $store->id,
                        'updated_fields' => array_keys($storeUpdateData),
                        'new_values' => $storeUpdateData
                    ]);
                }
                
                DB::commit();

                return response()->json([
                    'message' => 'Diseño publicado correctamente',
                    'design' => $design->fresh(),
                    'store' => [
                        'name' => $store->fresh()->name,
                        'description' => $store->fresh()->description
                    ]
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Error al publicar diseño', [
                'store_id' => $store->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Error al publicar el diseño: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Revierte los cambios a una versión anterior
     */
    public function revert(Request $request)
    {
        try {
            $validated = $request->validate([
                'history_id' => 'required|exists:store_design_histories,id'
            ]);

            $store = $request->route('store');
            $design = StoreDesign::where('store_id', $store->id)->firstOrFail();
            
            // Obtener versión del historial
            $history = StoreDesignHistory::findOrFail($validated['history_id']);
            $historicalData = json_decode($history->data, true);

            // Guardar diseño actual en historial
            StoreDesignHistory::create([
                'store_design_id' => $design->id,
                'data' => json_encode($design->toArray()),
                'created_by' => auth()->id(),
                'note' => 'Respaldo automático antes de revertir'
            ]);

            // Revertir a versión anterior
            $design->update($historicalData);

            return response()->json([
                'message' => 'Diseño revertido correctamente',
                'design' => $design
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error al revertir diseño', [
                'store_id' => $store->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al revertir el diseño'
            ], 500);
        }
    }
} 