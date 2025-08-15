<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Shared\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class MakeSuperAdmin extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'make:super-admin 
                           {--email= : Email del super administrador}
                           {--name= : Nombre del super administrador}
                           {--password= : Contraseña del super administrador}';

    /**
     * The console command description.
     */
    protected $description = 'Crear un usuario super administrador para Linkiu.bio';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Creando Super Administrador para Linkiu.bio');
        $this->info('===============================================');

        // Obtener datos del usuario
        $email = $this->option('email') ?: $this->ask('📧 Email del super administrador');
        $name = $this->option('name') ?: $this->ask('👤 Nombre completo');
        $password = $this->option('password') ?: $this->secret('🔐 Contraseña');

        // Validar datos
        $validator = Validator::make([
            'email' => $email,
            'name' => $name,
            'password' => $password,
        ], [
            'email' => 'required|email|unique:users,email',
            'name' => 'required|string|min:2|max:255',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            $this->error('❌ Error en la validación:');
            foreach ($validator->errors()->all() as $error) {
                $this->error("   - $error");
            }
            return Command::FAILURE;
        }

        // Verificar si ya existe un super admin
        $existingSuperAdmin = User::where('role', 'super_admin')->first();
        if ($existingSuperAdmin) {
            $this->warn('⚠️  Ya existe un super administrador:');
            $this->warn("   📧 {$existingSuperAdmin->email}");
            $this->warn("   👤 {$existingSuperAdmin->name}");
            
            if (!$this->confirm('¿Deseas crear otro super administrador?')) {
                $this->info('❌ Operación cancelada');
                return Command::SUCCESS;
            }
        }

        try {
            // Crear el usuario
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'role' => 'super_admin',
                'last_login_at' => null,
                'store_id' => null, // Super admin no pertenece a ninguna tienda
                'email_verified_at' => now(), // Verificado automáticamente
            ]);

            $this->info('✅ Super administrador creado exitosamente!');
            $this->info('===============================================');
            $this->table(['Campo', 'Valor'], [
                ['ID', $user->id],
                ['Nombre', $user->name],
                ['Email', $user->email],
                ['Rol', $user->role],
                ['Creado', $user->created_at->format('Y-m-d H:i:s')],
            ]);

            $this->info('🔗 URLs importantes:');
            $this->info("   📊 Panel Super Admin: " . config('app.url') . '/superlinkiu');
            $this->info("   🔐 Login: " . config('app.url') . '/superlinkiu/login');

            $this->warn('🔐 IMPORTANTE: Guarda estas credenciales en un lugar seguro');
            
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Error al crear el super administrador:');
            $this->error("   {$e->getMessage()}");
            return Command::FAILURE;
        }
    }
}
