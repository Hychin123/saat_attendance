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
        Schema::create('user_shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('shift_id')->constrained()->onDelete('cascade');
            $table->date('effective_from'); // When this shift assignment starts
            $table->date('effective_to')->nullable(); // When this shift assignment ends (null = ongoing)
            $table->boolean('is_primary')->default(true); // Is this the user's primary shift
            $table->timestamps();
            
            // Prevent duplicate active assignments
            $table->index(['user_id', 'shift_id', 'effective_from']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_shifts');
    }
};
