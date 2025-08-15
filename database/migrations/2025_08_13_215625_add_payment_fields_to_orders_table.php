<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Agregar payment_method_id
            $table->foreignId('payment_method_id')->nullable()->after('payment_method');
            
            // Agregar cash_amount para pagos en efectivo
            $table->decimal('cash_amount', 10, 2)->nullable()->after('payment_proof_path');
            
            // Hacer columnas address opcionales para pickup
            $table->text('customer_address')->nullable()->change();
            $table->string('department')->nullable()->change();
            $table->string('city')->nullable()->change();
        });
        
        // Actualizar enum de payment_method para incluir nuevos valores
        DB::statement("ALTER TABLE orders MODIFY COLUMN payment_method ENUM('transferencia', 'contra_entrega', 'efectivo', 'cash', 'bank_transfer', 'card_terminal') DEFAULT 'cash'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['payment_method_id', 'cash_amount']);
            
            // Revertir columnas a requeridas
            $table->text('customer_address')->nullable(false)->change();
            $table->string('department')->nullable(false)->change();
            $table->string('city')->nullable(false)->change();
        });
        
        // Revertir enum de payment_method
        DB::statement("ALTER TABLE orders MODIFY COLUMN payment_method ENUM('transferencia', 'contra_entrega', 'efectivo') DEFAULT 'efectivo'");
    }
};
