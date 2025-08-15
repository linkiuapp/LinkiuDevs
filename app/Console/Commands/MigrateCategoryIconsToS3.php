<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Shared\Models\CategoryIcon;

class MigrateCategoryIconsToS3 extends Command
{
    protected $signature = 'migrate:category-icons-to-s3';
    protected $description = 'Migrate category icons from public/assets/icons/categories/ to S3 bucket';

    public function handle()
    {
        $this->info('Starting migration of category icons to S3...');

        $iconsDirectory = public_path('assets/icons/categories');
        
        if (!is_dir($iconsDirectory)) {
            $this->error('Icons directory not found: ' . $iconsDirectory);
            return 1;
        }

        $iconMappings = [
            'Desgranado.svg' => 'desgranado.svg',
            'Pizza.svg' => 'pizza.svg', 
            'Perro caliente.svg' => 'perro_caliente.svg',
            'Hamburguesa.svg' => 'hamburguesa.svg',
        ];

        $migratedCount = 0;

        foreach ($iconMappings as $originalName => $newName) {
            $originalPath = $iconsDirectory . '/' . $originalName;
            
            if (!file_exists($originalPath)) {
                $this->warn("Original file not found: {$originalName}");
                continue;
            }

            try {
                // Leer el archivo
                $fileContent = file_get_contents($originalPath);
                
                // Subir al bucket S3
                $s3Path = 'category-icons/' . $newName;
                Storage::disk('public')->put($s3Path, $fileContent, 'public');
                
                $this->info("Migrated: {$originalName} → {$s3Path}");
                $migratedCount++;
                
            } catch (\Exception $e) {
                $this->error("Failed to migrate {$originalName}: " . $e->getMessage());
            }
        }

        $this->info("Migration completed. {$migratedCount} icons migrated to S3.");

        // Actualizar la base de datos si es necesario
        $this->info('Updating database records...');
        
        $updates = [
            'desgranado' => 'category-icons/desgranado.svg',
            'pizza' => 'category-icons/pizza.svg',
            'perro_caliente' => 'category-icons/perro_caliente.svg',
            'hamburguesa' => 'category-icons/hamburguesa.svg',
        ];

        foreach ($updates as $name => $newPath) {
            CategoryIcon::where('name', $name)->update(['image_path' => $newPath]);
            $this->info("Updated DB record for: {$name}");
        }

        $this->info('Database update completed.');
        return 0;
    }
} 