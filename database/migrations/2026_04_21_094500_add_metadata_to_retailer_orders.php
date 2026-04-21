<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('retailer_orders', function (Blueprint $table) {
            $table->json('metadata')->nullable()->after('invoice_path');
        });
    }

    public function down()
    {
        Schema::table('retailer_orders', function (Blueprint $table) {
            $table->dropColumn('metadata');
        });
    }
};
