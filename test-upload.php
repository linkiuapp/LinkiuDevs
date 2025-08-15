<?php
/**
 * Script de diagnóstico para uploads en producción
 */

echo "=== DIAGNÓSTICO DE UPLOADS ===\n\n";

// 1. Verificar configuración
echo "1. CONFIGURACIÓN:\n";
echo "   FILESYSTEM_DISK: " . $_ENV['FILESYSTEM_DISK'] ?? 'no definido' . "\n";
echo "   APP_URL: " . $_ENV['APP_URL'] ?? 'no definido' . "\n\n";

// 2. Verificar directorios
echo "2. DIRECTORIOS:\n";
$dirs = [
    'storage/app/public',
    'storage/app/public/system',
    'storage/app/public/avatars',
    'public/storage'
];

foreach ($dirs as $dir) {
    $path = __DIR__ . '/' . $dir;
    if (file_exists($path)) {
        echo "   ✅ $dir existe";
        if (is_writable($path)) {
            echo " (escribible)";
        } else {
            echo " ❌ (NO escribible)";
        }
        echo "\n";
    } else {
        echo "   ❌ $dir NO existe\n";
    }
}

// 3. Verificar enlace simbólico
echo "\n3. ENLACE SIMBÓLICO:\n";
$linkPath = __DIR__ . '/public/storage';
if (is_link($linkPath)) {
    echo "   ✅ Enlace simbólico existe\n";
    echo "   Apunta a: " . readlink($linkPath) . "\n";
} else {
    echo "   ❌ Enlace simbólico NO existe\n";
}

// 4. Test de escritura
echo "\n4. TEST DE ESCRITURA:\n";
$testFile = __DIR__ . '/storage/app/public/test_' . time() . '.txt';
try {
    file_put_contents($testFile, 'test');
    echo "   ✅ Puede escribir en storage/app/public\n";
    unlink($testFile);
} catch (Exception $e) {
    echo "   ❌ NO puede escribir: " . $e->getMessage() . "\n";
}

// 5. Verificar usuario de Apache
echo "\n5. USUARIO DE APACHE:\n";
echo "   Usuario actual: " . get_current_user() . "\n";
echo "   UID: " . getmyuid() . "\n";
echo "   GID: " . getmygid() . "\n";

echo "\n=== FIN DEL DIAGNÓSTICO ===\n";

