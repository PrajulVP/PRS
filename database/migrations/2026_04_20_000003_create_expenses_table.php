<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('type'); // Travel, Food, Stay, Miscellaneous
            $table->decimal('amount', 12, 2);
            $table->decimal('distance_km', 10, 2)->nullable(); // For travel/KM logic
            $table->string('bill_path')->nullable();
            $table->boolean('is_outstation')->default(false);
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('rejection_reason')->nullable();
            
            $table->unsignedBigInteger('manager_id')->nullable(); // Approved/Rejected by
            $table->unsignedBigInteger('admin_id')->nullable();   // Final approval
            
            $table->timestamp('expense_date');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('expenses');
    }
};
