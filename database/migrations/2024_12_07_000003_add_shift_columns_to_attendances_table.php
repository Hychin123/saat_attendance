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
        Schema::table('attendances', function (Blueprint $table) {
            $table->foreignId('shift_id')->nullable()->after('role_id')->constrained()->onDelete('set null');
            $table->boolean('is_late')->default(false)->after('notes');
            $table->integer('late_minutes')->default(0)->after('is_late');
            $table->decimal('work_hours', 5, 2)->nullable()->after('late_minutes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropForeign(['shift_id']);
            $table->dropColumn(['shift_id', 'is_late', 'late_minutes', 'work_hours']);
        });
    }
};
