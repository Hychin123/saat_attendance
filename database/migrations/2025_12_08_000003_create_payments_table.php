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
        Schema::create('payments', function (Blueprint $table) {
            $table->id('payment_id');
            $table->string('sale_id', 50);
            
            $table->decimal('amount', 12, 2)->default(0);
            
            $table->enum('payment_type', ['DEPOSIT', 'BALANCE', 'FULL'])->default('DEPOSIT');
            $table->enum('payment_method', ['CASH', 'BANK', 'QR', 'CREDIT_CARD', 'OTHER'])->default('CASH');
            
            $table->unsignedBigInteger('paid_by')->nullable()->comment('User who received payment');
            $table->string('transaction_reference')->nullable();
            $table->text('notes')->nullable();
            
            $table->timestamp('payment_date')->useCurrent();
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('sale_id')->references('sale_id')->on('sales')->onDelete('cascade');
            $table->foreign('paid_by')->references('id')->on('users')->onDelete('set null');
            
            // Indexes
            $table->index('sale_id');
            $table->index('payment_type');
            $table->index('payment_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
