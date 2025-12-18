<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'pgsql') {
            // PostgreSQL syntax
            DB::statement('ALTER TABLE notifications ALTER COLUMN data TYPE JSON USING data::json');
        } else {
            // MySQL/MariaDB syntax
            DB::statement('ALTER TABLE notifications MODIFY data JSON');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'pgsql') {
            // PostgreSQL syntax
            DB::statement('ALTER TABLE notifications ALTER COLUMN data TYPE TEXT');
        } else {
            // MySQL/MariaDB syntax
            DB::statement('ALTER TABLE notifications MODIFY data TEXT');
        }
    }
};
