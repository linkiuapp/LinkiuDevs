<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MigrationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $migrations = [
            '2025_07_17_015114_create_store_locations_table',
            '2025_07_17_015115_create_location_schedules_table',
            '2025_07_17_015116_create_location_social_links_table',
            '2025_07_17_015133_create_location_schedules_table'
        ];

        $batch = DB::table('migrations')->max('batch') + 1;

        foreach ($migrations as $migration) {
            if (!DB::table('migrations')->where('migration', $migration)->exists()) {
                DB::table('migrations')->insert([
                    'migration' => $migration,
                    'batch' => $batch
                ]);
                $this->command->info("Migration {$migration} marked as run.");
            } else {
                $this->command->info("Migration {$migration} already exists in the migrations table.");
            }
        }
    }
}