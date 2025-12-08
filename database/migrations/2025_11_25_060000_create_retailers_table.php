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
            $table->foreignId('field_staff_id')->constrained('fieldstaffs')->onDelete('cascade');
            $table->foreignId('sales_manager_id')->constrained('sales_managers')->onDelete('cascade');
            $table->foreignId('distributor_id')->constrained('distributors')->onDelete('cascade');
            $table->string('proprietor_name')->nullable();
            $table->string('gst')->nullable();
            $table->string('contact_no')->nullable();
            $table->text('address')->nullable();
            $table->string('pincode');
            $table->decimal('credit_limit', 10, 2)->default(0);
            $table->enum('status', ['active', 'inactive'])->default('active');
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
