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
        Schema::create('filters', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Sediment, Carbon, RO, UV, etc.
            $table->string('code')->unique(); // FILT-001
            $table->text('description')->nullable();
            $table->integer('max_liters')->nullable()->comment('Maximum liters before replacement (e.g., 10000)');
            $table->integer('max_days')->nullable()->comment('Maximum days before replacement (e.g., 180)');
            $table->integer('position')->nullable()->comment('Filter position in the system (1-7)');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('filters');
    }
};
