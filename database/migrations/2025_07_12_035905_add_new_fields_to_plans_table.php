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
        Schema::table('plans', function (Blueprint $table) {
            // Precios por período (monthly, quarterly, semester)
            $table->json('prices')->nullable()->after('price');
            
            // Lista de características para mostrar en UI
            $table->json('features_list')->nullable()->after('additional_features');
            
            // Para destacar un plan como "Más popular"
            $table->boolean('is_featured')->default(false)->after('is_public');
            
            // Para ordenar los planes
            $table->integer('sort_order')->default(0)->after('is_featured');
            
            // Límite de usuarios administradores
            $table->integer('max_admins')->default(1)->after('max_sedes');
            
            // Zonas de reparto
            $table->integer('max_delivery_zones')->default(1)->after('max_admins');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn([
                'prices',
                'features_list',
                'is_featured',
                'sort_order',
                'max_admins',
                'max_delivery_zones'
            ]);
        });
    }
};
