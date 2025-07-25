<?php

namespace App\Features\TenantAdmin\Services;

use Illuminate\Http\UploadedFile;

class StoreDesignImageService
{
    /**
     * SIEMPRE usar storage/ - Laravel Cloud debe soportar public/storage/
     */
    private function getStoragePath(): string
    {
        return 'storage';
    }

    /**
     * Crear directorios necesarios - SIEMPRE en storage/
     */
    private function ensureDirectoriesExist(int $storeId): void
    {
        $storeDir = public_path('storage/store-design/' . $storeId);
        if (!file_exists($storeDir)) {
            mkdir($storeDir, 0755, true);
        }
    }

    /**
     * Procesa y guarda el logo de la tienda
     *
     * @param UploadedFile $file
     * @param int $storeId
     * @return array{logo_url: string, logo_webp_url: string}
     */
    public function handleLogo(UploadedFile $file, int $storeId): array
    {
        // Asegurar que existan los directorios
        $this->ensureDirectoriesExist($storeId);
        
        // Generar nombre único para el archivo
        $filename = 'logo_' . time() . '.' . $file->getClientOriginalExtension();
        
        // GUARDAR SIEMPRE en public/storage/store-design/{storeId}/
        $destinationPath = public_path('storage/store-design/' . $storeId);
        $file->move($destinationPath, $filename);
        
        // Retornar URLs usando asset('storage/...')
        return [
            'logo_url' => asset('storage/store-design/' . $storeId . '/' . $filename),
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
        // Asegurar que existan los directorios
        $this->ensureDirectoriesExist($storeId);
        
        // Generar nombre único para el archivo
        $filename = 'favicon_' . time() . '.' . $file->getClientOriginalExtension();
        
        // GUARDAR SIEMPRE en public/storage/store-design/{storeId}/
        $destinationPath = public_path('storage/store-design/' . $storeId);
        $file->move($destinationPath, $filename);
        
        // Retornar URL usando asset('storage/...')
        return [
            'favicon_url' => asset('storage/store-design/' . $storeId . '/' . $filename)
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
            $directory = public_path('storage/store-design/' . $storeId);

            // Verificar si el directorio existe
            if (!is_dir($directory)) {
                return;
            }

            // Obtener todos los archivos que coincidan con el patrón
            $files = glob($directory . '/' . $prefix . '_*');
            foreach ($files as $file) {
                if (file_exists($file)) {
                    unlink($file);
                }
            }
        } catch (\Exception $e) {
            \Log::warning('Error cleaning old images:', [
                'store_id' => $storeId,
                'prefix' => $prefix,
                'error' => $e->getMessage()
            ]);
        }
    }
} 