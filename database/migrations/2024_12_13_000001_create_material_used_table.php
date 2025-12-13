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
        Schema::create('material_used', function (Blueprint $table) {
            $table->id();
            $table->string('reference_no')->unique();
            $table->foreignId('user_id')->constrained()->comment('Smith user');
            $table->foreignId('warehouse_id')->constrained();
            $table->foreignId('item_id')->constrained();
            $table->decimal('quantity', 15, 2);
            $table->string('unit')->nullable();
            $table->date('usage_date');
            $table->string('project_name')->nullable();
            $table->text('purpose')->nullable();
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
        Schema::dropIfExists('material_used');
    }
};
