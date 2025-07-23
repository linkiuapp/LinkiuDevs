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
        Schema::create('product_variable_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id'); // Temporalmente sin foreign key
            $table->foreignId('variable_id')->constrained('product_variables')->onDelete('cascade');
            $table->boolean('is_required')->default(false);
            $table->string('custom_label')->nullable(); // Etiqueta personalizada por producto
            $table->integer('display_order')->default(0); // Orden específico por producto
            $table->string('group_name')->nullable(); // Agrupación visual
            $table->integer('group_order')->default(0); // Orden del grupo
            $table->timestamps();
            
            // Índices
            $table->unique(['product_id', 'variable_id']); // Una variable por producto
            $table->index(['product_id', 'display_order']);
            $table->index(['product_id', 'group_name']);
            
            // TODO: Agregar foreign key cuando se implemente la tabla products
            // $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variable_assignments');
    }
};
