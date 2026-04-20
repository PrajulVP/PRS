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
        Schema::table('inventories', function (Blueprint $table) {
            $table->string('side')->nullable()->after('variant');
            $table->string('size')->nullable()->after('side');
        });

        Schema::table('retailer_order_items', function (Blueprint $table) {
            $table->string('side')->nullable()->after('variant');
            $table->string('size')->nullable()->after('side');
        });

        Schema::table('distributor_order_items', function (Blueprint $table) {
            $table->string('side')->nullable()->after('variant');
            $table->string('size')->nullable()->after('side');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventories', function (Blueprint $table) {
            $table->dropColumn(['side', 'size']);
        });

        Schema::table('retailer_order_items', function (Blueprint $table) {
            $table->dropColumn(['side', 'size']);
        });

        Schema::table('distributor_order_items', function (Blueprint $table) {
            $table->dropColumn(['side', 'size']);
        });
    }
};
