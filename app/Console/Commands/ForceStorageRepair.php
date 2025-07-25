<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ForceStorageRepair extends Command
{
    protected $signature = 'storage:force-repair';
    protected $description = 'Aggressively repair storage symlink for Laravel Cloud';

    public function handle()
    {
        $this->info('🚨 FORCE STORAGE REPAIR - Laravel Cloud');
        
        try {
            $publicPath = '/var/www/html/public';
            $storagePath = '/var/www/html/storage/app/public';
            $publicStorage = $publicPath . '/storage';
            
            $this->info("📍 Paths:");
            $this->line("   public: {$publicPath}");
            $this->line("   storage: {$storagePath}");
            $this->line("   symlink target: {$publicStorage}");
            
            // Paso 1: Crear storage/app/public si no existe
            if (!is_dir($storagePath)) {
                $this->info('📁 Creating storage/app/public...');
                mkdir($storagePath, 0755, true);
                chmod($storagePath, 0755);
            }
            
            // Paso 2: Eliminar cualquier cosa en public/storage
            if (file_exists($publicStorage)) {
                $this->warn('🗑️ Removing existing public/storage...');
                if (is_link($publicStorage)) {
                    unlink($publicStorage);
                } else {
                    // Es directorio - eliminar recursivamente
                    $this->removeDirectory($publicStorage);
                }
            }
            
            // Paso 3: Crear symlink
            $this->info('🔗 Creating symlink...');
            symlink($storagePath, $publicStorage);
            
            // Paso 4: Verificar symlink
            if (is_link($publicStorage) && readlink($publicStorage) === $storagePath) {
                $this->info('✅ Symlink created successfully!');
            } else {
                $this->error('❌ Symlink creation failed!');
                return 1;
            }
            
            // Paso 5: Crear directorios de imágenes
            $directories = [
                'avatars', 'system', 'stores/logos', 'store-design', 'products'
            ];
            
            $this->info('📂 Creating image directories...');
            foreach ($directories as $dir) {
                $fullPath = $storagePath . '/' . $dir;
                if (!is_dir($fullPath)) {
                    mkdir($fullPath, 0755, true);
                    chmod($fullPath, 0755);
                    $this->line("   ✅ {$dir}");
                } else {
                    $this->line("   ℹ️ {$dir} (exists)");
                }
            }
            
            // Paso 6: Verificación final
            $this->info('🔍 Final verification:');
            $this->line('   public/storage exists: ' . (file_exists($publicStorage) ? 'YES' : 'NO'));
            $this->line('   public/storage is link: ' . (is_link($publicStorage) ? 'YES' : 'NO'));
            $this->line('   symlink target: ' . (is_link($publicStorage) ? readlink($publicStorage) : 'N/A'));
            
            $this->info('🎉 Storage repair completed!');
            return 0;
            
        } catch (\Exception $e) {
            $this->error('💥 CRITICAL ERROR: ' . $e->getMessage());
            $this->error('File: ' . $e->getFile());
            $this->error('Line: ' . $e->getLine());
            return 1;
        }
    }
    
    private function removeDirectory($dir)
    {
        if (!is_dir($dir)) return false;
        
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            if (is_dir($path)) {
                $this->removeDirectory($path);
            } else {
                unlink($path);
            }
        }
        return rmdir($dir);
    }
} 