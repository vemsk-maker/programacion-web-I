<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('movement_group_id')->constrained('movement_groups')->cascadeOnDelete();
            $table->enum('document_type', ['receipt', 'order', 'transfer_note']);
            $table->string('doc_number');
            $table->string('client_name')->nullable();
            $table->string('client_nit')->nullable();
            $table->decimal('total_amount', 10, 2)->nullable();
            $table->enum('status', ['open', 'closed', 'cancelled']);
            $table->timestamp('printed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
