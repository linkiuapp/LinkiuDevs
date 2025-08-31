<?php

namespace Database\Seeders;

use App\Shared\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Super Admin',
            'email' => 'admin@local.test',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'role' => 'super_admin',
            'last_login_at' => null,
            'store_id' => null,
        ]);
    }
} 