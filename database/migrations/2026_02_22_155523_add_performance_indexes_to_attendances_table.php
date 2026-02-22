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
            // Add index on date column for date-based queries (today, this week, this month, etc.)
            $table->index('date', 'idx_attendances_date');
            
            // Add composite index for user_id and date for efficient filtering
            $table->index(['user_id', 'date'], 'idx_attendances_user_date');
            
            // Add index on role_id for role-based filtering
            $table->index('role_id', 'idx_attendances_role_id');
            
            // Add index on created_at for sorting/filtering by creation time
            $table->index('created_at', 'idx_attendances_created_at');
            
            // Add index on time_out for finding unchecked out records
            $table->index('time_out', 'idx_attendances_time_out');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropIndex('idx_attendances_date');
            $table->dropIndex('idx_attendances_user_date');
            $table->dropIndex('idx_attendances_role_id');
            $table->dropIndex('idx_attendances_created_at');
            $table->dropIndex('idx_attendances_time_out');
        });
    }
};
