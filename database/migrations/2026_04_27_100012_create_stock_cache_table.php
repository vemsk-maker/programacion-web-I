<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_cache', function (Blueprint $table) {
            $table->id();
            $table->foreignId('location_id')->constrained('locations')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('batch_id')->nullable()->constrained('batches')->nullOnDelete();
            $table->decimal('quantity', 10, 2)->default(0);
        });

        // Partial unique index for rows WITHOUT a batch (batch_id IS NULL)
        DB::statement(
            'CREATE UNIQUE INDEX stock_cache_no_batch_unique
             ON stock_cache (location_id, product_id)
             WHERE batch_id IS NULL'
        );

        // Partial unique index for rows WITH a batch (batch_id IS NOT NULL)
        DB::statement(
            'CREATE UNIQUE INDEX stock_cache_with_batch_unique
             ON stock_cache (location_id, product_id, batch_id)
             WHERE batch_id IS NOT NULL'
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_cache');
    }
};
