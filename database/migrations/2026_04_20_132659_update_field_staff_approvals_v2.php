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
        // Update leave_requests table
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('manager_id')->nullable()->after('reason');
            $table->unsignedBigInteger('admin_id')->nullable()->after('manager_id');
            // We can't easily change enum in some DBs without raw SQL, but we'll try standard way
            // or just change it to string for flexibility
            $table->string('status')->default('pending')->change();
        });

        // Update expenses table
        Schema::table('expenses', function (Blueprint $table) {
            $table->string('status')->default('pending')->change();
        });

        // Ensure users has the device binding fields if not exists (redundancy check)
        if (!Schema::hasColumn('users', 'device_uuid')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('device_uuid')->nullable()->after('player_id');
                $table->timestamp('device_bound_at')->nullable()->after('device_uuid');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropColumn(['manager_id', 'admin_id']);
        });

        // Reverting enum change is complex, keeping it as string is usually fine in down
    }
};
