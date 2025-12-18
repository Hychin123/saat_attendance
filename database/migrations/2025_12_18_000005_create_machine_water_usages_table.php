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
        Schema::create('machine_water_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('machine_id')->constrained()->cascadeOnDelete();
            $table->integer('liters_dispensed')->comment('Liters dispensed in this transaction');
            $table->date('usage_date');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('machine_id');
            $table->index('usage_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('machine_water_usages');
    }
};
