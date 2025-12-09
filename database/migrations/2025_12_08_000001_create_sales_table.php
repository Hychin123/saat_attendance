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
        Schema::create('sales', function (Blueprint $table) {
            $table->string('sale_id', 50)->primary();
            
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('agent_id')->nullable()->comment('Sale agent who gets 5% commission');
            $table->unsignedBigInteger('warehouse_id');
            
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('tax', 12, 2)->default(0);
            $table->decimal('net_total', 12, 2)->default(0);
            
            $table->decimal('deposit_amount', 12, 2)->default(0);
            $table->decimal('remaining_amount', 12, 2)->default(0);
            
            $table->date('expected_ready_date')->nullable();
            $table->date('completed_date')->nullable();
            
            $table->enum('status', [
                'PENDING',
                'DEPOSITED',
                'PROCESSING',
                'READY',
                'COMPLETED',
                'CANCELLED',
                'REFUNDED'
            ])->default('PENDING');
            
            $table->text('notes')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Foreign keys
            $table->foreign('customer_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('agent_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('cascade');
            
            // Indexes
            $table->index('status');
            $table->index('customer_id');
            $table->index('agent_id');
            $table->index('warehouse_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
