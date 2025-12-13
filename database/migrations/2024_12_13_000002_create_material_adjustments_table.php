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
        Schema::create('material_adjustments', function (Blueprint $table) {
            $table->id();
            $table->string('reference_no')->unique();
            $table->foreignId('user_id')->constrained()->comment('Smith user');
            $table->foreignId('warehouse_id')->constrained();
            $table->foreignId('item_id')->constrained();
            $table->enum('adjustment_type', ['add', 'subtract']);
            $table->decimal('quantity', 15, 2);
            $table->decimal('previous_quantity', 15, 2);
            $table->decimal('new_quantity', 15, 2);
            $table->date('adjustment_date');
            $table->text('reason');
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_adjustments');
    }
};
