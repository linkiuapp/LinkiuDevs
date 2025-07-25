<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Shared\Models\User;
use App\Shared\Models\Store;
use Illuminate\Support\Facades\File;

class DiagnoseStorageIssues extends Command
{
    protected $signature = 'diagnose:storage {--fix-symlink} {--clean-orphans} {--test-upload}';
    protected $description = 'Diagnose and fix storage/image issues in production';

    public function handle()
    {
        $this->info('🖼️  DIAGNÓSTICO DE STORAGE E IMÁGENES');
        $this->newLine();

        // 1. Verificar symlink
        $this->checkSymlink();
        $this->newLine();

        // 2. Verificar directorios
        $this->checkDirectories();
        $this->newLine();

        // 3. Verificar imágenes existentes
        $this->checkExistingImages();
        $this->newLine();

        // 4. Opciones adicionales
        if ($this->option('fix-symlink')) {
            $this->fixSymlink();
        }

        if ($this->option('clean-orphans')) {
            $this->cleanOrphanImages();
        }

        if ($this->option('test-upload')) {
            $this->testUpload();
        }

        $this->info('✅ Diagnóstico completado');
    }

    private function checkSymlink()
    {
        $this->info('🔗 Verificando symlink de storage...');
        
        $publicStorage = public_path('storage');
        $storagePublic = storage_path('app/public');

        if (!file_exists($publicStorage)) {
            $this->error('❌ public/storage NO existe');
            $this->line('   Solución: php artisan storage:link');
            return;
        }

        if (!is_link($publicStorage)) {
            $this->warn('⚠️  public/storage existe pero NO es un symlink');
            $this->line('   Tipo: ' . (is_dir($publicStorage) ? 'directorio' : 'archivo'));
            return;
        }

        $target = readlink($publicStorage);
        $this->line("✅ Symlink existe: {$publicStorage} → {$target}");

        if (!file_exists($target)) {
            $this->error('❌ El target del symlink NO existe');
        } else {
            $this->line('✅ Target del symlink existe');
        }

        // Verificar si apunta al lugar correcto
        $expectedTarget = $storagePublic;
        if (realpath($target) !== realpath($expectedTarget)) {
            $this->warn('⚠️  Symlink apunta a lugar incorrecto');
            $this->line("   Actual: {$target}");
            $this->line("   Esperado: {$expectedTarget}");
        } else {
            $this->line('✅ Symlink apunta al lugar correcto');
        }
    }

    private function checkDirectories()
    {
        $this->info('📁 Verificando directorios de imágenes...');

        $directories = [
            'storage/avatars' => 'Avatares de usuarios',
            'storage/system' => 'Logo y favicon de app',
            'storage/stores/logos' => 'Logos de tiendas',
            'storage/store-design' => 'Diseños de tienda',
            'storage/products' => 'Imágenes de productos'
        ];

        foreach ($directories as $dir => $description) {
            $fullPath = public_path($dir);
            $exists = file_exists($fullPath);
            $writable = $exists ? is_writable($fullPath) : false;
            $fileCount = $exists ? count(glob($fullPath . '/*')) : 0;

            $status = $exists ? ($writable ? '✅' : '⚠️') : '❌';
            $this->line("{$status} {$dir} ({$description})");
            
            if ($exists) {
                $this->line("   Archivos: {$fileCount}");
                if (!$writable) {
                    $perms = substr(sprintf('%o', fileperms($fullPath)), -4);
                    $this->line("   Permisos: {$perms} (no escribible)");
                }
            }
        }
    }

    private function checkExistingImages()
    {
        $this->info('🖼️  Verificando imágenes existentes...');

        // Verificar avatares
        $usersWithAvatars = User::whereNotNull('avatar_path')->get();
        $this->line("👤 Usuarios con avatar: {$usersWithAvatars->count()}");

        $avatarIssues = 0;
        foreach ($usersWithAvatars as $user) {
            $avatarPath = public_path('storage/' . $user->avatar_path);
            if (!file_exists($avatarPath)) {
                $avatarIssues++;
            }
        }

        if ($avatarIssues > 0) {
            $this->warn("   ⚠️  {$avatarIssues} avatares con archivos faltantes");
        } else {
            $this->line("   ✅ Todos los avatares tienen archivos");
        }

        // Verificar logos de tiendas
        $storesWithLogos = Store::whereNotNull('logo_url')->get();
        $this->line("🏪 Tiendas con logo: {$storesWithLogos->count()}");

        $logoIssues = 0;
        foreach ($storesWithLogos as $store) {
            // Extraer path del logo_url
            $logoPath = str_replace(asset('storage/'), '', $store->logo_url);
            $fullLogoPath = public_path('storage/' . $logoPath);
            
            if (!file_exists($fullLogoPath)) {
                $logoIssues++;
            }
        }

        if ($logoIssues > 0) {
            $this->warn("   ⚠️  {$logoIssues} logos con archivos faltantes");
        } else {
            $this->line("   ✅ Todos los logos tienen archivos");
        }
    }

