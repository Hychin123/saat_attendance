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
        Schema::create('commissions', function (Blueprint $table) {
            $table->id('commission_id');
            $table->string('sale_id', 50);
            $table->unsignedBigInteger('agent_id');
            
            $table->decimal('commission_rate', 5, 2)->default(5.00)->comment('Commission percentage');
            $table->decimal('total_sale_amount', 12, 2)->default(0);
            $table->decimal('commission_amount', 12, 2)->default(0);
            
            $table->enum('status', ['PENDING', 'PAID', 'CANCELLED'])->default('PENDING');
            
            $table->date('paid_date')->nullable();
            $table->string('payment_reference')->nullable();
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('sale_id')->references('sale_id')->on('sales')->onDelete('cascade');
            $table->foreign('agent_id')->references('id')->on('users')->onDelete('cascade');
            
            // Indexes
            $table->index('sale_id');
            $table->index('agent_id');
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commissions');
    }
};
