<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MigrateImagesToCloud extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:migrate-images-to-cloud';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate images from local storage to cloud storage';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting migration of images from local storage to cloud storage...');
        
        // Directorios a migrar
        $directories = [
            'sliders',
            'logos',
            'store-designs',
            'products',
            'categories',
            'og-images',
            'announcements',
        ];
        
        $totalFiles = 0;
        $migratedFiles = 0;
        
        foreach ($directories as $directory) {
            $this->info("Processing directory: {$directory}");
            
            // Verificar si el directorio existe en el almacenamiento local
            if (!\Storage::disk('public')->exists($directory)) {
                $this->warn("Directory {$directory} does not exist in local storage. Skipping...");
                continue;
            }
            
            // Obtener todos los archivos en el directorio y subdirectorios
            $files = \Storage::disk('public')->allFiles($directory);
            $totalFiles += count($files);
            
            $bar = $this->output->createProgressBar(count($files));
            $bar->start();
            
            foreach ($files as $file) {
                try {
                    // Verificar si el archivo ya existe en la nube
                    if (!\Storage::disk('public')->exists($file)) {
                        // Leer el contenido del archivo local
                        $contents = \Storage::disk('public')->get($file);
                        
                        // Guardar el archivo en la nube
                        \Storage::disk('public')->put($file, $contents, 'public');
                        $migratedFiles++;
                    }
                } catch (\Exception $e) {
                    $this->error("Error migrating file {$file}: " . $e->getMessage());
                }
                
                $bar->advance();
            }
            
            $bar->finish();
            $this->newLine();
        }
        
        $this->info("Migration completed. {$migratedFiles} of {$totalFiles} files migrated successfully.");
    }
}
