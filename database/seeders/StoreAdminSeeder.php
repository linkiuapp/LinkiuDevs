<?php

namespace Database\Seeders;

use App\Shared\Models\Store;
use App\Shared\Models\User;
use Illuminate\Database\Seeder;

class StoreAdminSeeder extends Seeder
{
    public function run(): void
    {
        // Buscar la tienda gaby-y-belen
        $store = Store::where('slug', 'gaby-y-belen')->first();

        if (!$store) {
            $this->command->error('La tienda gaby-y-belen no existe');
            return;
        }

        // Verificar si el usuario ya existe
        $existingUser = User::where('email', 'eva@example.com')->first();
        if ($existingUser) {
            $this->command->info('El usuario administrador ya existe');
            return;
        }

        // Crear el usuario administrador de la tienda
        User::create([
            'name' => 'Eva Castro',
            'email' => 'eva@example.com',
            'password' => bcrypt('password'),
            'role' => 'store_admin',
            'store_id' => $store->id,
        ]);

        $this->command->info('Usuario administrador creado exitosamente');
    }
} 