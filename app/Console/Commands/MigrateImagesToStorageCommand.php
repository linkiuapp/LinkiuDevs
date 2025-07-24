<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MigrateImagesToStorageCommand extends Command
{
    protected $signature = 'migrate:images-to-storage';
    protected $description = 'Migrar archivos de public/images/ a public/storage/';

    public function handle()
    {
        $this->info('🔄 MIGRANDO ARCHIVOS: public/images/ → public/storage/');
        
        $imagesPath = public_path('images');
        $storagePath = public_path('storage');
        
        // Verificar si existe public/images/
        if (!File::exists($imagesPath)) {
            $this->info('✅ No hay directorio public/images/ para migrar');
            return;
        }
        
        // Crear directorios de destino si no existen
        if (!File::exists($storagePath)) {
            File::makeDirectory($storagePath, 0755, true);
            $this->info("📁 Creado directorio: {$storagePath}");
        }
        
        // Migrar subdirectorios
        $this->migrateDirectory('system');
        $this->migrateDirectory('avatars');
        
        // Eliminar directorio images si está vacío
        if ($this->isDirectoryEmpty($imagesPath)) {
            File::deleteDirectory($imagesPath);
            $this->info("🗑️ Eliminado directorio vacío: {$imagesPath}");
        }
        
        $this->info('✅ MIGRACIÓN COMPLETADA');
    }
    
    private function migrateDirectory(string $subdir): void
    {
        $sourcePath = public_path("images/{$subdir}");
        $destPath = public_path("storage/{$subdir}");
        
        if (!File::exists($sourcePath)) {
            $this->warn("⚠️ No existe: {$sourcePath}");
            return;
        }
        
        // Crear directorio de destino
        if (!File::exists($destPath)) {
            File::makeDirectory($destPath, 0755, true);
            $this->info("📁 Creado: {$destPath}");
        }
        
        // Migrar archivos
        $files = File::files($sourcePath);
        $moved = 0;
        
        foreach ($files as $file) {
            $filename = $file->getFilename();
            $sourceFile = $file->getPathname();
            $destFile = $destPath . '/' . $filename;
            
            if (File::move($sourceFile, $destFile)) {
                $this->line("📦 {$filename} → storage/{$subdir}/");
                $moved++;
            } else {
                $this->error("❌ Error moviendo: {$filename}");
            }
        }
        
        $this->info("✅ Migrados {$moved} archivos de {$subdir}");
        
        // Eliminar directorio origen si está vacío
        if ($this->isDirectoryEmpty($sourcePath)) {
            File::deleteDirectory($sourcePath);
            $this->info("🗑️ Eliminado directorio vacío: {$sourcePath}");
        }
    }
    
    private function isDirectoryEmpty(string $path): bool
    {
        return File::exists($path) && count(File::allFiles($path)) === 0;
    }
} 