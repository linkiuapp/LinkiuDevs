<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('store_policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->onDelete('cascade');
            $table->text('privacy_policy')->nullable();
            $table->text('terms_conditions')->nullable();
            $table->text('shipping_policy')->nullable();
            $table->text('return_policy')->nullable();
            $table->text('about_us')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_policies');
    }
};
