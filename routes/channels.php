<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

// NOTA: Como ahora usamos canales públicos (Channel en lugar de PrivateChannel),
// no necesitamos autorización. Comentamos estas definiciones para evitar errores 401.

/*
// Canal para SuperLinkiu (solo super admins)
Broadcast::channel('superlinkiu-notifications', function ($user) {
    return $user && $user->isSuperAdmin();
});

// Canal para cada tienda (solo store admins de esa tienda)
Broadcast::channel('store.{storeSlug}.notifications', function ($user, $storeSlug) {
    if (!$user || !$user->isStoreAdmin()) {
        return false;
    }
    
    // Verificar que el usuario pertenece a esta tienda
    $store = \App\Shared\Models\Store::where('slug', $storeSlug)->first();
    return $store && $user->tenant_id === $store->tenant_id;
}); 
*/ 