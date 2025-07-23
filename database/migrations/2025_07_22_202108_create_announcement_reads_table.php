<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('announcement_reads', function (Blueprint $table) {
            $table->id();
            
            // Relaciones
            $table->foreignId('announcement_id')->constrained('platform_announcements')->onDelete('cascade');
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
            
            // Timestamp de lectura
            $table->timestamp('read_at');
            
            $table->timestamps();
            
            // Índices para performance y unicidad
            $table->unique(['announcement_id', 'store_id']); // Una lectura por tienda por anuncio
            $table->index('store_id'); // Consultas por tienda
            $table->index('read_at'); // Ordenar por fecha de lectura
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('announcement_reads');
    }
};
