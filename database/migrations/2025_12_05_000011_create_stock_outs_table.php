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
        Schema::create('stock_outs', function (Blueprint $table) {
            $table->id();
            $table->string('reference_no')->unique(); // SO-2025-001
            $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
            $table->string('customer_department')->nullable(); // Customer or Department name
            $table->date('dispatch_date');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('issued_by')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['PENDING', 'APPROVED', 'DISPATCHED', 'CANCELLED'])->default('PENDING');
            $table->text('reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_outs');
    }
};
