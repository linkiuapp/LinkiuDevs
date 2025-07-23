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
        Schema::create('email_configurations', function (Blueprint $table) {
            $table->id();
            
            // Configuración SMTP
            $table->string('smtp_host')->nullable();
            $table->integer('smtp_port')->default(587);
            $table->string('smtp_username')->nullable();
            $table->string('smtp_password')->nullable();
            $table->enum('smtp_encryption', ['tls', 'ssl', 'none'])->default('tls');
            
            // Configuración del remitente
            $table->string('from_email')->nullable();
            $table->string('from_name')->default('Linkiu.bio Support');
            
            // Configuración de templates
            $table->text('ticket_created_template')->nullable();
            $table->text('ticket_response_template')->nullable();
            $table->text('ticket_status_changed_template')->nullable();
            $table->text('ticket_assigned_template')->nullable();
            
            // Configuración de eventos (qué emails enviar)
            $table->boolean('send_on_ticket_created')->default(true);
            $table->boolean('send_on_ticket_response')->default(true);
            $table->boolean('send_on_status_change')->default(true);
            $table->boolean('send_on_ticket_assigned')->default(false);
            
            // Estado de la configuración
            $table->boolean('is_active')->default(false);
            $table->timestamp('last_test_at')->nullable();
            $table->text('last_test_result')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_configurations');
    }
};
