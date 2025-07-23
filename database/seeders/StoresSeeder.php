<?php

namespace Database\Seeders;

use App\Shared\Models\Plan;
use App\Shared\Models\Store;
use App\Shared\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class StoresSeeder extends Seeder
{
    public function run(): void
    {
        $plans = Plan::all();

        // Crear la tienda gaby-y-belen
        $store = Store::create([
            'name' => 'Gaby y belen',
            'email' => null,
            'description' => 'Tienda de Gaby y Belen',
            'phone' => '3135229263',
            'country' => 'Colombia',
            'department' => 'Sucre',
            'city' => 'Sincelejo',
            'address' => 'barrio la palam',
            'slug' => 'gaby-y-belen',
            'plan_id' => $plans->where('name', 'Explorer')->first()->id,
            'status' => 'active',
            'verified' => false,
            'document_type' => 'nit',
            'document_number' => '1002493883-2',
        ]);

        // Crear el usuario administrador de la tienda
        User::create([
            'name' => 'Eva Castro',
            'email' => 'eva@example.com',
            'password' => bcrypt('password'),
            'role' => 'store_admin',
            'store_id' => $store->id,
        ]);
    }
} 