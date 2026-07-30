<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('distributor_order_item_batches', function (Blueprint $table) {
            $table->date('expiry_date')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('distributor_order_item_batches', function (Blueprint $table) {
            // Note: Reverting this may fail if there are null values in the DB.
            $table->date('expiry_date')->nullable(false)->change();
        });
    }
};
