<?php

namespace App\Features\SuperLinkiu\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProfileController extends Controller
{
    /**
     * Mostrar la página de perfil del super admin
     */
    public function index()
    {
        $user = Auth::user();
        return view('superlinkiu::profile.index', compact('user'));
    }

    /**
     * Actualizar la información del perfil
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'current_password' => 'nullable|required_with:password',
            'password' => 'nullable|min:8|confirmed',
        ]);
        
        // Verificar contraseña actual si se está cambiando la contraseña
        if ($request->filled('current_password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'La contraseña actual no es correcta.']);
            }
        }
        
        // Actualizar datos básicos
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        
        // Actualizar contraseña si se proporcionó
        if ($request->filled('password')) {
            $user->password = Hash::make($validated['password']);
        }
        
        $user->save();
        
        return redirect()->route('superlinkiu.profile.index')->with('success', 'Perfil actualizado correctamente.');
    }
    
    /**
     * Actualizar avatar del usuario
     */
    public function updateAvatar(Request $request)
    {
        try {
            $user = Auth::user();
            
            $validator = Validator::make($request->all(), [
                'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            if ($validator->fails()) {
                return back()->withErrors($validator->errors())->with('error', 'Archivo inválido');
            }

            DB::beginTransaction();
            
            try {
                // Eliminar avatar anterior si existe
                if ($user->avatar_path) {
                    // Para desarrollo local
                    if (Storage::disk('public')->exists($user->avatar_path)) {
                        Storage::disk('public')->delete($user->avatar_path);
                    }
                    // Para producción (S3)
                    if (config('filesystems.default') === 's3' && Storage::disk('s3')->exists($user->avatar_path)) {
                        Storage::disk('s3')->delete($user->avatar_path);
                    }
                }
                
                // Subir nuevo avatar
                $file = $request->file('avatar');
                $filename = 'avatar_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
                
                // Determinar el disco a usar
                $disk = config('filesystems.default', 'public');
                
                // Subir archivo
                if ($disk === 's3') {
                    // Para producción con S3
                    $path = $file->storeAs('avatars', $filename, 's3');
                    // Hacer público el archivo
                    Storage::disk('s3')->setVisibility($path, 'public');
                } else {
                    // Para desarrollo local
                    $path = $file->storeAs('avatars', $filename, 'public');
                }
                
                // Actualizar usuario
                $user->avatar_path = $path;
                $user->save();
                
                DB::commit();
                
                return back()->with('success', 'Avatar actualizado correctamente.');
                
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
            
        } catch (\Exception $e) {
            Log::error('Error al subir avatar', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', 'Error al subir el avatar. Por favor, intenta nuevamente.');
        }
    }
    
    /**
     * Actualizar la configuración del sistema
     */
    public function updateSystem(Request $request)
    {
        $request->validate([
            'app_name' => 'required|string|max:255',
            'app_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'app_favicon' => 'nullable|image|mimes:ico,png|max:1024',
        ]);
        
        try {
            // Guardar configuración en el archivo .env
            $this->updateEnvVariable('APP_NAME', $request->app_name);
            
            $disk = config('filesystems.default', 'public');
            
            // Procesar logo si se ha subido
            if ($request->hasFile('app_logo')) {
                $logoFile = $request->file('app_logo');
                $logoFilename = 'logo_' . time() . '.' . $logoFile->getClientOriginalExtension();
                
                if ($disk === 's3') {
                    $logoPath = $logoFile->storeAs('system', $logoFilename, 's3');
                    Storage::disk('s3')->setVisibility($logoPath, 'public');
                } else {
                    $logoPath = $logoFile->storeAs('system', $logoFilename, 'public');
                }
                
                // Aquí podrías guardar la ruta en una tabla de configuración
                $this->updateEnvVariable('APP_LOGO', $logoPath);
            }
            
            // Procesar favicon si se ha subido
            if ($request->hasFile('app_favicon')) {
                $faviconFile = $request->file('app_favicon');
                $faviconFilename = 'favicon_' . time() . '.' . $faviconFile->getClientOriginalExtension();
                
                if ($disk === 's3') {
                    $faviconPath = $faviconFile->storeAs('system', $faviconFilename, 's3');
                    Storage::disk('s3')->setVisibility($faviconPath, 'public');
                } else {
                    $faviconPath = $faviconFile->storeAs('system', $faviconFilename, 'public');
                }
                
                // Aquí podrías guardar la ruta en una tabla de configuración
                $this->updateEnvVariable('APP_FAVICON', $faviconPath);
            }
            
            return back()->with('success', 'Configuración del sistema actualizada correctamente.');
            
        } catch (\Exception $e) {
            Log::error('Error al actualizar configuración del sistema', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', 'Error al actualizar la configuración del sistema.');
        }
    }
    
    /**
     * Actualizar variable de entorno
     */
    private function updateEnvVariable($key, $value)
    {
        $path = base_path('.env');
        
        if (file_exists($path)) {
            $content = file_get_contents($path);
            
            // Escapar caracteres especiales en el valor
            $value = addslashes($value);
            
            // Reemplazar la variable si existe
            if (strpos($content, $key . '=') !== false) {
                $content = preg_replace("/$key=(.*)/", "$key=\"$value\"", $content);
            } else {
                // Agregar la variable si no existe
                $content .= "\n$key=\"$value\"";
            }
            
            file_put_contents($path, $content);
        }
    }
}