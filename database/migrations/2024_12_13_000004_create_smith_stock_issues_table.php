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
        Schema::create('smith_stock_issues', function (Blueprint $table) {
            $table->id();
            $table->string('reference_no')->unique();
            $table->foreignId('user_id')->constrained()->comment('Smith user');
            $table->foreignId('warehouse_id')->constrained();
            $table->foreignId('item_id')->constrained();
            $table->decimal('quantity', 15, 2);
            $table->date('issue_date');
            $table->string('project_name')->nullable();
            $table->text('purpose')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'issued', 'rejected'])->default('pending');
            $table->foreignId('issued_by')->nullable()->constrained('users');
            $table->timestamp('issued_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('smith_stock_issues');
    }
};
