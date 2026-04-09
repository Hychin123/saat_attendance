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
        Schema::create('smith_returns', function (Blueprint $table) {
            $table->id();
            $table->string('reference_no')->unique();
            $table->foreignId('user_id')->constrained()->comment('Smith user');
            $table->foreignId('warehouse_id')->constrained();
            $table->foreignId('item_id')->constrained()->comment('Defective item');
            $table->decimal('quantity', 15, 2);
            $table->foreignId('replacement_item_id')->nullable()->constrained('items')->comment('New replacement item');
            $table->decimal('replacement_quantity', 15, 2)->nullable();
            $table->date('return_date');
            $table->enum('return_reason', ['defective', 'damaged', 'wrong_item', 'quality_issue', 'other']);
            $table->text('description');
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'completed'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users');
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('smith_returns');
    }
};
