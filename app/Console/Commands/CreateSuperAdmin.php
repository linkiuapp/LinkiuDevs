<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CreateSuperAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-super-admin {email?} {password?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a super admin user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email') ?? 'admin@linkiubio.com';
        $password = $this->argument('password') ?? 'password';

        // Check if user exists
        $user = \App\Models\User::where('email', $email)->first();

        if ($user) {
            $this->info("User with email {$email} already exists!");
            
            if ($this->confirm('Do you want to update this user to super admin?')) {
                $user->role = 'super_admin';
                $user->save();
                $this->info('User updated to super admin successfully!');
            }
            
            return;
        }

        // Create new user
        $user = \App\Models\User::create([
            'name' => 'Super Admin',
            'email' => $email,
            'password' => bcrypt($password),
            'role' => 'super_admin',
        ]);

        $this->info("Super admin created successfully with email: {$email}");
        $this->info("Please change the password after first login!");
    }
}
