<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!\Illuminate\Support\Facades\Schema::hasColumn('products', 'brand_id')) {
            \Illuminate\Support\Facades\Schema::table('products', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->unsignedBigInteger('brand_id')->nullable()->after('product_name');
            });
        } else {
            // Use raw SQL to reorder the column without doctrine/dbal requirement
            DB::statement('ALTER TABLE products MODIFY COLUMN brand_id BIGINT UNSIGNED AFTER product_name');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to end of table (assuming created_at/updated_at are last normally, or just leave it)
        DB::statement('ALTER TABLE products MODIFY COLUMN brand_id BIGINT UNSIGNED AFTER updated_at');
    }
};
