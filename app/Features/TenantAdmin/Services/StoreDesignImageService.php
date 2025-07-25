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
        
        // Guardar en bucket S3
        Storage::disk('s3')->putFileAs('store-design/' . $storeId, $file, $filename, 'public');
        
        // Retornar URLs usando bucket S3
        return [
            'logo_url' => Storage::disk('s3')->url($path),
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
        
        // Guardar en bucket S3
        Storage::disk('s3')->putFileAs('store-design/' . $storeId, $file, $filename, 'public');
        
        // Retornar URL usando bucket S3
        return [
            'favicon_url' => Storage::disk('s3')->url($path)
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

            // Obtener todos los archivos que coincidan con el patrón en el bucket S3
            $files = Storage::disk('s3')->files($directory);
            
            foreach ($files as $file) {
                $filename = basename($file);
                if (str_starts_with($filename, $prefix . '_')) {
                    Storage::disk('s3')->delete($file);
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