<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Shared\Models\BillingSetting;

class BillingSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Solo crear si no existe
        if (BillingSetting::count() === 0) {
            BillingSetting::create([
                'logo_url' => asset('assets/images/linkiu-logo.png'),
                'company_name' => 'Linkiu SAS',
                'company_address' => 'Carrera 7 #32-16, Oficina 501, Bogotá D.C., Colombia',
                'tax_id' => '900.123.456-7',
                'phone' => '+57 (1) 234-5678',
                'email' => 'facturacion@linkiu.bio',
                'footer_text' => 'Gracias por confiar en Linkiu para impulsar tu negocio digital. Para soporte técnico, visita nuestro centro de ayuda en linkiu.bio/ayuda o contacta nuestro equipo en soporte@linkiu.bio',
            ]);

            $this->command->info('✅ Configuración de facturación creada exitosamente');
        } else {
            $this->command->info('ℹ️  La configuración de facturación ya existe');
        }
    }
}
