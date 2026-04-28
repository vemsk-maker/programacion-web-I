<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_movements', function (Blueprint $table) {
            // For report queries filtering/sorting by product + date
            $table->index(['product_id', 'created_at'], 'idx_inv_movements_product_date');
            // group_id already has a FK index in most DBs, but add explicitly for clarity
            $table->index('group_id', 'idx_inv_movements_group');
        });

        Schema::table('batches', function (Blueprint $table) {
            // For expiration reports: filter by expiration_date quickly
            $table->index('expiration_date', 'idx_batches_expiration_date');
        });

        Schema::table('stock_cache', function (Blueprint $table) {
            // For stock report aggregations by location or product
            $table->index('location_id', 'idx_stock_cache_location');
            $table->index('product_id',  'idx_stock_cache_product');
        });
    }

    public function down(): void
    {
        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->dropIndex('idx_inv_movements_product_date');
            $table->dropIndex('idx_inv_movements_group');
        });

        Schema::table('batches', function (Blueprint $table) {
            $table->dropIndex('idx_batches_expiration_date');
        });

        Schema::table('stock_cache', function (Blueprint $table) {
            $table->dropIndex('idx_stock_cache_location');
            $table->dropIndex('idx_stock_cache_product');
        });
    }
};
