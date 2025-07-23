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
        Schema::table('store_designs', function (Blueprint $table) {
            // Verificamos si las columnas existen antes de intentar eliminarlas
            if (Schema::hasColumn('store_designs', 'header_gradient_start_color')) {
                $table->dropColumn('header_gradient_start_color');
            }
            if (Schema::hasColumn('store_designs', 'header_gradient_end_color')) {
                $table->dropColumn('header_gradient_end_color');
            }
            if (Schema::hasColumn('store_designs', 'header_gradient_direction')) {
                $table->dropColumn('header_gradient_direction');
            }
            if (Schema::hasColumn('store_designs', 'header_background_type')) {
                $table->dropColumn('header_background_type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('store_designs', function (Blueprint $table) {
            // Restauramos las columnas en caso de rollback
            $table->enum('header_background_type', ['solid'])->default('solid');
            $table->string('header_gradient_start_color', 20)->nullable();
            $table->string('header_gradient_end_color', 20)->nullable();
            $table->string('header_gradient_direction', 20)->default('to-r');
        });
    }
};
