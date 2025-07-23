<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Explorer, Master, Legend
            $table->text('description')->nullable();
            $table->boolean('allow_custom_slug')->default(false);
            $table->decimal('price', 10, 2)->default(0);
            $table->string('currency')->default('COP');
            $table->integer('duration_in_days');
            
            // Límites del plan
            $table->integer('max_products');
            $table->integer('max_slider');
            $table->integer('max_active_promotions');
            $table->integer('max_active_coupons');
            $table->integer('max_categories');
            $table->integer('max_sedes');
            
            // Soporte
            $table->enum('support_level', ['basic', 'priority', 'premium'])->default('basic');
            $table->integer('support_response_time'); // en horas
            
            $table->boolean('is_active')->default(true);
            $table->boolean('is_public')->default(true);
            $table->integer('trial_days')->default(0);
            $table->json('additional_features')->nullable();
            $table->string('version')->default('1.0');
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
}; 