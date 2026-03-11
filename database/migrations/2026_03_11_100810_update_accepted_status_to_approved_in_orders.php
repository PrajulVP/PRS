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
        DB::table('retailer_orders')
            ->where('status', 'accepted')
            ->update(['status' => 'approved']);

        DB::table('distributor_orders')
            ->where('status', 'accepted')
            ->update(['status' => 'approved']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('retailer_orders')
            ->where('status', 'approved')
            ->update(['status' => 'accepted']);

        DB::table('distributor_orders')
            ->where('status', 'approved')
            ->update(['status' => 'accepted']);
    }
};
