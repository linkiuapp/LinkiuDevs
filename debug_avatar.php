<?php

// 🔧 SCRIPT TEMPORAL DE DEBUG - Avatar Super Admin
// Ejecutar con: php debug_avatar.php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🔍 =============  AVATAR DEBUG SUPER ADMIN  ============= 🔍\n\n";

// 1. Obtener usuario super admin
$user = \App\Models\User::where('role', 'super_admin')->first();

if (!$user) {
    echo "❌ No se encontró usuario super admin\n";
    exit(1);
}

echo "👤 USUARIO:\n";
echo "   ID: {$user->id}\n";
echo "   Nombre: {$user->name}\n";
echo "   Email: {$user->email}\n";
echo "   Role: {$user->role}\n\n";

echo "💾 BASE DE DATOS:\n";
echo "   avatar_path (BD): " . ($user->avatar_path ?? 'NULL') . "\n";
echo "   avatar_path (raw): " . ($user->getRawOriginal('avatar_path') ?? 'NULL') . "\n\n";

echo "🎯 ACCESSOR TEST:\n";
echo "   \$user->avatar_url: " . ($user->avatar_url ?? 'NULL') . "\n";
echo "   getAvatarUrlAttribute(): " . ($user->getAvatarUrlAttribute() ?? 'NULL') . "\n\n";

echo "⚙️ CONFIGURACIÓN:\n";
echo "   FILESYSTEM_DISK: " . config('filesystems.default') . "\n";
echo "   S3 BUCKET: " . config('filesystems.disks.s3.bucket') . "\n";
echo "   S3 URL: " . config('filesystems.disks.s3.url') . "\n";
echo "   S3 REGION: " . config('filesystems.disks.s3.region') . "\n\n";

if ($user->avatar_path) {
    echo "🧪 TESTS S3:\n";
    try {
        $s3Url = \Storage::disk('s3')->url($user->avatar_path);
        echo "   URL S3 generada: {$s3Url}\n";
        
        $exists = \Storage::disk('s3')->exists($user->avatar_path);
        echo "   Archivo existe en S3: " . ($exists ? 'SÍ ✅' : 'NO ❌') . "\n";
        
        if ($exists) {
            $size = \Storage::disk('s3')->size($user->avatar_path);
            echo "   Tamaño archivo: {$size} bytes\n";
        }
    } catch (\Exception $e) {
        echo "   ❌ Error S3: " . $e->getMessage() . "\n";
    }
    
    echo "\n🌐 COMPARACIÓN URLs:\n";
    echo "   S3 URL: " . \Storage::disk('s3')->url($user->avatar_path) . "\n";
    echo "   Asset local: " . asset('storage/' . $user->avatar_path) . "\n";
} else {
    echo "🚫 NO HAY AVATAR_PATH EN BD\n";
}

echo "\n🔄 ATRIBUTOS ELOQUENT:\n";
echo "   Casts: " . json_encode($user->getCasts(), JSON_PRETTY_PRINT) . "\n";
echo "   Mutated attributes: " . json_encode($user->getMutatedAttributes()) . "\n";

echo "\n🔍 DEBUG COMPLETO TERMINADO\n";
echo "💡 Si el problema persiste, revisar:\n";
echo "   1. Middleware que modifique URLs\n";
echo "   2. Event listeners en User model\n";
echo "   3. Cache de Laravel\n";
echo "   4. Configuración de .env\n"; 