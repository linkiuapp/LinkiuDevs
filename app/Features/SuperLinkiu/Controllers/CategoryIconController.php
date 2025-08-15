<?php

namespace App\Features\SuperLinkiu\Controllers;

use App\Http\Controllers\Controller;
use App\Shared\Models\CategoryIcon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class CategoryIconController extends Controller
{
    /**
     * Display a listing of category icons
     */
    public function index()
    {
        $icons = CategoryIcon::orderBy('sort_order')
            ->orderBy('display_name')
            ->paginate(20);

        $totalIcons = CategoryIcon::count();
        $activeIcons = CategoryIcon::where('is_active', true)->count();

        return view('superlinkiu::category-icons.index', compact(
            'icons',
            'totalIcons', 
            'activeIcons'
        ));
    }

    /**
     * Show the form for creating a new category icon
     */
    public function create()
    {
        return view('superlinkiu::category-icons.create');
    }

    /**
     * Store a newly created category icon
     */
    public function store(Request $request)
    {


        $request->validate([
            'display_name' => ['required', 'string', 'max:255'],
            'name' => ['nullable', 'string', 'max:255', 'unique:category_icons,name'],
            'icon_file' => ['required', 'file', 'mimes:svg,png,jpg,jpeg', 'max:2048'],
            'is_active' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        try {
            // Auto-generar name si no se proporciona
            $name = $request->name ?: Str::slug($request->display_name);
            
            // Verificar unicidad del name generado
            $originalName = $name;
            $counter = 1;
            while (CategoryIcon::where('name', $name)->exists()) {
                $name = $originalName . '-' . $counter;
                $counter++;
            }

            // Inicializar variable de imagen
            $imagePath = null;

            // Manejar subida del archivo
            if ($request->hasFile('icon_file')) {
                $file = $request->file('icon_file');
                $filename = $name . '.' . $file->getClientOriginalExtension();
                
                // ✅ Guardar con método estándar ESTANDAR_IMAGENES.md
                // Crear directorio si no existe
                $destinationPath = public_path('storage/category-icons');
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }
                
                // GUARDAR con move() - Método estándar obligatorio
                $file->move($destinationPath, $filename);
                $imagePath = 'category-icons/' . $filename;
            } else {
                throw new \Exception('No se recibió el archivo de imagen');
            }

            // Determinar sort_order
            $maxSortOrder = CategoryIcon::max('sort_order') ?? 0;
            $sortOrder = $request->sort_order ?: ($maxSortOrder + 1);

            // Crear el icono
            $categoryIcon = CategoryIcon::create([
                'name' => $name,
                'display_name' => $request->display_name,
                'image_path' => $imagePath,
                'is_active' => $request->boolean('is_active', true),
                'sort_order' => $sortOrder,
            ]);

            if (!$categoryIcon) {
                throw new \Exception('Error al guardar el icono en la base de datos');
            }

            return redirect()
                ->route('superlinkiu.category-icons.index')
                ->with('success', 'Icono de categoría creado exitosamente.');

        } catch (\Exception $e) {
            \Log::error('Error creating category icon: ' . $e->getMessage());
            return back()
                ->withErrors(['general' => 'Error al crear el icono: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Show the form for editing the specified category icon
     */
    public function edit(CategoryIcon $categoryIcon)
    {
        return view('superlinkiu::category-icons.edit', compact('categoryIcon'));
    }

    /**
     * Update the specified category icon
     */
    public function update(Request $request, CategoryIcon $categoryIcon)
    {
        $request->validate([
            'display_name' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255', Rule::unique('category_icons')->ignore($categoryIcon->id)],
            'icon_file' => ['nullable', 'file', 'mimes:svg,png,jpg,jpeg', 'max:2048'],
            'is_active' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        try {
            $data = [
                'name' => $request->name,
                'display_name' => $request->display_name,
                'is_active' => $request->boolean('is_active'),
                'sort_order' => $request->sort_order ?: $categoryIcon->sort_order,
            ];

            // Manejar nuevo archivo si se sube
            if ($request->hasFile('icon_file')) {
                // ✅ Eliminar archivo anterior usando método estándar
                if ($categoryIcon->image_path) {
                    $oldFile = public_path('storage/' . $categoryIcon->image_path);
                    if (file_exists($oldFile)) {
                        unlink($oldFile);
                    }
                }

                // Subir nuevo archivo al bucket S3
                $file = $request->file('icon_file');
                $filename = $request->name . '.' . $file->getClientOriginalExtension();
                
                // ✅ Crear directorio si no existe
                $destinationPath = public_path('storage/category-icons');
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }
                
                // GUARDAR con move() - Método estándar obligatorio
                $file->move($destinationPath, $filename);
                $data['image_path'] = 'category-icons/' . $filename;
            }

            $categoryIcon->update($data);

            return redirect()
                ->route('superlinkiu.category-icons.index')
                ->with('success', 'Icono de categoría actualizado exitosamente.');

        } catch (\Exception $e) {
            \Log::error('Error updating category icon: ' . $e->getMessage());
            return back()
                ->withErrors(['general' => 'Error al actualizar el icono: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Toggle active status of category icon
     */
    public function toggleActive(CategoryIcon $categoryIcon)
    {
        try {
            $categoryIcon->update([
                'is_active' => !$categoryIcon->is_active
            ]);

            return response()->json([
                'success' => true,
                'is_active' => $categoryIcon->is_active,
                'message' => $categoryIcon->is_active ? 'Icono activado' : 'Icono desactivado'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar estado: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update sort order of category icons
     */
    public function updateOrder(Request $request)
    {
        $request->validate([
            'icons' => ['required', 'array'],
            'icons.*.id' => ['required', 'exists:category_icons,id'],
            'icons.*.sort_order' => ['required', 'integer', 'min:0'],
        ]);

        try {
            foreach ($request->icons as $iconData) {
                CategoryIcon::where('id', $iconData['id'])
                    ->update(['sort_order' => $iconData['sort_order']]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Orden actualizado exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar orden: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified category icon
     */
    public function destroy(CategoryIcon $categoryIcon)
    {
        try {
            // Verificar si el icono está siendo usado
            $categoriesCount = \App\Features\TenantAdmin\Models\Category::where('icon_id', $categoryIcon->id)->count();
            
            if ($categoriesCount > 0) {
                return redirect()
                    ->route('superlinkiu.category-icons.index')
                    ->with('error', "No se puede eliminar el icono porque está siendo usado por {$categoriesCount} categoría(s).");
            }

            // ✅ Eliminar archivo usando método estándar
            if ($categoryIcon->image_path) {
                $filePath = public_path('storage/' . $categoryIcon->image_path);
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }

            // Eliminar registro
            $categoryIcon->delete();

            return redirect()
                ->route('superlinkiu.category-icons.index')
                ->with('success', 'Icono de categoría eliminado exitosamente.');

        } catch (\Exception $e) {
            \Log::error('Error deleting category icon: ' . $e->getMessage());
            return redirect()
                ->route('superlinkiu.category-icons.index')
                ->with('error', 'Error al eliminar el icono: ' . $e->getMessage());
        }
    }
} 