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
        Schema::table('user_leave_balances', function (Blueprint $table) {
            $table->decimal('balance', 5, 1)->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_leave_balances', function (Blueprint $table) {
            $table->integer('balance')->default(0)->change();
        });
    }
};
