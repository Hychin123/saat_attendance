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
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "Morning Shift", "Night Shift"
            $table->string('code')->unique(); // e.g., "MS", "NS"
            $table->time('start_time'); // Shift start time
            $table->time('end_time'); // Shift end time
            $table->integer('grace_period_minutes')->default(15); // Late tolerance in minutes
            $table->integer('minimum_work_hours')->default(8); // Minimum required work hours
            $table->boolean('is_active')->default(true);
            $table->boolean('is_overnight')->default(false); // For shifts that span midnight
            $table->text('description')->nullable();
            $table->json('working_days')->nullable(); // ['monday', 'tuesday', etc.]
            $table->string('color')->nullable(); // For UI display (hex color)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shifts');
    }
};
