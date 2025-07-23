<?php

namespace Database\Seeders;

use App\Shared\Models\Plan;
use Illuminate\Database\Seeder;

class PlansSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Explorer',
                'description' => 'Plan gratuito para comenzar tu tienda online',
                'allow_custom_slug' => false,
                'price' => 0,
                'prices' => json_encode([
                    'monthly' => 0,
                    'quarterly' => 0,
                    'semester' => 0
                ]),
                'currency' => 'COP',
                'duration_in_days' => 30,
                'max_products' => 20,
                'max_slider' => 1,
                'max_active_promotions' => 1,
                'max_active_coupons' => 1,
                'max_categories' => 3,
                'max_sedes' => 1,
                'max_admins' => 1,
                'max_delivery_zones' => 1,
                'support_level' => 'basic',
                'support_response_time' => 120, // 5 días en horas
                'is_active' => true,
                'is_public' => true,
                'is_featured' => false,
                'sort_order' => 1,
                'trial_days' => 0,
                'additional_features' => json_encode([
                    'Soporte básico',
                    'Personalización básica',
                    'Reportes básicos'
                ]),
                'features_list' => json_encode([
                    '20 productos',
                    '1 slider',
                    '1 promoción activa',
                    '1 cupón',
                    '3 categorías',
                    '1 sede',
                    '1 zona de reparto',
                    'Soporte en 5 días',
                    'Slug genérico'
                ]),
            ],
            [
                'name' => 'Master',
                'description' => 'Plan ideal para tiendas en crecimiento',
                'allow_custom_slug' => true,
                'price' => 60000,
                'prices' => json_encode([
                    'monthly' => 60000,
                    'quarterly' => 160000,
                    'semester' => 320000
                ]),
                'currency' => 'COP',
                'duration_in_days' => 30,
                'max_products' => 150,
                'max_slider' => 10,
                'max_active_promotions' => 10,
                'max_active_coupons' => 10,
                'max_categories' => 15,
                'max_sedes' => 5,
                'max_admins' => 3,
                'max_delivery_zones' => 2,
                'support_level' => 'priority',
                'support_response_time' => 48,
                'is_active' => true,
                'is_public' => true,
                'is_featured' => true,
                'sort_order' => 2,
                'trial_days' => 7,
                'additional_features' => json_encode([
                    'Soporte prioritario',
                    'Personalización avanzada',
                    'Reportes avanzados',
                    'Integración con redes sociales'
                ]),
                'features_list' => json_encode([
                    '150 productos',
                    '10 sliders',
                    '10 promociones activas',
                    '10 cupones',
                    '15 categorías',
                    '5 sedes',
                    '2 zonas de reparto',
                    'Soporte en 48 horas',
                    'Slug personalizado',
                    'Múltiples administradores'
                ]),
            ],
            [
                'name' => 'Legend',
                'description' => 'Plan premium para tiendas profesionales',
                'allow_custom_slug' => true,
                'price' => 100000,
                'prices' => json_encode([
                    'monthly' => 100000,
                    'quarterly' => 280000,
                    'semester' => 550000
                ]),
                'currency' => 'COP',
                'duration_in_days' => 30,
                'max_products' => 350,
                'max_slider' => 20,
                'max_active_promotions' => 20,
                'max_active_coupons' => 20,
                'max_categories' => 30,
                'max_sedes' => 10,
                'max_admins' => 5,
                'max_delivery_zones' => 4,
                'support_level' => 'premium',
                'support_response_time' => 12,
                'is_active' => true,
                'is_public' => true,
                'is_featured' => false,
                'sort_order' => 3,
                'trial_days' => 7,
                'additional_features' => json_encode([
                    'Soporte premium',
                    'Personalización total',
                    'Reportes avanzados',
                    'Integración con marketplaces',
                    'Prioridad en nuevas funciones'
                ]),
                'features_list' => json_encode([
                    '350 productos',
                    '20 sliders',
                    '20 promociones activas',
                    '20 cupones',
                    '30 categorías',
                    '10 sedes',
                    '4 zonas de reparto',
                    'Soporte en 12 horas',
                    'Slug personalizado',
                    'Múltiples administradores',
                    'Acceso prioritario a nuevas funciones'
                ]),
            ],
        ];

        foreach ($plans as $plan) {
            Plan::create($plan);
        }
    }
} 