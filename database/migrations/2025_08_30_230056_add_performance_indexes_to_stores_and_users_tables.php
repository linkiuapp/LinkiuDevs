<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add performance indexes for validation endpoints
     */
    public function up(): void
    {
        // Add indexes to stores table for slug and email validation
        Schema::table('stores', function (Blueprint $table) {
            // Index for slug validation (if not already exists)
            if (!$this->indexExists('stores', 'stores_slug_index')) {
                $table->index('slug', 'stores_slug_index');
            }
            
            // Index for email validation (if not already exists)
            if (!$this->indexExists('stores', 'stores_email_index')) {
                $table->index('email', 'stores_email_index');
            }
            
            // Composite index for status and created_at (for analytics)
            if (!$this->indexExists('stores', 'stores_status_created_at_index')) {
                $table->index(['status', 'created_at'], 'stores_status_created_at_index');
            }
            
            // Index for plan_id (for billing calculations)
            if (!$this->indexExists('stores', 'stores_plan_id_index')) {
                $table->index('plan_id', 'stores_plan_id_index');
            }
        });

        // Add indexes to users table for email validation
        Schema::table('users', function (Blueprint $table) {
            // Index for email validation (if not already exists)
            if (!$this->indexExists('users', 'users_email_index')) {
                $table->index('email', 'users_email_index');
            }
            
            // Composite index for role and store_id (for admin lookups)
            if (!$this->indexExists('users', 'users_role_store_id_index')) {
                $table->index(['role', 'store_id'], 'users_role_store_id_index');
            }
        });

        // Add indexes to plans table for billing calculations (only if columns exist)
        if (Schema::hasColumn('plans', 'status')) {
            Schema::table('plans', function (Blueprint $table) {
                // Index for active plans
                if (!$this->indexExists('plans', 'plans_status_index')) {
                    $table->index('status', 'plans_status_index');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropIndex('stores_slug_index');
            $table->dropIndex('stores_email_index');
            $table->dropIndex('stores_status_created_at_index');
            $table->dropIndex('stores_plan_id_index');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_email_index');
            $table->dropIndex('users_role_store_id_index');
        });

        if (Schema::hasColumn('plans', 'status')) {
            Schema::table('plans', function (Blueprint $table) {
                $table->dropIndex('plans_status_index');
            });
        }
    }

    /**
     * Check if an index exists on a table
     */
    private function indexExists(string $table, string $indexName): bool
    {
        try {
            // Simple approach: try to create the index and catch the exception if it exists
            // In newer Laravel versions, we can use a different approach
            $connection = Schema::getConnection();
            $schemaBuilder = $connection->getSchemaBuilder();
            
            // For MySQL, we can check the information_schema
            if ($connection->getDriverName() === 'mysql') {
                $result = $connection->select("
                    SELECT COUNT(*) as count 
                    FROM information_schema.statistics 
                    WHERE table_schema = ? 
                    AND table_name = ? 
                    AND index_name = ?
                ", [
                    $connection->getDatabaseName(),
                    $table,
                    $indexName
                ]);
                
                return $result[0]->count > 0;
            }
            
            // For other databases, assume index doesn't exist
            return false;
        } catch (\Exception $e) {
            // If we can't check, assume it doesn't exist
            return false;
        }
    }
};
