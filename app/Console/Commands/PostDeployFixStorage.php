<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class PostDeployFixStorage extends Command
{
    protected $signature = 'post-deploy:fix-storage';
    protected $description = 'Fix storage issues after Laravel Cloud deployment';

    public function handle()
    {
        $this->info('🚀 POST-DEPLOY: Reparando storage...');

        // 1. Verificar y crear symlink
        $this->fixSymlink();

        // 2. Crear directorios necesarios
        $this->createDirectories();

        // 3. Verificar permisos
        $this->fixPermissions();

        // 4. Migrar imágenes existentes si es necesario
        $this->migrateExistingImages();

        $this->info('✅ Storage reparado exitosamente');
    }

    private function fixSymlink()
    {
        $this->info('🔗 Verificando symlink...');

        $publicStorage = public_path('storage');
        $storagePublic = storage_path('app/public');

        // Asegurar que storage/app/public existe
        if (!file_exists($storagePublic)) {
            mkdir($storagePublic, 0755, true);
            $this->line("✅ Creado: {$storagePublic}");
        }

        // Eliminar public/storage si no es symlink correcto
        if (file_exists($publicStorage)) {
            if (!is_link($publicStorage)) {
                $this->warn("⚠️  public/storage existe pero no es symlink - eliminando");
                if (is_dir($publicStorage)) {
                    $this->deleteDirectory($publicStorage);
                } else {
                    unlink($publicStorage);
                }
            } else {
                // Verificar si el symlink apunta al lugar correcto
                $target = readlink($publicStorage);
                if (realpath($target) !== realpath($storagePublic)) {
                    $this->warn("⚠️  Symlink apunta a lugar incorrecto - recreando");
                    unlink($publicStorage);
                }
            }
        }

        // Crear symlink si no existe
        if (!file_exists($publicStorage)) {
            symlink($storagePublic, $publicStorage);
            $this->info("✅ Symlink creado: {$publicStorage} → {$storagePublic}");
        } else {
            $this->line("✅ Symlink ya existe y es correcto");
        }
    }

    private function createDirectories()
    {
        $this->info('📁 Creando directorios necesarios...');

        $directories = [
            'storage/app/public/avatars',
            'storage/app/public/system',
            'storage/app/public/stores/logos',
            'storage/app/public/store-design',
            'storage/app/public/products'
        ];

        foreach ($directories as $dir) {
            $fullPath = base_path($dir);
            if (!file_exists($fullPath)) {
                mkdir($fullPath, 0755, true);
                $this->line("✅ Creado: {$dir}");
            }
        }
    }

    private function fixPermissions()
    {
        $this->info('🔐 Verificando permisos...');

        $paths = [
            storage_path('app/public'),
            public_path('storage')
        ];

        foreach ($paths as $path) {
            if (file_exists($path)) {
                chmod($path, 0755);
                $this->line("✅ Permisos actualizados: {$path}");
            }
        }
    }

    private function migrateExistingImages()
    {
        $this->info('🔄 Migrando imágenes existentes...');

        // Verificar si hay imágenes en public/storage que necesiten moverse
        $publicStoragePath = public_path('storage');
        $storagePublicPath = storage_path('app/public');

        if (is_dir($publicStoragePath) && !is_link($publicStoragePath)) {
            $this->warn("⚠️  Encontradas imágenes en public/storage directo - moviendo a storage/app/public");
            
            $items = glob($publicStoragePath . '/*');
            foreach ($items as $item) {
                $basename = basename($item);
                $destination = $storagePublicPath . '/' . $basename;
                
                if (!file_exists($destination)) {
                    if (is_dir($item)) {
                        $this->copyDirectory($item, $destination);
                    } else {
                        copy($item, $destination);
                    }
                    $this->line("✅ Movido: {$basename}");
                }
            }
        }
    }

    private function deleteDirectory($dir)
    {
        if (!is_dir($dir)) {
            return false;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            if (is_dir($path)) {
                $this->deleteDirectory($path);
            } else {
                unlink($path);
            }
        }

        return rmdir($dir);
    }

    private function copyDirectory($src, $dst)
    {
        if (!is_dir($src)) {
            return false;
        }

        if (!file_exists($dst)) {
            mkdir($dst, 0755, true);
        }

        $files = array_diff(scandir($src), ['.', '..']);
        foreach ($files as $file) {
            $srcPath = $src . '/' . $file;
            $dstPath = $dst . '/' . $file;
            
            if (is_dir($srcPath)) {
                $this->copyDirectory($srcPath, $dstPath);
            } else {
                copy($srcPath, $dstPath);
            }
        }

        return true;
    }
} 