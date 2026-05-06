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
        Schema::table('return_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('distributor_id')->nullable()->after('user_id');
            $table->unsignedBigInteger('field_staff_id')->nullable()->after('distributor_id');
            $table->unsignedBigInteger('sales_manager_id')->nullable()->after('field_staff_id');

            // Optional: Foreign keys if users/distributors tables exist
            // $table->foreign('distributor_id')->references('id')->on('distributors')->onDelete('set null');
            // $table->foreign('field_staff_id')->references('id')->on('field_staffs')->onDelete('set null');
            // $table->foreign('sales_manager_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('return_requests', function (Blueprint $table) {
            $table->dropColumn(['distributor_id', 'field_staff_id', 'sales_manager_id']);
        });
    }
};
