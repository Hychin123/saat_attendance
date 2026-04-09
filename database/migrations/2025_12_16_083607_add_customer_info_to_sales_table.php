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
        Schema::table('sales', function (Blueprint $table) {
            $table->string('customer_name')->nullable()->after('customer_id');
            $table->enum('customer_gender', ['Male', 'Female', 'Other'])->nullable()->after('customer_name');
            $table->string('customer_phone', 20)->nullable()->after('customer_gender');
            $table->string('customer_location')->nullable()->after('customer_phone');
            $table->string('location_product')->nullable()->after('customer_location');
            $table->string('water_filter_cabinet_id')->nullable()->after('location_product');
            $table->string('board_id')->nullable()->after('water_filter_cabinet_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn([
                'customer_name',
                'customer_gender',
                'customer_phone',
                'customer_location',
                'location_product',
                'water_filter_cabinet_id',
                'board_id'
            ]);
        });
    }
};
