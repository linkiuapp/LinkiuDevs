<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EmailTemplate;

class EmailTemplateSeederNew extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Limpiar tabla existente si existe
        EmailTemplate::truncate();
        
        // Crear plantillas por defecto
        EmailTemplate::createDefaults();
        
        $this->command->info('Plantillas de email creadas exitosamente');
    }
}
