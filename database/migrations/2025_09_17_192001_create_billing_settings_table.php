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
        Schema::create('billing_settings', function (Blueprint $table) {
            $table->id();
            $table->string('logo_url')->nullable();
            $table->string('company_name')->default('Linkiu Technologies');
            $table->text('company_address')->nullable();
            $table->string('tax_id')->default('123456789-0');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('footer_text')->nullable();
            $table->string('currency', 3)->default('COP');
            $table->string('country', 2)->default('CO');
            $table->timestamps();
        });
        
        // Insertar configuración por defecto
        DB::table('billing_settings')->insert([
            'company_name' => 'Linkiu Technologies',
            'company_address' => 'Sincelejo, Sucre, Colombia',
            'tax_id' => '123456789-0',
            'email' => 'facturacion@linkiu.bio',
            'phone' => '+57 300 123 4567',
            'footer_text' => 'Gracias por confiar en Linkiu para hacer crecer tu negocio online.',
            'currency' => 'COP',
            'country' => 'CO',
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('billing_settings');
    }
};