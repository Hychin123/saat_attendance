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
        Schema::create('filter_replacements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('machine_filter_id')->constrained()->cascadeOnDelete();
            $table->date('replaced_date');
            $table->unsignedBigInteger('replaced_by')->nullable()->comment('User ID of technician');
            $table->integer('old_used_liters')->default(0)->comment('Liters used when replaced');
            $table->integer('days_used')->default(0)->comment('Days since installation');
            $table->text('note')->nullable();
            $table->timestamps();
            
            // Foreign key
            $table->foreign('replaced_by')->references('id')->on('users')->onDelete('set null');
            
            // Indexes
            $table->index('machine_filter_id');
            $table->index('replaced_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('filter_replacements');
    }
};
