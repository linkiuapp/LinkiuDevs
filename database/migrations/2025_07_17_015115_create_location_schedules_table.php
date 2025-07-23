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
        Schema::create('location_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('location_id')->constrained('store_locations')->onDelete('cascade');
            $table->tinyInteger('day_of_week'); // 0=Sunday, 1=Monday, ..., 6=Saturday
            $table->boolean('is_closed')->default(false);
            $table->time('open_time_1')->nullable();
            $table->time('close_time_1')->nullable();
            $table->time('open_time_2')->nullable(); // Optional additional schedule
            $table->time('close_time_2')->nullable();
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['location_id', 'day_of_week']);
            
            // Unique constraint for day per location
            $table->unique(['location_id', 'day_of_week']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('location_schedules');
    }
};