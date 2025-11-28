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
        Schema::table('distributor_orders', function (Blueprint $table) {
            // Add sales_manager_id
            $table->foreignId('sales_manager_id')->nullable()->constrained('sales_managers')->onDelete('set null');

            // Add cancellation_reason
            $table->text('cancellation_reason')->nullable();

            // Modify status column (ENUM)
            // This is a bit tricky with ENUMs in SQLite (used for testing sometimes) and older MySQL versions.
            // A more robust way involves dropping and re-adding, or using DB raw statements.
            // For now, I'll attempt a direct change, which works in newer MySQL/PostgreSQL.
            $table->enum('status', [
                'pending',
                'accepted_by_sales_manager',
                'delivered', // Admin accepted
                'cancelled',
                'cancellation_requested'
            ])->default('pending')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('distributor_orders', function (Blueprint $table) {
            // Revert status column
            // This assumes original enum values can be restored without data loss
            $table->enum('status', ['pending', 'accepted', 'dispatched', 'delivered'])->default('pending')->change();

            // Drop added columns
            $table->dropForeign(['sales_manager_id']);
            $table->dropColumn('sales_manager_id');
            $table->dropColumn('cancellation_reason');
        });
    }
};