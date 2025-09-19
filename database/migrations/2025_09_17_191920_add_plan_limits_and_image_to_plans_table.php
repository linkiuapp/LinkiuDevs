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
            // Solo agregar columnas que NO existen
            
            // Verificar si no existe image_url (ya existe según la lista)
            // $table->string('image_url')->nullable()->after('name'); // YA EXISTE
            
            // PRODUCTOS (max_products, max_categories, max_active_coupons ya existen)
            if (!Schema::hasColumn('plans', 'max_variables')) {
                $table->integer('max_variables')->default(15)->after('max_categories');
            }
            if (!Schema::hasColumn('plans', 'max_product_images')) {
                $table->integer('max_product_images')->default(5)->after('max_variables');
            }
            
            // DISEÑO Y MARKETING (max_active_coupons ya existe, max_slider existe pero debe ser max_sliders)
            if (!Schema::hasColumn('plans', 'max_sliders') && Schema::hasColumn('plans', 'max_slider')) {
                // Renombrar max_slider a max_sliders para consistencia
                $table->renameColumn('max_slider', 'max_sliders');
            } elseif (!Schema::hasColumn('plans', 'max_sliders')) {
                $table->integer('max_sliders')->default(3)->after('max_product_images');
            }
            
            // ENVÍOS Y LOGÍSTICA (max_delivery_zones ya existe, max_sedes existe pero debe ser max_locations)
            if (!Schema::hasColumn('plans', 'max_locations') && Schema::hasColumn('plans', 'max_sedes')) {
                // Renombrar max_sedes a max_locations
                $table->renameColumn('max_sedes', 'max_locations');
            } elseif (!Schema::hasColumn('plans', 'max_locations')) {
                $table->integer('max_locations')->default(1)->after('max_active_coupons');
            }
            
            // PAGOS
            if (!Schema::hasColumn('plans', 'max_payment_methods')) {
                $table->integer('max_payment_methods')->default(4)->after('max_delivery_zones');
            }
            if (!Schema::hasColumn('plans', 'max_bank_accounts')) {
                $table->integer('max_bank_accounts')->default(2)->after('max_payment_methods');
            }
            
            // VENTAS Y PEDIDOS
            if (!Schema::hasColumn('plans', 'order_history_months')) {
                $table->integer('order_history_months')->default(6)->after('max_bank_accounts');
            }
            
            // ADMINISTRACIÓN (max_admins ya existe)
            if (!Schema::hasColumn('plans', 'max_tickets_per_month')) {
                $table->integer('max_tickets_per_month')->default(5)->after('max_admins');
            }
            
            // support_level ya existe
            
            // ANALÍTICAS
            if (!Schema::hasColumn('plans', 'analytics_retention_days')) {
                $table->integer('analytics_retention_days')->default(30)->after('support_level');
            }
            
            // CONFIGURACIÓN (trial_days, is_featured, sort_order ya existen)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            // Solo eliminar las columnas que agregamos
            $columnsToRemove = [];
            
            if (Schema::hasColumn('plans', 'max_variables')) {
                $columnsToRemove[] = 'max_variables';
            }
            if (Schema::hasColumn('plans', 'max_product_images')) {
                $columnsToRemove[] = 'max_product_images';
            }
            if (Schema::hasColumn('plans', 'max_payment_methods')) {
                $columnsToRemove[] = 'max_payment_methods';
            }
            if (Schema::hasColumn('plans', 'max_bank_accounts')) {
                $columnsToRemove[] = 'max_bank_accounts';
            }
            if (Schema::hasColumn('plans', 'order_history_months')) {
                $columnsToRemove[] = 'order_history_months';
            }
            if (Schema::hasColumn('plans', 'max_tickets_per_month')) {
                $columnsToRemove[] = 'max_tickets_per_month';
            }
            if (Schema::hasColumn('plans', 'analytics_retention_days')) {
                $columnsToRemove[] = 'analytics_retention_days';
            }
            
            if (!empty($columnsToRemove)) {
                $table->dropColumn($columnsToRemove);
            }
            
            // Revertir renombres
            if (Schema::hasColumn('plans', 'max_sliders') && !Schema::hasColumn('plans', 'max_slider')) {
                $table->renameColumn('max_sliders', 'max_slider');
            }
            if (Schema::hasColumn('plans', 'max_locations') && !Schema::hasColumn('plans', 'max_sedes')) {
                $table->renameColumn('max_locations', 'max_sedes');
            }
        });
    }
};