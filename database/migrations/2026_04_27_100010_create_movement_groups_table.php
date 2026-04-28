<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movement_groups', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['purchase', 'sale', 'transfer', 'adjustment', 'waste']);
            $table->string('reference_doc')->nullable();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('origin_location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movement_groups');
    }
};
