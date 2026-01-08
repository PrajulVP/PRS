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
        Schema::create('retailers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('field_staff_id')->nullable()->constrained('fieldstaffs')->onDelete('set null');
            $table->foreignId('sales_manager_id')->nullable()->constrained('sales_managers')->onDelete('set null');
            $table->foreignId('distributor_id')->nullable()->constrained('distributors')->onDelete('set null');
            $table->string('gst')->nullable();
            $table->string('contact_no')->nullable();
            $table->text('address')->nullable();
            $table->string('pincode');
            $table->decimal('credit_limit', 10, 2)->default(0);
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('retailers');
    }
};
