<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Shared\Models\CategoryIcon;

class CategoryIconsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $icons = [
            [
                'name' => 'desgranado',
                'display_name' => 'Desgranado',
                'image_path' => 'category-icons/desgranado.svg',
                'sort_order' => 1,
            ],
            [
                'name' => 'pizza',
                'display_name' => 'Pizza',
                'image_path' => 'category-icons/pizza.svg',
                'sort_order' => 2,
            ],
            [
                'name' => 'perro_caliente',
                'display_name' => 'Perro Caliente',
                'image_path' => 'category-icons/perro_caliente.svg',
                'sort_order' => 3,
            ],
            [
                'name' => 'hamburguesa',
                'display_name' => 'Hamburguesa',
                'image_path' => 'category-icons/hamburguesa.svg',
                'sort_order' => 4,
            ],
        ];

        foreach ($icons as $icon) {
            CategoryIcon::updateOrCreate(
                ['name' => $icon['name']],
                $icon
            );
        }
    }
} 