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
        Schema::table('users', function (Blueprint $table) {
            $table->integer('age')->nullable()->after('name');
            $table->string('school')->nullable()->after('age');
            $table->foreignId('role_id')->nullable()->after('school')->constrained()->onDelete('set null');
            $table->decimal('salary', 10, 2)->nullable()->after('role_id');
            $table->string('kpa')->nullable()->after('salary')->comment('Key Performance Area');
            $table->string('phone')->nullable()->after('kpa');
            $table->string('profile_image')->nullable()->after('phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropColumn([
                'age',
                'school',
                'role_id',
                'salary',
                'kpa',
                'phone',
                'profile_image'
            ]);
        });
    }
};
