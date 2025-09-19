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
        // Ejecutar directamente con SQL para evitar problemas de Laravel
        try {
            // Verificar y renombrar max_slider a max_sliders
            if (Schema::hasColumn('plans', 'max_slider') && !Schema::hasColumn('plans', 'max_sliders')) {
                DB::statement('ALTER TABLE plans CHANGE max_slider max_sliders INT(11) NOT NULL DEFAULT 1');
                echo "✅ Campo max_slider renombrado a max_sliders\n";
            } elseif (!Schema::hasColumn('plans', 'max_sliders')) {
                Schema::table('plans', function (Blueprint $table) {
                    $table->integer('max_sliders')->default(1)->after('max_categories');
                });
                echo "✅ Campo max_sliders creado\n";
            }
            
            // Verificar y renombrar max_sedes a max_locations
            if (Schema::hasColumn('plans', 'max_sedes') && !Schema::hasColumn('plans', 'max_locations')) {
                DB::statement('ALTER TABLE plans CHANGE max_sedes max_locations INT(11) NOT NULL DEFAULT 1');
                echo "✅ Campo max_sedes renombrado a max_locations\n";
            } elseif (!Schema::hasColumn('plans', 'max_locations')) {
                Schema::table('plans', function (Blueprint $table) {
                    $table->integer('max_locations')->default(1)->after('max_delivery_zones');
                });
                echo "✅ Campo max_locations creado\n";
            }
            
            // Agregar campos faltantes si no existen
            Schema::table('plans', function (Blueprint $table) {
                if (!Schema::hasColumn('plans', 'max_variables')) {
                    $table->integer('max_variables')->default(15)->after('max_categories');
                    echo "✅ Campo max_variables creado\n";
                }
                
                if (!Schema::hasColumn('plans', 'max_product_images')) {
                    $table->integer('max_product_images')->default(5)->after('max_variables');
                    echo "✅ Campo max_product_images creado\n";
                }
                
                if (!Schema::hasColumn('plans', 'max_payment_methods')) {
                    $table->integer('max_payment_methods')->default(4)->after('max_delivery_zones');
                    echo "✅ Campo max_payment_methods creado\n";
                }
                
                if (!Schema::hasColumn('plans', 'max_bank_accounts')) {
                    $table->integer('max_bank_accounts')->default(2)->after('max_payment_methods');
                    echo "✅ Campo max_bank_accounts creado\n";
                }
                
                if (!Schema::hasColumn('plans', 'order_history_months')) {
                    $table->integer('order_history_months')->default(6)->after('max_bank_accounts');
                    echo "✅ Campo order_history_months creado\n";
                }
                
                if (!Schema::hasColumn('plans', 'max_tickets_per_month')) {
                    $table->integer('max_tickets_per_month')->default(5)->after('max_admins');
                    echo "✅ Campo max_tickets_per_month creado\n";
                }
                
                if (!Schema::hasColumn('plans', 'analytics_retention_days')) {
                    $table->integer('analytics_retention_days')->default(30)->after('support_response_time');
                    echo "✅ Campo analytics_retention_days creado\n";
                }
                
                if (!Schema::hasColumn('plans', 'image_url')) {
                    $table->string('image_url')->nullable()->after('name');
                    echo "✅ Campo image_url creado\n";
                }
            });
            
        } catch (Exception $e) {
            echo "❌ Error en migración: " . $e->getMessage() . "\n";
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir cambios
        try {
            if (Schema::hasColumn('plans', 'max_sliders') && !Schema::hasColumn('plans', 'max_slider')) {
                DB::statement('ALTER TABLE plans CHANGE max_sliders max_slider INT(11) NOT NULL DEFAULT 1');
            }
            
            if (Schema::hasColumn('plans', 'max_locations') && !Schema::hasColumn('plans', 'max_sedes')) {
                DB::statement('ALTER TABLE plans CHANGE max_locations max_sedes INT(11) NOT NULL DEFAULT 1');
            }
            
            Schema::table('plans', function (Blueprint $table) {
                $columnsToRemove = ['max_variables', 'max_product_images', 'max_payment_methods', 
                                   'max_bank_accounts', 'order_history_months', 'max_tickets_per_month', 
                                   'analytics_retention_days', 'image_url'];
                
                foreach ($columnsToRemove as $column) {
                    if (Schema::hasColumn('plans', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        } catch (Exception $e) {
            echo "❌ Error revirtiendo migración: " . $e->getMessage() . "\n";
        }
    }
};