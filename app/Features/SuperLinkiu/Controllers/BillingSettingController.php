<?php

namespace App\Features\SuperLinkiu\Controllers;

use App\Http\Controllers\Controller;
use App\Shared\Models\BillingSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BillingSettingController extends Controller
{
    /**
     * Mostrar configuración de facturación
     */
    public function index()
    {
        $settings = BillingSetting::getInstance();
        
        return view('superlinkiu::billing-settings.index', compact('settings'));
    }
    
    /**
     * Actualizar configuración de facturación
     */
    public function update(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string|max:255',
            'company_address' => 'nullable|string|max:500',
            'tax_id' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'footer_text' => 'nullable|string|max:1000',
            'logo' => 'nullable|file|max:2048'
        ]);
        
        // Validación manual del tipo de archivo si se subió un logo
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $fileExtension = strtolower($file->getClientOriginalExtension());
            
            if (!in_array($fileExtension, $allowedExtensions)) {
                return back()->withErrors(['logo' => 'El logo debe ser una imagen (jpg, jpeg, png, gif, webp).']);
            }
        }
        
        $settings = BillingSetting::getInstance();
        
        // Manejar subida de logo
        if ($request->hasFile('logo')) {
            try {
                $file = $request->file('logo');
                
                // Eliminar logo anterior si existe
                if ($settings->logo_url) {
                    $oldLogoPath = str_replace('/storage/', '', $settings->logo_url);
                    $oldFullPath = storage_path('app/public/' . $oldLogoPath);
                    
                    if (file_exists($oldFullPath)) {
                        unlink($oldFullPath);
                    }
                }
                
                // Crear directorio si no existe
                $uploadPath = storage_path('app/public/billing/logos');
                if (!file_exists($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }
                
                // Generar nombre único para el archivo
                $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $destinationPath = $uploadPath . '/' . $fileName;
                
                // Mover archivo manualmente
                if (move_uploaded_file($file->getPathname(), $destinationPath)) {
                    $settings->logo_url = '/storage/billing/logos/' . $fileName;
                } else {
                    return back()->withErrors(['logo' => 'Error al subir el logo. Intenta nuevamente.']);
                }
                
            } catch (\Exception $e) {
                \Log::error('Error subiendo logo: ' . $e->getMessage());
                return back()->withErrors(['logo' => 'Error al procesar el logo: ' . $e->getMessage()]);
            }
        }
        
        // Actualizar otros campos
        $settings->fill($request->only([
            'company_name',
            'company_address', 
            'tax_id',
            'phone',
            'email',
            'footer_text'
        ]));
        
        $settings->save();
        
        return redirect()->route('superlinkiu.billing-settings.index')
                        ->with('success', 'Configuración de facturación actualizada correctamente.');
    }
    
    /**
     * Eliminar logo
     */
    public function removeLogo()
    {
        $settings = BillingSetting::getInstance();
        
        if ($settings->logo_url) {
            try {
                $logoPath = str_replace('/storage/', '', $settings->logo_url);
                $fullPath = storage_path('app/public/' . $logoPath);
                
                if (file_exists($fullPath)) {
                    unlink($fullPath);
                }
            } catch (\Exception $e) {
                \Log::error('Error eliminando logo: ' . $e->getMessage());
            }
        }
        
        $settings->logo_url = null;
        $settings->save();
        
        return response()->json(['success' => true]);
    }
}
