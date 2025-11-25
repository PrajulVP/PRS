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

            // MUST be unique (matches your SQL: UNIQUE KEY distributors_user_id_unique)
            $table->unsignedBigInteger('user_id')->unique();
            $table->string('name');
            $table->string('gst')->nullable();
            $table->string('drug_license_no')->nullable();
            $table->string('contact_no')->nullable();
            $table->string('address')->nullable();
            $table->string('pincode')->unique()->nullable();

            $table->unsignedBigInteger('district_id')->nullable();
            $table->unsignedBigInteger('area_id')->nullable();

            $table->timestamps();

            // Indexes (as in your SQL)
            $table->index('district_id');
            $table->index('area_id');

            // IMPORTANT:
            // Your MySQL table uses MyISAM → foreign keys NOT supported.
            // Do NOT add ->constrained() or foreign key references.
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
