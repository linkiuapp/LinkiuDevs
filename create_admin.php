<?php

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Crear usuario administrador
$user = DB::table('users')->insert([
    'name' => 'Super Admin',
    'email' => 'admin@linkiu.bio',
    'password' => Hash::make('password'),
    'role' => 'admin',
    'created_at' => now(),
    'updated_at' => now()
]);

if ($user) {
    echo "Usuario administrador creado correctamente.\n";
} else {
    echo "Error al crear el usuario administrador.\n";
}

// Verificar que el usuario se haya creado
$admin = DB::table('users')->where('email', 'admin@linkiu.bio')->first();
if ($admin) {
    echo "Usuario verificado: {$admin->name} ({$admin->email})\n";
} else {
    echo "No se encontró el usuario administrador.\n";
}