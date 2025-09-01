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
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            
            // Identificación de la plantilla
            $table->string('key')->unique(); // ej: 'store_welcome', 'ticket_created'
            $table->string('name'); // Nombre descriptivo
            $table->string('context'); // 'store_management', 'support', 'billing'
            
            // Contenido de la plantilla
            $table->string('subject');
            $table->text('body_html');
            $table->text('body_text')->nullable();
            
            // Variables disponibles (JSON array)
            $table->json('variables')->nullable();
            
            // Estado
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
            
            // Índices
            $table->index(['context', 'is_active']);
            $table->index('key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_templates');
    }
};
