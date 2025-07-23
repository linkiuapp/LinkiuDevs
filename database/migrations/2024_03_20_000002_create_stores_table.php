<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id')->nullable(); // Para futura implementación multi-tenant
            $table->foreignId('plan_id')->constrained();
            
            // Información básica
            $table->string('name');
            $table->string('slug')->unique();
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->boolean('verified')->default(false);
            
            // Datos de contacto y ubicación
            $table->string('document_type')->nullable(); // NIT o Cédula
            $table->string('document_number')->nullable();
            $table->string('phone')->nullable();
            $table->string('email');
            $table->string('address')->nullable();
            $table->string('country')->nullable();
            $table->string('department')->nullable();
            $table->string('city')->nullable();
            
            // Información detallada
            $table->text('description')->nullable();
            $table->text('privacy_policy_text')->nullable();
            $table->text('shipping_policy_text')->nullable();
            
            // Personalización del header
            $table->string('logo_url')->nullable();
            $table->string('header_background_color')->default('#FFFFFF');
            $table->string('header_text_title')->nullable();
            $table->string('header_text_color')->default('#000000');
            $table->string('header_short_description')->nullable();
            $table->string('header_short_description_color')->default('#000000');
            
            // Meta información
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();
            
            // Timestamps
            $table->timestamps();
            $table->timestamp('last_active_at')->nullable();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stores');
    }
}; 