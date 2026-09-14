<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('hospitals_clinics')) {
            Schema::create('hospitals_clinics', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('address')->nullable();
                $table->foreignId('district_id')->nullable()->constrained('districts')->onDelete('set null');
                $table->foreignId('area_id')->nullable()->constrained('areas')->onDelete('set null');
                $table->decimal('latitude', 10, 8)->nullable();
                $table->decimal('longitude', 11, 8)->nullable();
                $table->boolean('location_locked')->default(false);
                $table->timestamp('location_updated_at')->nullable();
                $table->enum('status', ['active', 'inactive'])->default('active');
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // Also ensure field_visits party_type enum supports hospital
        DB::statement("ALTER TABLE field_visits MODIFY COLUMN party_type ENUM('retailer', 'distributor', 'hospital', 'other') DEFAULT 'retailer'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hospitals_clinics');
    }
};
