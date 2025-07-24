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
            // Actualizar nombre de la app
            $this->updateEnvVariable('APP_NAME', $request->app_name);

            // Procesar logo si se subió
            if ($request->hasFile('app_logo')) {
                $logoFile = $request->file('app_logo');
                $logoFilename = 'logo_' . time() . '.' . $logoFile->getClientOriginalExtension();
                $logoPath = $logoFile->storeAs('system', $logoFilename, 'public');
                $this->updateEnvVariable('APP_LOGO', $logoPath);
            }

            // Procesar favicon si se subió
            if ($request->hasFile('app_favicon')) {
                $faviconFile = $request->file('app_favicon');
                $faviconFilename = 'favicon_' . time() . '.' . $faviconFile->getClientOriginalExtension();
                $faviconPath = $faviconFile->storeAs('system', $faviconFilename, 'public');
                $this->updateEnvVariable('APP_FAVICON', $faviconPath);
            }

            return back()->with('status', 'app-settings-updated');

        } catch (\Exception $e) {
            return back()->with('status', 'app-settings-error');
        }
    }

    /**
     * Handle avatar file upload
     */
    private function handleAvatarUpload($file, $user)
    {
        // Eliminar avatar anterior si existe
        if ($user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
        }

        // Generar nombre único para el archivo
        $filename = 'avatar_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
        
        // Subir archivo usando el disk público
        $path = $file->storeAs('avatars', $filename, 'public');
        
        // Actualizar path en el usuario
        $user->avatar_path = $path;
    }

    /**
     * Update environment variable
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