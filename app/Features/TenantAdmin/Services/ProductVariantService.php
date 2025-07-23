<?php

namespace App\Features\TenantAdmin\Services;

use App\Features\TenantAdmin\Models\Product;
use App\Features\TenantAdmin\Models\ProductVariant;
use App\Features\TenantAdmin\Models\Variable;

class ProductVariantService
{
    /**
     * Generar variantes automáticamente basadas en las variables del producto
     */
    public function generateVariants(Product $product, array $selectedVariables): array
    {
        if ($product->type !== 'variable') {
            return [];
        }

        // Obtener las variables seleccionadas con sus opciones
        $variables = Variable::whereIn('id', $selectedVariables)
            ->where('store_id', $product->store_id)
            ->get();

        if ($variables->isEmpty()) {
            return [];
        }

        // Generar todas las combinaciones posibles
        $combinations = $this->generateCombinations($variables);
        
        $variants = [];
        foreach ($combinations as $combination) {
            $variant = $this->createVariant($product, $combination);
            if ($variant) {
                $variants[] = $variant;
            }
        }

        return $variants;
    }

    /**
     * Generar todas las combinaciones posibles de opciones
     */
    private function generateCombinations($variables): array
    {
        $combinations = [[]];

        foreach ($variables as $variable) {
            $newCombinations = [];
            
            foreach ($combinations as $combination) {
                foreach ($variable->options as $option) {
                    $newCombination = $combination;
                    $newCombination[$variable->id] = $option;
                    $newCombinations[] = $newCombination;
                }
            }
            
            $combinations = $newCombinations;
        }

        return $combinations;
    }

    /**
     * Crear una variante individual
     */
    private function createVariant(Product $product, array $combination): ?ProductVariant
    {
        try {
            // Crear array de opciones para JSON
            $variantOptions = [];
            foreach ($combination as $variableId => $option) {
                $variantOptions[$variableId] = $option;
            }

            // Generar SKU único
            $sku = ProductVariant::generateUniqueSku($product, $variantOptions);

            // Crear la variante
            return ProductVariant::create([
                'product_id' => $product->id,
                'sku' => $sku,
                'price_modifier' => 0, // Por defecto sin modificador
                'is_active' => true,
                'variant_options' => $variantOptions,
            ]);

        } catch (\Exception $e) {
            \Log::error('Error creando variante: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Actualizar variantes existentes
     */
    public function updateVariants(Product $product, array $variantData): bool
    {
        try {
            foreach ($variantData as $variantId => $data) {
                $variant = ProductVariant::where('id', $variantId)
                    ->where('product_id', $product->id)
                    ->first();

                if ($variant) {
                    $variant->update([
                        'price_modifier' => $data['price_modifier'] ?? 0,
                        'is_active' => $data['is_active'] ?? true,
                    ]);
                }
            }

            return true;
        } catch (\Exception $e) {
            \Log::error('Error actualizando variantes: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Eliminar variantes que ya no son necesarias
     */
    public function removeUnusedVariants(Product $product, array $selectedVariables): bool
    {
        try {
            if ($product->type !== 'variable') {
                // Si cambió a simple, eliminar todas las variantes
                $product->variants()->delete();
                return true;
            }

            // Obtener las variables seleccionadas
            $variables = Variable::whereIn('id', $selectedVariables)
                ->where('store_id', $product->store_id)
                ->get();

            // Generar las combinaciones que deberían existir
            $expectedCombinations = $this->generateCombinations($variables);
            
            // Obtener variantes existentes
            $existingVariants = $product->variants;
            
            // Eliminar variantes que ya no corresponden
            foreach ($existingVariants as $variant) {
                $shouldKeep = false;
                
                foreach ($expectedCombinations as $combination) {
                    $variantOptions = [];
                    foreach ($combination as $variableId => $option) {
                        $variantOptions[$variableId] = $option;
                    }
                    
                    if ($variant->variant_options == $variantOptions) {
                        $shouldKeep = true;
                        break;
                    }
                }
                
                if (!$shouldKeep) {
                    $variant->delete();
                }
            }

            return true;
        } catch (\Exception $e) {
            \Log::error('Error eliminando variantes no utilizadas: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener estadísticas de variantes
     */
    public function getVariantStats(Product $product): array
    {
        $variants = $product->variants;
        
        return [
            'total_variants' => $variants->count(),
            'active_variants' => $variants->where('is_active', true)->count(),
            'inactive_variants' => $variants->where('is_active', false)->count(),
            'price_range' => [
                'min' => $variants->min('final_price'),
                'max' => $variants->max('final_price'),
            ],
            'average_modifier' => $variants->avg('price_modifier'),
        ];
    }

    /**
     * Validar datos de variantes
     */
    public function validateVariantData(array $variantData): array
    {
        $errors = [];

        foreach ($variantData as $index => $data) {
            if (isset($data['price_modifier'])) {
                $modifier = $data['price_modifier'];
                if (!is_numeric($modifier) || $modifier < -1000000 || $modifier > 1000000) {
                    $errors["variant_{$index}_price_modifier"] = 'El modificador de precio debe estar entre -$1.000.000 y $1.000.000';
                }
            }
        }

        return $errors;
    }

    /**
     * Duplicar variantes de un producto a otro
     */
    public function duplicateVariants(Product $originalProduct, Product $newProduct): bool
    {
        try {
            foreach ($originalProduct->variants as $variant) {
                $variant->duplicate($newProduct);
            }
            
            return true;
        } catch (\Exception $e) {
            \Log::error('Error duplicando variantes: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener las variables utilizadas en un producto
     */
    public function getProductVariables(Product $product): array
    {
        if ($product->type !== 'variable' || $product->variants->isEmpty()) {
            return [];
        }

        $variableIds = [];
        foreach ($product->variants as $variant) {
            if ($variant->variant_options) {
                $variableIds = array_merge($variableIds, array_keys($variant->variant_options));
            }
        }

        $variableIds = array_unique($variableIds);
        
        return Variable::whereIn('id', $variableIds)
            ->where('store_id', $product->store_id)
            ->get()
            ->toArray();
    }
} 