    private function fixSymlink()
    {
        $this->info('🔧 Intentando reparar symlink...');

        try {
            $publicStorage = public_path('storage');
            
            // Eliminar si existe pero no es symlink correcto
            if (file_exists($publicStorage) && !is_link($publicStorage)) {
                $this->warn('Eliminando directorio/archivo incorrecto en public/storage');
                if (is_dir($publicStorage)) {
                    File::deleteDirectory($publicStorage);
                } else {
                    unlink($publicStorage);
                }
            }

            // Recrear symlink
            $storagePublic = storage_path('app/public');
            if (!file_exists($storagePublic)) {
                mkdir($storagePublic, 0755, true);
                $this->line("Creado directorio: {$storagePublic}");
            }

            if (file_exists($publicStorage)) {
                unlink($publicStorage);
            }

            symlink($storagePublic, $publicStorage);
            $this->info('✅ Symlink recreado exitosamente');

        } catch (\Exception $e) {
            $this->error('❌ Error recreando symlink: ' . $e->getMessage());
        }
    }

    private function cleanOrphanImages()
    {
        $this->info('🧹 Limpiando imágenes huérfanas...');

        $directories = [
            'storage/avatars',
            'storage/stores/logos',
            'storage/store-design'
        ];

        foreach ($directories as $dir) {
            $fullPath = public_path($dir);
            if (!file_exists($fullPath)) continue;

            $files = glob($fullPath . '/*');
            $orphanCount = 0;

            foreach ($files as $file) {
                if (!is_file($file)) continue;

                $filename = basename($file);
                $isOrphan = false;

                // Verificar según el tipo de directorio
                if (str_contains($dir, 'avatars')) {
                    $relativePath = 'avatars/' . $filename;
                    $isOrphan = !User::where('avatar_path', $relativePath)->exists();
                } elseif (str_contains($dir, 'logos')) {
                    $expectedUrl = asset('storage/stores/logos/' . $filename);
                    $isOrphan = !Store::where('logo_url', $expectedUrl)->exists();
                }

                if ($isOrphan) {
                    $orphanCount++;
                    if ($this->confirm("¿Eliminar archivo huérfano: {$filename}?", false)) {
                        unlink($file);
                        $this->line("Eliminado: {$filename}");
                    }
                }
            }

            if ($orphanCount === 0) {
                $this->line("✅ {$dir}: Sin archivos huérfanos");
            } else {
                $this->line("⚠️  {$dir}: {$orphanCount} archivos huérfanos encontrados");
            }
        }
    }

    private function testUpload()
    {
        $this->info('🧪 Probando upload de archivo...');

        try {
            $testDir = public_path('storage/test');
            if (!file_exists($testDir)) {
                mkdir($testDir, 0755, true);
            }

            $testFile = $testDir . '/test_' . time() . '.txt';
            $testContent = 'Test file created at ' . now() . ' from command';
            
            file_put_contents($testFile, $testContent);
            
            if (file_exists($testFile)) {
                $url = asset('storage/test/' . basename($testFile));
                $this->info("✅ Archivo creado exitosamente");
                $this->line("   Archivo: {$testFile}");
                $this->line("   URL: {$url}");
                
                // Probar lectura
                $readContent = file_get_contents($testFile);
                if ($readContent === $testContent) {
                    $this->line("✅ Archivo se puede leer correctamente");
                } else {
                    $this->error("❌ Error leyendo archivo");
                }

                // Limpiar
                unlink($testFile);
                if (is_dir($testDir) && count(glob($testDir . '/*')) === 0) {
                    rmdir($testDir);
                }
                $this->line("✅ Archivo de prueba eliminado");

            } else {
                $this->error("❌ No se pudo crear archivo de prueba");
            }

        } catch (\Exception $e) {
            $this->error('❌ Error en test de upload: ' . $e->getMessage());
        }
    }
} 