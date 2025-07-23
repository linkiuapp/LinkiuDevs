<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Features\TenantAdmin\Models\Product;
use App\Features\TenantAdmin\Models\ProductImage;
use App\Features\TenantAdmin\Models\ProductVariant;
use App\Features\TenantAdmin\Models\Category;
use App\Features\TenantAdmin\Models\Variable;
use App\Shared\Models\Store;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener todas las tiendas
        $stores = Store::all();

        foreach ($stores as $store) {
            $this->seedProductsForStore($store);
        }
    }

    /**
     * Crear productos para una tienda específica
     */
    private function seedProductsForStore(Store $store): void
    {
        // Obtener categorías y variables de la tienda
        $categories = Category::where('store_id', $store->id)->get();
        $variables = Variable::where('store_id', $store->id)->get();

        // Productos de ejemplo
        $productsData = [
            [
                'name' => 'Camiseta Básica Algodón',
                'description' => 'Camiseta básica de algodón 100% orgánico. Cómoda y versátil para uso diario. Disponible en varios colores y tallas.',
                'price' => 25000,
                'type' => 'simple',
                'is_active' => true,
            ],
            [
                'name' => 'Pantalón Jeans Clásico',
                'description' => 'Pantalón jeans de corte clásico, confeccionado en denim de alta calidad. Perfecto para cualquier ocasión.',
                'price' => 85000,
                'type' => 'variable',
                'is_active' => true,
            ],
            [
                'name' => 'Sudadera con Capucha',
                'description' => 'Sudadera cómoda con capucha, ideal para días frescos. Fabricada con mezcla de algodón y poliéster.',
                'price' => 45000,
                'type' => 'simple',
                'is_active' => true,
            ],
            [
                'name' => 'Zapatos Deportivos',
                'description' => 'Zapatos deportivos de alta calidad, perfectos para ejercicio y uso casual. Suela antideslizante y materiales transpirables.',
                'price' => 120000,
                'type' => 'variable',
                'is_active' => true,
            ],
            [
                'name' => 'Chaqueta de Cuero',
                'description' => 'Chaqueta de cuero genuino, estilo clásico. Perfecta para looks casuales y elegantes.',
                'price' => 180000,
                'type' => 'simple',
                'is_active' => false,
            ],
            [
                'name' => 'Falda Midi Elegante',
                'description' => 'Falda midi elegante, perfecta para ocasiones especiales. Disponible en diferentes colores y tallas.',
                'price' => 55000,
                'type' => 'variable',
                'is_active' => true,
            ],
            [
                'name' => 'Camisa Formal',
                'description' => 'Camisa formal de algodón, ideal para el trabajo y eventos especiales. Corte clásico y cómodo.',
                'price' => 65000,
                'type' => 'simple',
                'is_active' => true,
            ],
            [
                'name' => 'Vestido Casual',
                'description' => 'Vestido casual cómodo y versátil. Perfecto para uso diario y salidas informales.',
                'price' => 75000,
                'type' => 'variable',
                'is_active' => true,
            ],
        ];

        foreach ($productsData as $productData) {
            // Crear producto
            $product = Product::create([
                'name' => $productData['name'],
                'description' => $productData['description'],
                'price' => $productData['price'],
                'type' => $productData['type'],
                'is_active' => $productData['is_active'],
                'store_id' => $store->id,
            ]);

            // Asociar categorías aleatorias (1-3 categorías)
            if ($categories->count() > 0) {
                $randomCategories = $categories->random(min(3, $categories->count()));
                $product->categories()->attach($randomCategories->pluck('id')->toArray());
            }

            // Crear variantes si es producto variable
            if ($productData['type'] === 'variable' && $variables->count() > 0) {
                $this->createVariantsForProduct($product, $variables);
            }

            // Crear imágenes ficticias (opcional)
            $this->createFakeImagesForProduct($product);
        }
    }

    /**
     * Crear variantes para un producto
     */
    private function createVariantsForProduct(Product $product, $variables): void
    {
        // Seleccionar 1-2 variables aleatorias
        $selectedVariables = $variables->random(min(2, $variables->count()));
        
        $combinations = [[]];
        
        foreach ($selectedVariables as $variable) {
            $newCombinations = [];
            
            foreach ($combinations as $combination) {
                // Usar hasta 3 opciones por variable
                $options = array_slice($variable->options, 0, 3);
                
                foreach ($options as $option) {
                    $newCombination = $combination;
                    $newCombination[$variable->id] = $option;
                    $newCombinations[] = $newCombination;
                }
            }
            
            $combinations = $newCombinations;
        }

        // Crear variantes
        foreach ($combinations as $combination) {
            $priceModifier = rand(-10000, 20000); // Modificador aleatorio
            
            ProductVariant::create([
                'product_id' => $product->id,
                'sku' => ProductVariant::generateUniqueSku($product, $combination),
                'price_modifier' => $priceModifier,
                'is_active' => rand(0, 1) ? true : false,
                'variant_options' => $combination,
            ]);
        }
    }

    /**
     * Crear imágenes ficticias para un producto
     */
    private function createFakeImagesForProduct(Product $product): void
    {
        // Crear 1-3 imágenes ficticias
        $imageCount = rand(1, 3);
        
        for ($i = 0; $i < $imageCount; $i++) {
            ProductImage::create([
                'product_id' => $product->id,
                'image_path' => "products/demo/product_{$product->id}_{$i}.jpg",
                'thumbnail_path' => "products/demo/thumb_product_{$product->id}_{$i}.jpg",
                'medium_path' => "products/demo/medium_product_{$product->id}_{$i}.jpg",
                'large_path' => "products/demo/large_product_{$product->id}_{$i}.jpg",
                'alt_text' => $product->name . " - Imagen " . ($i + 1),
                'sort_order' => $i,
                'is_main' => $i === 0, // Primera imagen como principal
            ]);
        }
    }
} 