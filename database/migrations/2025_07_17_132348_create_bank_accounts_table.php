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
        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_method_id')->constrained('payment_methods')->onDelete('cascade'); // ID del método de pago (FK)
            $table->string('bank_name'); // Nombre del banco
            $table->string('account_type'); // Tipo de cuenta (ahorros, corriente)
            $table->string('account_number'); // Número de cuenta
            $table->string('account_holder'); // Titular de la cuenta
            $table->string('document_number')->nullable(); // Número de documento del titular (opcional)
            $table->boolean('is_active')->default(true); // Estado activo/inactivo
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade'); // ID de la tienda (FK)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_accounts');
    }
};
