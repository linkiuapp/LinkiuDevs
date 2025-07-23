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
        Schema::create('store_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('manager_name')->nullable();
            $table->string('phone', 20);
            $table->string('whatsapp', 20)->nullable();
            $table->string('department', 100);
            $table->string('city', 100);
            $table->text('address');
            $table->boolean('is_main')->default(false);
            $table->boolean('is_active')->default(true);
            $table->text('whatsapp_message')->nullable();
            $table->unsignedInteger('whatsapp_clicks')->default(0);
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['store_id', 'is_active']);
            $table->index(['store_id', 'is_main']);
            
            // Unique constraint for name per store
            $table->unique(['store_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_locations');
    }
};