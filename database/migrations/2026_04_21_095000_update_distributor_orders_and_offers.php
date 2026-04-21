<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('distributor_orders', function (Blueprint $table) {
            $table->json('metadata')->nullable()->after('invoice_path');
        });

        Schema::table('offers', function (Blueprint $table) {
            $table->string('target_brand')->nullable()->after('description');
        });
    }

    public function down()
    {
        Schema::table('distributor_orders', function (Blueprint $table) {
            $table->dropColumn('metadata');
        });

        Schema::table('offers', function (Blueprint $table) {
            $table->dropColumn('target_brand');
        });
    }
};
