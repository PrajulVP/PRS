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
        Schema::create('incentive_slabs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('min_achievement_percent', 5, 2); // e.g., 90.00
            $table->decimal('max_achievement_percent', 5, 2)->nullable();
            $table->decimal('incentive_percent', 5, 2); // e.g., 5.00
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incentive_slabs');
    }
};
