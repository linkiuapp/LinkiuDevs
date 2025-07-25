<?php

namespace App\Features\TenantAdmin\Services;

use App\Features\TenantAdmin\Models\Product;
use App\Features\TenantAdmin\Models\ProductImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class ProductImageService
{
    /**
     * Crear directorios necesarios - SIEMPRE en storage/
     */
    private function ensureDirectoriesExist(int $productId): void
    {
        $productDir = public_path('storage/products/' . $productId . '/images');
        if (!file_exists($productDir)) {
            mkdir($productDir, 0755, true);
        }
    }

    /**
     * Procesar y guardar imágenes de producto
     */
    public function processImages(Product $product, array $images, ?int $mainImageIndex = null): array
    {
        $processedImages = [];
        
        foreach ($images as $index => $image) {
            if ($image instanceof UploadedFile) {
                $processedImage = $this->processImage($product, $image, $index, $mainImageIndex);
                if ($processedImage) {
                    $processedImages[] = $processedImage;
                }
            }
        }
        
        return $processedImages;
    }

    /**
     * Procesar una imagen individual
     */
    private function processImage(Product $product, UploadedFile $image, int $index, ?int $mainImageIndex = null): ?ProductImage
    {
        try {
            // Validar que sea una imagen
            if (!$image->isValid() || !str_starts_with($image->getMimeType(), 'image/')) {
                return null;
            }

            // Asegurar que existan los directorios
            $this->ensureDirectoriesExist($product->id);

            // Generar nombre único para la imagen
            $filename = $this->generateUniqueFilename($image);
            
            // GUARDAR SIEMPRE en public/storage/products/{productId}/images/
            $destinationPath = public_path('storage/products/' . $product->id . '/images');
            $image->move($destinationPath, $filename);
            
            // Path relativo para guardar en BD
            $relativePath = 'products/' . $product->id . '/images/' . $filename;

            // Crear registro en base de datos
            $productImage = ProductImage::create([
                'product_id' => $product->id,
                'image_path' => $relativePath,
                'thumbnail_path' => null, // Por ahora null, se puede implementar después
                'medium_path' => null,
                'large_path' => null,
                'alt_text' => $product->name,
                'is_main' => $mainImageIndex === $index,
                'sort_order' => $index
            ]);

            return $productImage;

        } catch (\Exception $e) {
            \Log::error('Error procesando imagen de producto: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Generar nombre único para archivo
     */
    private function generateUniqueFilename(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        $name = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $timestamp = now()->format('YmdHis');
        $random = Str::random(8);
        
        return "{$name}_{$timestamp}_{$random}.{$extension}";
    }

    /**
     * Eliminar imagen
     */
    public function deleteImage(ProductImage $image): bool
    {
        try {
            // Eliminar archivo físico
            $fullPath = public_path('storage/' . $image->image_path);
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }

            // Eliminar thumbnails si existen
            if ($image->thumbnail_path) {
                $thumbnailPath = public_path('storage/' . $image->thumbnail_path);
                if (file_exists($thumbnailPath)) {
                    unlink($thumbnailPath);
                }
            }

            if ($image->medium_path) {
                $mediumPath = public_path('storage/' . $image->medium_path);
                if (file_exists($mediumPath)) {
                    unlink($mediumPath);
                }
            }

            if ($image->large_path) {
                $largePath = public_path('storage/' . $image->large_path);
                if (file_exists($largePath)) {
                    unlink($largePath);
                }
            }

            // Eliminar registro de BD
            return $image->delete();

        } catch (\Exception $e) {
            \Log::error('Error eliminando imagen de producto: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Eliminar todas las imágenes de un producto
     */
    public function deleteAllImages(Product $product): bool
    {
        try {
            foreach ($product->images as $image) {
                $this->deleteImage($image);
            }
            return true;
        } catch (\Exception $e) {
            \Log::error('Error eliminando imágenes del producto: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Establecer imagen principal
     */
    public function setMainImage(Product $product, ProductImage $image): bool
    {
        try {
            // Quitar la marca de principal de todas las imágenes
            $product->images()->update(['is_main' => false]);
            
            // Establecer como principal la imagen seleccionada
            $image->update(['is_main' => true]);
            
            return true;
        } catch (\Exception $e) {
            \Log::error('Error estableciendo imagen principal: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Reordenar imágenes
     */
    public function reorderImages(Product $product, array $imageIds): bool
    {
        try {
            foreach ($imageIds as $index => $imageId) {
                ProductImage::where('id', $imageId)
                    ->where('product_id', $product->id)
                    ->update(['sort_order' => $index]);
            }
            return true;
        } catch (\Exception $e) {
            \Log::error('Error reordenando imágenes: ' . $e->getMessage());
            return false;
        }
    }
} 