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
            $table->dropForeign(['district_id']);
            $table->dropColumn('district_id');
            $table->dropForeign(['area_id']);
            $table->dropColumn('area_id');
            $table->dropColumn('gst');
            $table->dropColumn('contact_no');
            $table->dropColumn('address');
            $table->dropColumn('pincode');
            $table->dropColumn('route');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('gst')->nullable();
            $table->string('contact_no')->nullable();
            $table->string('address')->nullable();
            $table->string('pincode')->nullable();
            $table->foreignId('district_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('area_id')->nullable()->constrained()->onDelete('set null');
            $table->string('route')->nullable();
        });
    }
};
