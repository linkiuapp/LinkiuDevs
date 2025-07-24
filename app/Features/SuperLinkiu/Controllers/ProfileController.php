<?php

namespace App\Features\SuperLinkiu\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function show()
    {
        return view('superlinkiu::profile.show', [
            'user' => Auth::user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ]);

        // Actualizar información básica
        $user->fill($request->only(['name', 'email']));

        // Manejar avatar si se subió uno nuevo
        if ($request->hasFile('avatar')) {
            $this->handleAvatarUpload($request->file('avatar'), $user);
        }

        $user->save();

        return back()->with('status', 'profile-updated');
    }

    /**
     * Update the user's password.
     */
    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        Auth::user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('status', 'password-updated');
    }

    /**
     * Delete the user's avatar.
     */
    public function deleteAvatar()
    {
        $user = Auth::user();

        if ($user->avatar_path) {
            // Eliminar archivo del storage público
            Storage::disk('public')->delete($user->avatar_path);
            
            // Limpiar campo en BD
            $user->update(['avatar_path' => null]);
        }

        return back()->with('status', 'avatar-deleted');
    }

    /**
     * Update app settings (logo, favicon, name)
     */
    public function updateAppSettings(Request $request)
    {
        $request->validate([
            'app_name' => ['required', 'string', 'max:255'],
            'app_logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
            'app_favicon' => ['nullable', 'image', 'mimes:ico,png', 'max:1024'],
        ]);

        try {
            // Detectar automáticamente el disk correcto
            $disk = $this->getStorageDisk();
            
            // TODO: Implementar actualización de APP_NAME sin modificar .env directamente
            // $this->updateEnvVariable('APP_NAME', $request->app_name);

            // Manejar logo
            if ($request->hasFile('app_logo')) {
                $logoFile = $request->file('app_logo');
                $logoFilename = 'logo_' . time() . '.' . $logoFile->getClientOriginalExtension();
                $logoPath = $logoFile->storeAs('system', $logoFilename, $disk);
                
                // TODO: Implementar actualización de APP_LOGO sin modificar .env directamente  
                // $this->updateEnvVariable('APP_LOGO', $logoPath);
                
                // Por ahora, guardamos en sesión para mostrar el cambio temporalmente
                session(['temp_app_logo' => $logoPath]);
                
                // Log para debugging
                \Log::info("Logo guardado: {$logoPath} en disk: {$disk}");
            }

            // Manejar favicon
            if ($request->hasFile('app_favicon')) {
                $faviconFile = $request->file('app_favicon');
                $faviconFilename = 'favicon_' . time() . '.' . $faviconFile->getClientOriginalExtension();
                $faviconPath = $faviconFile->storeAs('system', $faviconFilename, $disk);
                
                // TODO: Implementar actualización de APP_FAVICON sin modificar .env directamente
                // $this->updateEnvVariable('APP_FAVICON', $faviconPath);
                
                // Por ahora, guardamos en sesión para mostrar el cambio temporalmente
                session(['temp_app_favicon' => $faviconPath]);
                
                // Log para debugging
                \Log::info("Favicon guardado: {$faviconPath} en disk: {$disk}");
            }

            return back()->with('status', 'app-settings-updated');
        } catch (\Exception $e) {
            // Log del error para debugging
            \Log::error('Error en updateAppSettings: ' . $e->getMessage());
            return back()->with('status', 'app-settings-error');
        }
    }

    /**
     * Detecta automáticamente qué disk usar según el entorno
     */
    private function getStorageDisk(): string
    {
        // En Laravel Cloud existe el disk 'storage'
        if (config('filesystems.disks.storage')) {
            return 'storage';
        }
        
        // En local y otros entornos, usar el disk por defecto o public
        $defaultDisk = config('filesystems.default', 'public');
        
        // Si el disk por defecto es 'local', usar 'public' para URLs públicas
        if ($defaultDisk === 'local') {
            return 'public';
        }
        
        return $defaultDisk;
    }

    private function handleAvatarUpload($file, $user)
    {
        // Detectar automáticamente el disk correcto
        $disk = $this->getStorageDisk();
        
        // Eliminar avatar anterior si existe
        if ($user->avatar_path) {
            Storage::disk($disk)->delete($user->avatar_path);
        }

        // Generar nombre único para el archivo
        $filename = 'avatar_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
        
        // Guardar en el disk configurado
        $path = $file->storeAs('avatars', $filename, $disk);
        
        // Actualizar path en el usuario
        $user->avatar_path = $path;
    }

    /**
     * Update environment variable
     */
    private function updateEnvVariable($key, $value)
    {
        $path = base_path('.env');
        
        if (!file_exists($path)) {
            return false;
        }

        $content = file_get_contents($path);
        
        // Escapar valor para uso seguro en .env
        $escapedValue = str_replace('"', '\"', $value);
        $newLine = $key . '="' . $escapedValue . '"';
        
        // Escapar la clave para usar en regex
        $escapedKey = preg_quote($key, '/');
        
        // Buscar si la variable ya existe
        $pattern = '/^' . $escapedKey . '=.*$/m';
        
        if (preg_match($pattern, $content)) {
            // Reemplazar la variable existente
            $content = preg_replace($pattern, $newLine, $content);
        } else {
            // Agregar la variable al final del archivo
            $content = rtrim($content) . "\n" . $newLine . "\n";
        }
        
        // Guardar el archivo
        return file_put_contents($path, $content) !== false;
    }
}