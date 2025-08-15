<?php

namespace App\Features\TenantAdmin\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class StoreDesignImageService
{


    /**
     * Procesa y guarda el logo de la tienda
     *
     * @param UploadedFile $file
     * @param int $storeId
     * @return array{logo_url: string, logo_webp_url: string}
     */
    public function handleLogo(UploadedFile $file, int $storeId): array
    {
        // Generar nombre único para el archivo
        $filename = 'logo_' . time() . '.' . $file->getClientOriginalExtension();
        $path = 'store-design/' . $storeId . '/' . $filename;
        
        // ✅ Crear directorio si no existe
        $destinationPath = public_path('storage/store-design/' . $storeId);
        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0755, true);
        }
        
        // ✅ GUARDAR con move() - Método estándar obligatorio
        $file->move($destinationPath, $filename);
        
        // ✅ Retornar URLs usando método estándar
        return [
            'logo_url' => asset('storage/' . $path),
            'logo_webp_url' => null // Por ahora no generamos WebP
        ];
    }

    /**
     * Procesa y guarda el favicon de la tienda
     *
     * @param UploadedFile $file
     * @param int $storeId
     * @return array{favicon_url: string}
     */
    public function handleFavicon(UploadedFile $file, int $storeId): array
    {
        // Generar nombre único para el archivo
        $filename = 'favicon_' . time() . '.' . $file->getClientOriginalExtension();
        $path = 'store-design/' . $storeId . '/' . $filename;
        
        // ✅ Crear directorio si no existe
        $destinationPath = public_path('storage/store-design/' . $storeId);
        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0755, true);
        }
        
        // ✅ GUARDAR con move() - Método estándar obligatorio
        $file->move($destinationPath, $filename);
        
        // ✅ Retornar URL usando método estándar
        return [
            'favicon_url' => asset('storage/' . $path)
        ];
    }

    /**
     * Elimina imágenes antiguas de una tienda
     *
     * @param int $storeId
     * @param string $prefix Logo o favicon
     * @return void
     */
    public function cleanOldImages(int $storeId, string $prefix): void
    {
        try {
            $directory = 'store-design/' . $storeId;

            // ✅ Obtener archivos usando método estándar
            $directoryPath = public_path('storage/' . $directory);
            if (is_dir($directoryPath)) {
                $files = glob($directoryPath . '/' . $prefix . '_*');
                
                foreach ($files as $filePath) {
                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }
                }
            }
        } catch (\Exception $e) {
            \Log::warning('Error cleaning old images from S3:', [
                'store_id' => $storeId,
                'prefix' => $prefix,
                'error' => $e->getMessage()
            ]);
        }
    }
} 