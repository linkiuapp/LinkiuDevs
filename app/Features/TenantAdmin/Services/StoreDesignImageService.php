<?php

namespace App\Features\TenantAdmin\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class StoreDesignImageService
{
    /**
     * Directorio base para almacenar las imágenes de diseño de la tienda
     */
    protected string $baseDirectory = 'store-design';

    /**
     * Procesa y guarda el logo de la tienda
     *
     * @param UploadedFile $file
     * @param int $storeId
     * @return array{logo_url: string, logo_webp_url: string}
     */
    public function handleLogo(UploadedFile $file, int $storeId): array
    {
        $directory = $this->getStoreDirectory($storeId);
        
        // Generar nombre único para el archivo
        $filename = 'logo_' . time() . '.' . $file->getClientOriginalExtension();
        $path = $directory . '/' . $filename;
        
        // Guardar el archivo directamente
        $storedPath = Storage::disk('public')->putFileAs($directory, $file, $filename);

        return [
            'logo_url' => asset('storage/' . $storedPath),
            'logo_webp_url' => null // Por ahora no generamos WebP sin Intervention Image
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
        $directory = $this->getStoreDirectory($storeId);
        
        // Generar nombre único para el archivo
        $filename = 'favicon_' . time() . '.' . $file->getClientOriginalExtension();
        
        // Guardar el archivo directamente
        $storedPath = Storage::disk('public')->putFileAs($directory, $file, $filename);

        return [
            'favicon_url' => asset('storage/' . $storedPath)
        ];
    }

    /**
     * Obtiene el directorio de almacenamiento para una tienda específica
     */
    protected function getStoreDirectory(int $storeId): string
    {
        return "{$this->baseDirectory}/{$storeId}";
    }

    /**
     * Procesa y guarda una imagen con las opciones especificadas
     *
     * @param UploadedFile $file
     * @param string $directory
     * @param string $prefix
     * @param array $options
     * @return string Ruta relativa del archivo guardado
     */
    protected function processAndSaveImage(UploadedFile $file, string $directory, string $prefix, array $options): string
    {
        $image = Image::make($file);
        
        // Aplicar redimensionamiento si está especificado
        if (isset($options['resize'])) {
            [$width, $height] = $options['resize'];
            if ($height === null) {
                $image->widen($width, function ($constraint) {
                    $constraint->upsize();
                });
            } else {
                $image->fit($width, $height);
            }
        }

        // Generar nombre único
        $filename = $prefix . '_' . time() . '.' . ($options['format'] ?? $file->getClientOriginalExtension());
        $path = $directory . '/' . $filename;

        // Guardar imagen procesada
        Storage::put(
            $path,
            $image->encode(
                format: $options['format'] ?? null,
                quality: $options['quality'] ?? 90
            )->encoded
        );

        return $path;
    }

    /**
     * Convierte y guarda una imagen en formato WebP
     *
     * @param UploadedFile $file
     * @param string $directory
     * @param string $prefix
     * @param array $options
     * @return string Ruta relativa del archivo WebP
     */
    protected function convertToWebP(UploadedFile $file, string $directory, string $prefix, array $options): string
    {
        $image = Image::make($file);

        // Aplicar redimensionamiento si está especificado
        if (isset($options['resize'])) {
            [$width, $height] = $options['resize'];
            if ($height === null) {
                $image->widen($width, function ($constraint) {
                    $constraint->upsize();
                });
            } else {
                $image->fit($width, $height);
            }
        }

        // Generar nombre para versión WebP
        $filename = $prefix . '_' . time() . '.webp';
        $path = $directory . '/' . $filename;

        // Guardar versión WebP
        Storage::put(
            $path,
            $image->encode('webp', $options['quality'] ?? 90)->encoded
        );

        return $path;
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
            $directory = 'public/assets/store-design/' . $storeId;

            // Verificar si el directorio existe
            if (!Storage::disk('public')->exists($directory)) {
                return;
            }

            // Obtener todos los archivos que coincidan con el patrón
            $files = Storage::disk('public')->files($directory);
            foreach ($files as $file) {
                if (str_starts_with(basename($file), $prefix . '_')) {
                    Storage::disk('public')->delete($file);
                }
            }
        } catch (\Exception $e) {
            Log::warning('Error cleaning old images:', [
                'store_id' => $storeId,
                'prefix' => $prefix,
                'error' => $e->getMessage()
            ]);
        }
    }
} 