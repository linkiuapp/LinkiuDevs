<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // Added missing import for DB facade

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ticket_categories', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('color', 20)->default('info'); // Color para UI (primary, secondary, success, etc.)
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Insertar categorías por defecto
        DB::table('ticket_categories')->insert([
            [
                'slug' => 'technical',
                'name' => 'Técnico',
                'description' => 'Problemas técnicos, bugs, errores del sistema',
                'color' => 'error',
                'is_active' => true,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'billing',
                'name' => 'Facturación',
                'description' => 'Consultas sobre planes, pagos y facturación',
                'color' => 'warning',
                'is_active' => true,
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'general',
                'name' => 'General',
                'description' => 'Consultas generales y soporte básico',
                'color' => 'info',
                'is_active' => true,
                'sort_order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'feature_request',
                'name' => 'Solicitud de Función',
                'description' => 'Solicitudes de nuevas funcionalidades',
                'color' => 'success',
                'is_active' => true,
                'sort_order' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_categories');
    }
};
