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
            $table->foreignId('distributor_id')->nullable()->constrained('distributors')->onDelete('set null');
            $table->foreignId('field_staff_id')->nullable()->constrained('fieldstaffs')->onDelete('set null');
            $table->foreignId('district_id')->constrained('districts')->onDelete('cascade');
            $table->foreignId('area_id')->constrained('areas')->onDelete('cascade');
            $table->string('proprietor_name')->nullable();
            $table->string('contact_no')->nullable();
            $table->string('gst')->nullable();
            $table->text('address')->nullable();
            $table->string('status')->default('active');
            $table->decimal('credit_limit', 10, 2)->default(0);
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
