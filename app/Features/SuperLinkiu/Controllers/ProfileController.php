<?php

namespace App\Features\SuperLinkiu\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

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
    public function updateProfile(Request $request)
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
     * Actualizar la imagen de perfil
     */
    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        
        $user = Auth::user();
        
        // Eliminar avatar anterior si existe
        if ($user->avatar_path && Storage::disk(config('filesystems.default'))->exists($user->avatar_path)) {
            Storage::disk(config('filesystems.default'))->delete($user->avatar_path);
        }
        
        // Guardar nuevo avatar
        $path = $request->file('avatar')->store('avatars', config('filesystems.default'));
        $user->avatar_path = $path;
        $user->save();
        
        return redirect()->route('superlinkiu.profile.index')->with('success', 'Imagen de perfil actualizada correctamente.');
    }
    
    /**
     * Actualizar la configuración del sistema
     */
    public function updateSystemSettings(Request $request)
    {
        $request->validate([
            'app_name' => 'required|string|max:255',
            'app_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'app_favicon' => 'nullable|image|mimes:ico,png|max:1024',
        ]);
        
        // Guardar configuración en el archivo .env
        $this->updateEnvVariable('APP_NAME', $request->app_name);
        
        // Procesar logo si se ha subido
        if ($request->hasFile('app_logo')) {
            $logoPath = $request->file('app_logo')->store('system', config('filesystems.default'));
            // Aquí podrías guardar la ruta en una tabla de configuración o en un archivo
        }
        
        // Procesar favicon si se ha subido
        if ($request->hasFile('app_favicon')) {
            $faviconPath = $request->file('app_favicon')->store('system', config('filesystems.default'));
            // Aquí podrías guardar la ruta en una tabla de configuración o en un archivo
        }
        
        return redirect()->route('superlinkiu.profile.index')->with('success', 'Configuración del sistema actualizada correctamente.');
    }
    
    /**
     * Actualizar variable de entorno
     */
    private function updateEnvVariable($key, $value)
    {
        $path = base_path('.env');
        
        if (file_exists($path)) {
            $content = file_get_contents($path);
            
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