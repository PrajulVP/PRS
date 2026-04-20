<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Attendance Logs (Punch-In/Out)
        Schema::create('attendance_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['punch_in', 'punch_out']);
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->string('device_id')->nullable();
            $table->boolean('is_mock_location')->default(false);
            $table->timestamp('timestamp');
            $table->timestamps();
        });

        // 2. Continuous Location Tracking
        Schema::create('location_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->boolean('is_mock_location')->default(false);
            $table->timestamp('timestamp');
            $table->timestamps();
            
            $table->index(['user_id', 'timestamp']);
        });

        // 3. Visit Management
        Schema::create('visit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('customer_category', ['Doctor', 'Hospital', 'Retailer', 'Distributor/Wholesaler']);
            $table->string('customer_name');
            $table->unsignedBigInteger('customer_id')->nullable(); // Link to Retailer/Distributor if applicable
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->timestamp('check_in_at')->nullable();
            $table->timestamp('check_out_at')->nullable();
            $table->text('notes')->nullable();
            $table->date('next_follow_up_date')->nullable();
            $table->string('photo_path')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('visit_logs');
        Schema::dropIfExists('location_logs');
        Schema::dropIfExists('attendance_logs');
    }
};
