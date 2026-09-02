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
        if (!\Illuminate\Support\Facades\Schema::hasColumn('products', 'brand')) {
            Schema::table('products', function (Blueprint $table) {
                $table->string('brand')->nullable()->after('product_name');
            });
        } else {
            \Illuminate\Support\Facades\DB::statement('ALTER TABLE products MODIFY COLUMN brand VARCHAR(191) AFTER product_name');
        }

        // Auto-fill existing rows based on brand_id
        \Illuminate\Support\Facades\DB::statement('
            UPDATE products p
            JOIN brands b ON p.brand_id = b.id
            SET p.brand = b.name
            WHERE p.brand IS NULL OR p.brand = ""
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('brand');
        });
    }
};
