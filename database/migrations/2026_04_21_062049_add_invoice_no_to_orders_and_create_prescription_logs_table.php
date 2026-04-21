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
            $table->string('invoice_no')->nullable()->after('invoice_path');
            $table->unique(['distributor_id', 'invoice_no'], 'dist_order_inv_unique');
        });

        Schema::table('retailer_orders', function (Blueprint $table) {
            $table->string('invoice_no')->nullable()->after('invoice_path');
            $table->unique(['distributor_id', 'invoice_no'], 'ret_order_inv_unique');
        });

        Schema::create('prescription_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('retailer_id')->nullable();
            $table->text('raw_text')->nullable();
            $table->json('extracted_data')->nullable();
            $table->timestamps();

            $table->foreign('retailer_id')->references('id')->on('retailers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prescription_logs');

        Schema::table('retailer_orders', function (Blueprint $table) {
            $table->dropUnique('ret_order_inv_unique');
            $table->dropColumn('invoice_no');
        });

        Schema::table('distributor_orders', function (Blueprint $table) {
            $table->dropUnique('dist_order_inv_unique');
            $table->dropColumn('invoice_no');
        });
    }
};
