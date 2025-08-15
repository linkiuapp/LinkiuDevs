<?php

namespace App\Features\TenantAdmin\Controllers;

use App\Http\Controllers\Controller;
use App\Shared\Models\Store;
use App\Shared\Models\StorePolicy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class BusinessProfileController extends Controller
{
    public function index()
    {
        // El middleware ya identificó la tienda
        $store = view()->shared('currentStore');
        
        if (!$store) {
            return response()->json(['error' => 'Store not found'], 404);
        }
        
        // Cargar las políticas si existen, si no crear un registro vacío
        $policies = $store->policies ?: new StorePolicy();
        
        return view('tenant-admin::business-profile.index', compact('store', 'policies'));
    }

    public function updateOwner(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'owner_name' => 'required|string|max:255',
            'owner_email' => 'required|email|max:255',
            'owner_document_type' => 'required|string|max:50',
            'owner_document_number' => 'required|string|max:50',
            'owner_phone' => 'required|string|max:20',
            'owner_country' => 'required|string|max:100',
            'owner_department' => 'required|string|max:100',
            'owner_city' => 'required|string|max:100',
            'owner_address' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Actualizar datos del usuario
        $user = auth()->user();
        $user->update([
            'name' => $request->owner_name,
            'email' => $request->owner_email,
        ]);

        // Actualizar campos del propietario en la tabla stores
        $store = view()->shared('currentStore');
        $store->update([
            'document_type' => $request->owner_document_type,
            'document_number' => $request->owner_document_number,
            'phone' => $request->owner_phone,
            'country' => $request->owner_country,
            'department' => $request->owner_department,
            'city' => $request->owner_city,
            'address' => $request->owner_address,
        ]);

        return back()->with('success', 'Información del propietario actualizada correctamente.');
    }



    public function updateStore(Request $request, Store $store)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'store_phone' => 'nullable|string|max:20',
            'store_email' => 'nullable|email|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $updateData = [
            'name' => $request->name,
            'description' => $request->description,
        ];

        // Manejar subida de logo
        if ($request->hasFile('logo')) {
            // ✅ Eliminar logo anterior si existe
            if ($store->logo_url && str_contains($store->logo_url, '/storage/')) {
                // Extraer path relativo del URL
                $oldPath = str_replace(asset('storage/'), '', $store->logo_url);
                $oldFile = public_path('storage/' . $oldPath);
                if (file_exists($oldFile)) {
                    unlink($oldFile);
                }
            }
            
            // Generar nombre único para el archivo
            $filename = 'logo_' . $store->id . '_' . time() . '.' . $request->file('logo')->getClientOriginalExtension();
            
            // ✅ Crear directorio si no existe
            $destinationPath = public_path('storage/stores/logos');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }
            
            // ✅ GUARDAR con move() - Método estándar obligatorio
            $request->file('logo')->move($destinationPath, $filename);
            
            // ✅ Generar URL usando método estándar
            $updateData['logo_url'] = asset('storage/stores/logos/' . $filename);
        }

        $store->update($updateData);

        return back()->with('success', 'Información de la tienda actualizada correctamente.');
    }

    public function updateFiscal(Request $request, Store $store)
    {
        $validator = Validator::make($request->all(), [
            'business_name' => 'required|string|max:255',
            'tax_id' => 'required|string|max:50',
            'fiscal_address' => 'required|string|max:255',
            'fiscal_city' => 'required|string|max:100',
            'fiscal_department' => 'required|string|max:100',
            'postal_code' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Usar campos existentes de la tabla stores para información fiscal
        $store->update([
            'header_text_title' => $request->business_name, // Usar como razón social
            'document_number' => $request->tax_id, // NIT
            'address' => $request->fiscal_address,
            'city' => $request->fiscal_city,
            'department' => $request->fiscal_department,
        ]);

        return back()->with('success', 'Información fiscal actualizada correctamente.');
    }

    public function updateSeo(Request $request, Store $store)
    {
        $validator = Validator::make($request->all(), [
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:255',
            'og_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'google_analytics' => 'nullable|string|max:100',
            'facebook_pixel' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $updateData = [
            'meta_title' => $request->meta_title,
            'meta_description' => $request->meta_description,
            'meta_keywords' => $request->meta_keywords,
        ];

        // Manejar subida de imagen OG
        if ($request->hasFile('og_image')) {
            // ✅ Crear directorio si no existe
            $destinationPath = public_path('storage/og-images');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }
            
            // Generar nombre único
            $filename = 'og_' . $store->id . '_' . time() . '.' . $request->file('og_image')->getClientOriginalExtension();
            
            // ✅ GUARDAR con move() - Método estándar obligatorio
            $request->file('og_image')->move($destinationPath, $filename);
            
            $updateData['header_short_description'] = 'og-images/' . $filename; // Usar este campo para OG image
        }

        $store->update($updateData);

        return back()->with('success', 'Información SEO actualizada correctamente.');
    }

    public function updatePolicies(Request $request, Store $store)
    {
        $validator = Validator::make($request->all(), [
            'privacy_policy' => 'nullable|string',
            'terms_conditions' => 'nullable|string',
            'shipping_policy' => 'nullable|string',
            'return_policy' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Crear o actualizar políticas
        $store->policies()->updateOrCreate(
            ['store_id' => $store->id],
            [
                'privacy_policy' => $request->privacy_policy,
                'terms_conditions' => $request->terms_conditions,
                'shipping_policy' => $request->shipping_policy,
                'return_policy' => $request->return_policy,
            ]
        );

        return back()->with('success', 'Políticas actualizadas correctamente.');
    }

    public function updateAbout(Request $request, Store $store)
    {
        $validator = Validator::make($request->all(), [
            'about_us' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Crear o actualizar la sección "Acerca de"
        $store->policies()->updateOrCreate(
            ['store_id' => $store->id],
            ['about_us' => $request->about_us]
        );

        return back()->with('success', 'Información "Acerca de nosotros" actualizada correctamente.');
    }
} 