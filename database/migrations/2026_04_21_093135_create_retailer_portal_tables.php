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
        // 1. Offers Table
        Schema::create('offers', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        // 2. Loyalty Slabs/Gifts Table
        Schema::create('loyalty_slabs', function (Blueprint $table) {
            $table->id();
            $table->string('slab_name');
            $table->integer('min_points');
            $table->string('gift_name');
            $table->string('gift_image')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 3. Patients Table (for Chemist Patient Database)
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('retailer_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('contact')->nullable();
            $table->text('medication_history')->nullable();
            $table->enum('category', ['acute', 'chronic'])->default('acute');
            $table->date('next_reorder_date')->nullable();
            $table->timestamps();
        });

        // 4. Loyalty Redemptions Table
        Schema::create('loyalty_redemptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('retailer_id')->constrained()->onDelete('cascade');
            $table->foreignId('loyalty_slab_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['pending', 'approved', 'delivered', 'rejected'])->default('pending');
            $table->date('expected_delivery_date')->nullable();
            $table->string('gift_receipt_photo')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loyalty_redemptions');
        Schema::dropIfExists('patients');
        Schema::dropIfExists('loyalty_slabs');
        Schema::dropIfExists('offers');
    }
};
