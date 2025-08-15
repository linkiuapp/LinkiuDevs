<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Config;

class DiagnoseStorageCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'storage:diagnose';

    /**
     * The console command description.
     */
    protected $description = 'Diagnosticar configuración de storage y ubicación de imágenes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 DIAGNÓSTICO DE STORAGE - LINKIU.BIO');
        $this->line('==========================================');
        
        // 1. Verificar configuración actual
        $this->info('📋 1. CONFIGURACIÓN ACTUAL:');
        $defaultDisk = Config::get('filesystems.default');
        $this->line("   Default Disk: {$defaultDisk}");
        
        $filesystemDisk = env('FILESYSTEM_DISK', 'local');
        $this->line("   FILESYSTEM_DISK env: {$filesystemDisk}");
        
        // 2. Verificar variables de entorno S3/R2
        $this->info('🔧 2. VARIABLES S3/CLOUDFLARE R2:');
        $s3Vars = [
            'AWS_ACCESS_KEY_ID' => env('AWS_ACCESS_KEY_ID'),
            'AWS_SECRET_ACCESS_KEY' => env('AWS_SECRET_ACCESS_KEY') ? 'SET (hidden)' : 'NOT SET',
            'AWS_DEFAULT_REGION' => env('AWS_DEFAULT_REGION'),
            'AWS_BUCKET' => env('AWS_BUCKET'),
            'AWS_ENDPOINT' => env('AWS_ENDPOINT'),
            'AWS_USE_PATH_STYLE_ENDPOINT' => env('AWS_USE_PATH_STYLE_ENDPOINT'),
        ];
        
        foreach ($s3Vars as $key => $value) {
            $status = $value ? '✅' : '❌';
            $displayValue = $value ?: 'NOT SET';
            $this->line("   {$status} {$key}: {$displayValue}");
        }
        
        // 3. Probar conectividad de discos
        $this->info('🔌 3. CONECTIVIDAD DE DISCOS:');
        
        $disks = ['local', 'public', 's3'];
        foreach ($disks as $disk) {
            try {
                $files = Storage::disk($disk)->files();
                $this->line("   ✅ {$disk}: Conexión exitosa");
            } catch (\Exception $e) {
                $this->line("   ❌ {$disk}: Error - " . $e->getMessage());
            }
        }
        
        // 4. Buscar imágenes en diferentes ubicaciones
        $this->info('🖼️  4. UBICACIÓN DE IMÁGENES:');
        
        // Buscar en disco local
        try {
            $localFiles = Storage::disk('local')->allFiles();
            $imageFiles = array_filter($localFiles, function($file) {
                return preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $file);
            });
            $this->line("   📁 Local storage: " . count($imageFiles) . " imágenes encontradas");
        } catch (\Exception $e) {
            $this->line("   ❌ Local storage: Error - " . $e->getMessage());
        }
        
        // Buscar en disco público
        try {
            $publicFiles = Storage::disk('public')->allFiles();
            $imageFiles = array_filter($publicFiles, function($file) {
                return preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $file);
            });
            $this->line("   📁 Public storage: " . count($imageFiles) . " imágenes encontradas");
        } catch (\Exception $e) {
            $this->line("   ❌ Public storage: Error - " . $e->getMessage());
        }
        
        // Buscar en S3/R2
        try {
            $s3Files = Storage::disk('public')->allFiles();
            $imageFiles = array_filter($s3Files, function($file) {
                return preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $file);
            });
            $this->line("   ☁️  S3/R2 storage: " . count($imageFiles) . " imágenes encontradas");
        } catch (\Exception $e) {
            $this->line("   ❌ S3/R2 storage: Error - " . $e->getMessage());
        }
        
        // 5. Verificar URLs generadas
        $this->info('🔗 5. PRUEBA DE URLs:');
        
        try {
            $testPath = 'test-image.jpg';
            $localUrl = Storage::disk('local')->url($testPath);
            $this->line("   Local URL: {$localUrl}");
        } catch (\Exception $e) {
            $this->line("   ❌ Local URL error: " . $e->getMessage());
        }
        
        try {
            $testPath = 'test-image.jpg';
            $publicUrl = Storage::disk('public')->url($testPath);
            $this->line("   Public URL: {$publicUrl}");
        } catch (\Exception $e) {
            $this->line("   ❌ Public URL error: " . $e->getMessage());
        }
        
        try {
            $testPath = 'test-image.jpg';
            $s3Url = Storage::disk('public')->url($testPath);
            $this->line("   S3/R2 URL: {$s3Url}");
        } catch (\Exception $e) {
            $this->line("   ❌ S3/R2 URL error: " . $e->getMessage());
        }
        
        // 6. Recomendaciones
        $this->info('💡 6. RECOMENDACIONES:');
        
        if (!env('AWS_ACCESS_KEY_ID')) {
            $this->line("   ⚠️  Variables Cloudflare R2 no configuradas");
            $this->line("   📝 Configurar variables AWS_* en Laravel Cloud");
        }
        
        if ($defaultDisk === 'local' && env('APP_ENV') === 'production') {
            $this->line("   ⚠️  Usando storage local en producción");
            $this->line("   📝 Cambiar FILESYSTEM_DISK=s3 para producción");
        }
        
        $this->line('==========================================');
        $this->info('✅ Diagnóstico completado');
    }
} 