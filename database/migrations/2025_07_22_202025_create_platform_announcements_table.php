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
        Schema::create('platform_announcements', function (Blueprint $table) {
            $table->id();
            
            // Contenido básico
            $table->string('title');
            $table->text('content');
            $table->enum('type', ['critical', 'important', 'info'])->default('info');
            $table->integer('priority')->default(1); // 1-10 para ordenar
            
            // Banner información
            $table->string('banner_image')->nullable(); // Ruta a imagen 320x100
            $table->string('banner_link')->nullable(); // URL externa o interna
            $table->boolean('show_as_banner')->default(false);
            
            // Segmentación por planes
            $table->json('target_plans')->nullable(); // ['explorer', 'master', 'legend']
            $table->json('target_stores')->nullable(); // IDs específicas o null=todas
            
            // Fechas de vigencia
            $table->timestamp('published_at')->nullable(); // Publicación programada
            $table->timestamp('expires_at')->nullable(); // Null = permanente
            
            // Estados y comportamiento
            $table->boolean('is_active')->default(false);
            $table->boolean('show_popup')->default(false); // Modal automático
            $table->boolean('send_email')->default(false); // Email para críticos
            $table->integer('auto_mark_read_after')->nullable(); // Días para auto-marcar leído
            
            $table->timestamps();
            
            // Índices para performance
            $table->index(['is_active', 'published_at']);
            $table->index(['type', 'priority']);
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('platform_announcements');
    }
};
