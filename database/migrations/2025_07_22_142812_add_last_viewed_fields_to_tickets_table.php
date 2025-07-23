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
        Schema::table('tickets', function (Blueprint $table) {
            $table->timestamp('last_viewed_by_store_admin_at')->nullable()->after('metadata');
            $table->timestamp('last_viewed_by_super_admin_at')->nullable()->after('last_viewed_by_store_admin_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn(['last_viewed_by_store_admin_at', 'last_viewed_by_super_admin_at']);
        });
    }
};
