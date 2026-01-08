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
        Schema::create('distributors', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique();
            $table->foreignId('sales_manager_id')->nullable()->constrained('sales_managers')->onDelete('set null');
            $table->string('name');
            $table->string('gst')->nullable();
            $table->string('drug_license_no')->nullable();
            $table->string('contact_no')->nullable();
            $table->string('address')->nullable();
            $table->string('pincode')->unique()->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->unsignedBigInteger('district_id')->nullable();
            $table->unsignedBigInteger('area_id')->nullable();
            $table->timestamps();

            $table->index('district_id');
            $table->index('area_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('distributors');
    }
};
